<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobOpeningController extends Controller
{
    /**
     * Display listing of job openings with filters.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $department = $request->query('department');
        $employmentType = $request->query('employment_type');
        $search = $request->query('search');

        $query = JobOpening::withCount('applications')
            ->with('creator');

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

        $jobs = $query->latest('id')->paginate(10)->withQueryString();

        $stats = [
            'total' => JobOpening::count(),
            'active' => JobOpening::active()->count(),
            'draft' => JobOpening::where('status', JobOpening::STATUS_DRAFT)->count(),
            'expired' => JobOpening::expired()->count(),
            'total_applications' => JobApplication::count(),
        ];

        $departments = JobOpening::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        $employmentTypes = ['Full-time', 'Part-time', 'Contract', 'Remote', 'Internship'];

        return view('admin.jobs.index', compact(
            'jobs',
            'stats',
            'status',
            'department',
            'employmentType',
            'search',
            'departments',
            'employmentTypes'
        ));
    }

    /**
     * Show form for creating a new job opening.
     */
    public function create(): View
    {
        $suggestedCode = JobOpening::generateJobCode();
        $employmentTypes = ['Full-time', 'Part-time', 'Contract', 'Remote', 'Internship'];

        return view('admin.jobs.create', compact('suggestedCode', 'employmentTypes'));
    }

    /**
     * Store newly created job opening.
     */
    public function store(Request $request): RedirectResponse
    {
        
        $validated = $this->validateJobOpening($request);

        // Normalize skills from comma separated string or array
        $validated['required_skills'] = $this->parseSkills($request->input('required_skills'));

        // Handle publish/draft action button
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

        return redirect()->route('admin.jobs.show', $job)
            ->with('success', "Job opening '{$job->job_title}' ({$job->job_code}) created successfully.");
    }

    /**
     * Display details of a job opening and view all applications received.
     */
    public function show(JobOpening $job): View
    {
        $job->load(['creator']);

        $applications = $job->applications()
            ->latest('submitted_at')
            ->paginate(15);

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

        return view('admin.jobs.show', compact('job', 'applications', 'appStats'));
    }

    /**
     * Show form for editing the job opening.
     */
    public function edit(JobOpening $job): View
    {
        $employmentTypes = ['Full-time', 'Part-time', 'Contract', 'Remote', 'Internship'];

        return view('admin.jobs.edit', compact('job', 'employmentTypes'));
    }

    /**
     * Update specified job opening.
     */
    public function update(Request $request, JobOpening $job): RedirectResponse
    {
        $validated = $this->validateJobOpening($request, $job->id);

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

        return redirect()->route('admin.jobs.show', $job)
            ->with('success', "Job opening '{$job->job_title}' updated successfully.");
    }

    /**
     * Quick status update: publish, unpublish, activate, deactivate, or expire.
     */
    public function updateStatus(Request $request, JobOpening $job): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::in([
                JobOpening::STATUS_DRAFT,
                JobOpening::STATUS_PUBLISHED,
                JobOpening::STATUS_ACTIVE,
                JobOpening::STATUS_CLOSED,
                JobOpening::STATUS_EXPIRED,
            ])],
        ]);

        $newStatus = $request->input('status');

        $updates = ['status' => $newStatus];

        if ($newStatus === JobOpening::STATUS_PUBLISHED && !$job->published_at) {
            $updates['published_at'] = now();
        }

        $job->update($updates);

        $statusLabel = match($newStatus) {
            JobOpening::STATUS_PUBLISHED => 'Published',
            JobOpening::STATUS_DRAFT => 'Unpublished (Moved to Draft)',
            JobOpening::STATUS_ACTIVE => 'Activated',
            JobOpening::STATUS_CLOSED => 'Deactivated / Closed',
            JobOpening::STATUS_EXPIRED => 'Marked as Expired',
            default => ucfirst($newStatus),
        };

        return back()->with('success', "Job '{$job->job_title}' status changed to {$statusLabel}.");
    }

    /**
     * Remove the specified job opening (Soft Delete).
     */
    public function destroy(JobOpening $job): RedirectResponse
    {
        $title = $job->job_title;
        $job->delete();

        return redirect()->route('admin.jobs.index')
            ->with('success', "Job opening '{$title}' has been moved to trash.");
    }

    /**
     * Validate incoming job opening request.
     */
    protected function validateJobOpening(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'job_title' => ['required', 'string', 'max:255'],
            'job_code' => ['required', 'string', 'max:50', Rule::unique('job_openings', 'job_code')->ignore($id)],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'vacancies' => ['required', 'integer', 'min:1', 'max:999'],
            'employment_type' => ['nullable', 'string', 'max:50'],
            'experience_min' => ['nullable', 'integer', 'min:0', 'max:50'],
            'experience_max' => ['nullable', 'integer', 'min:0', 'max:50', 'gte:experience_min'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'job_description' => ['required', 'string'],
            'required_qualifications' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'application_deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::in([
                JobOpening::STATUS_DRAFT,
                JobOpening::STATUS_PUBLISHED,
                JobOpening::STATUS_ACTIVE,
                JobOpening::STATUS_CLOSED,
                JobOpening::STATUS_EXPIRED,
            ])],
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
