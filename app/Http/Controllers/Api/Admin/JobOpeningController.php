<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJobOpeningRequest;
use App\Http\Requests\Admin\UpdateJobOpeningRequest;
use App\Http\Requests\Admin\UpdateJobStatusRequest;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobOpeningController extends Controller
{
    /**
     * List job openings with filters and counts.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'all');
        $department = $request->query('department');
        $employmentType = $request->query('employment_type');
        $search = $request->query('search');

        $query = JobOpening::withCount('applications')->with('creator');

        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'expired') {
            $query->expired();
        } elseif ($status === 'draft') {
            $query->where('status', JobOpening::STATUS_DRAFT);
        } elseif ($status === 'published') {
            $query->where('status', JobOpening::STATUS_PUBLISHED);
        } elseif ($status === 'closed') {
            $query->where('status', JobOpening::STATUS_CLOSED);
        }

        if ($department) {
            $query->where('department', $department);
        }

        if ($employmentType) {
            $query->where('employment_type', $employmentType);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('job_title', 'like', "%{$search}%")
                  ->orWhere('job_code', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        $jobs = $query->latest('id')->paginate(10);

        $formattedJobs = collect($jobs->items())->map(function ($j) {
            return [
                'id' => $j->id,
                'job_code' => $j->job_code,
                'job_title' => $j->job_title,
                'department' => $j->department ?? 'General',
                'designation' => $j->designation ?? 'Not specified',
                'employment_type' => $j->employment_type ?? 'Full-time',
                'vacancies' => $j->vacancies,
                'salary_formatted' => $j->salary_formatted,
                'experience_formatted' => $j->experience_formatted,
                'applications_count' => $j->applications_count,
                'status' => $j->status,
                'is_expired' => $j->is_expired,
                'days_remaining' => $j->days_remaining,
                'deadline' => $j->application_deadline ? $j->application_deadline->format('M d, Y') : 'No closing date',
                'published_at' => $j->published_at ? $j->published_at->format('M d, Y') : null,
                'created_at' => $j->created_at->format('M d, Y'),
            ];
        });

        $stats = [
            'total' => JobOpening::count(),
            'active' => JobOpening::active()->count(),
            'draft' => JobOpening::where('status', JobOpening::STATUS_DRAFT)->count(),
            'expired' => JobOpening::expired()->count(),
            'total_applications' => JobApplication::count(),
        ];

        $departments = JobOpening::whereNotNull('department')->distinct()->pluck('department');
        $employmentTypes = ['Full-time', 'Part-time', 'Contract', 'Remote', 'Internship'];

        return response()->json([
            'jobs' => $formattedJobs,
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'total' => $jobs->total(),
                'per_page' => $jobs->perPage(),
            ],
            'stats' => $stats,
            'departments' => $departments,
            'employment_types' => $employmentTypes,
        ]);
    }

    /**
     * Store newly created job opening.
     */
    public function store(StoreJobOpeningRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['required_skills'] = $this->parseSkills($request->input('required_skills'));

        $action = $request->input('action_type', 'save');
        if ($action === 'publish') {
            $validated['status'] = JobOpening::STATUS_PUBLISHED;
            if (empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }
        } elseif ($action === 'draft') {
            $validated['status'] = JobOpening::STATUS_DRAFT;
        }

        if (empty($validated['job_code'])) {
            $validated['job_code'] = JobOpening::generateJobCode();
        }

        $validated['created_by'] = Auth::id();

        $job = JobOpening::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Job opening '{$job->job_title}' ({$job->job_code}) created successfully.",
            'job' => $job,
        ], 201);
    }

    /**
     * View detailed job opening and its received applications.
     */
    public function show(JobOpening $job): JsonResponse
    {
        $job->load('creator');

        $applications = $job->applications()->latest('submitted_at')->paginate(15);

        $formattedApps = collect($applications->items())->map(function ($app) {
            return [
                'id' => $app->id,
                'application_code' => $app->application_code,
                'full_name' => $app->full_name,
                'email' => $app->email,
                'phone' => $app->phone,
                'current_location' => $app->current_location ?? 'Not specified',
                'highest_qualification' => $app->highest_qualification ?? 'Not specified',
                'total_experience' => $app->total_experience_years ? $app->total_experience_years . ' yrs' : 'Fresher',
                'current_ctc' => $app->current_ctc ? '$' . number_format($app->current_ctc, 0) : 'N/A',
                'expected_ctc' => $app->expected_ctc ? '$' . number_format($app->expected_ctc, 0) : 'N/A',
                'status' => $app->status,
                'source' => $app->source ?? 'direct',
                'crm_transfer_status' => $app->crm_transfer_status,
                'submitted_at' => $app->submitted_at ? $app->submitted_at->format('M d, Y - h:i A') : $app->created_at->format('M d, Y - h:i A'),
            ];
        });

        $appStats = [
            'total' => $job->applications()->count(),
            'pending' => $job->applications()->whereIn('status', [
                JobApplication::STATUS_PENDING,
                JobApplication::STATUS_SUBMITTED,
                JobApplication::STATUS_UNDER_REVIEW,
            ])->count(),
            'shortlisted' => $job->applications()->where('status', JobApplication::STATUS_SHORTLISTED)->count(),
            'rejected' => $job->applications()->where('status', JobApplication::STATUS_REJECTED)->count(),
            'crm_transferred' => $job->applications()->whereIn('crm_transfer_status', [
                JobApplication::CRM_TRANSFERRED,
                JobApplication::CRM_COMPLETED,
                'success',
            ])->count(),
        ];

        return response()->json([
            'job' => [
                'id' => $job->id,
                'job_code' => $job->job_code,
                'job_title' => $job->job_title,
                'department' => $job->department,
                'designation' => $job->designation,
                'vacancies' => $job->vacancies,
                'employment_type' => $job->employment_type,
                'experience_min' => $job->experience_min,
                'experience_max' => $job->experience_max,
                'experience_formatted' => $job->experience_formatted,
                'salary_min' => $job->salary_min,
                'salary_max' => $job->salary_max,
                'salary_formatted' => $job->salary_formatted,
                'job_description' => $job->job_description,
                'required_qualifications' => $job->required_qualifications,
                'required_skills' => $job->required_skills ?? [],
                'status' => $job->status,
                'is_expired' => $job->is_expired,
                'days_remaining' => $job->days_remaining,
                'published_at' => $job->published_at ? $job->published_at->format('Y-m-d') : null,
                'application_deadline' => $job->application_deadline ? $job->application_deadline->format('Y-m-d') : null,
                'creator_name' => $job->creator->name ?? 'Administrator',
                'created_at' => $job->created_at->format('M d, Y'),
            ],
            'promotion_urls' => [
                'direct_job' => url("/jobs/{$job->job_code}"),
                'direct_apply' => url("/jobs/{$job->job_code}/apply"),
                'linkedin' => url("/jobs/{$job->job_code}/apply?source=linkedin"),
                'naukri' => url("/jobs/{$job->job_code}/apply?source=naukri"),
                'indeed' => url("/jobs/{$job->job_code}/apply?source=indeed"),
                'whatsapp' => url("/jobs/{$job->job_code}/apply?source=whatsapp"),
            ],
            'app_stats' => $appStats,
            'applications' => [
                'data' => $formattedApps,
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'total' => $applications->total(),
                    'per_page' => $applications->perPage(),
                ],
            ],
        ]);
    }

    /**
     * Update job opening.
     */
    public function update(UpdateJobOpeningRequest $request, JobOpening $job): JsonResponse
    {
        //dd("hello");
        $validated = $request->validated();
        $validated['required_skills'] = $this->parseSkills($request->input('required_skills'));

        $action = $request->input('action_type');
        if ($action === 'publish') {
            $validated['status'] = JobOpening::STATUS_PUBLISHED;
            if (empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }
        } elseif ($action === 'draft') {
            $validated['status'] = JobOpening::STATUS_DRAFT;
        }

        $job->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Job opening '{$job->job_title}' updated successfully.",
            'job' => $job,
        ]);
    }

    /**
     * Quick status update: publish, unpublish, activate, deactivate, or expire.
     */
    public function updateStatus(UpdateJobStatusRequest $request, JobOpening $job): JsonResponse
    {
        $newStatus = $request->validated('status');
        $updates = ['status' => $newStatus];

        if ($newStatus === JobOpening::STATUS_PUBLISHED && !$job->published_at) {
            $updates['published_at'] = now();
        }

        $job->update($updates);

        return response()->json([
            'success' => true,
            'message' => "Status of '{$job->job_title}' updated to " . ucfirst($newStatus) . '.',
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Soft delete job opening.
     */
    public function destroy(JobOpening $job): JsonResponse
    {
        $title = $job->job_title;
        $job->delete();

        return response()->json([
            'success' => true,
            'message' => "Job opening '{$title}' moved to trash.",
        ]);
    }

    /**
     * Parse raw skills input into clean array.
     */
    protected function parseSkills(mixed $skills): ?array
    {
        if (is_array($skills)) {
            return array_values(array_filter(array_map('trim', $skills)));
        }

        if (is_string($skills) && trim($skills) !== '') {
            $parsed = array_map('trim', explode(',', $skills));
            return array_values(array_filter($parsed));
        }

        return null;
    }
}
