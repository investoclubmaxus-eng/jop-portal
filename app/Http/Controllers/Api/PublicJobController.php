<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\SubmitJobApplicationRequest;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStatusHistory;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicJobController extends Controller
{
    /**
     * Public listing of active vacancies.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $department = $request->query('department');
        $employmentType = $request->query('employment_type');

        // Automatically hide expired and draft vacancies
        $query = JobOpening::active()
            ->withCount('applications')
            ->latest('published_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('job_title', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('job_description', 'like', "%{$search}%");
            });
        }

        if ($department) {
            $query->where('department', $department);
        }

        if ($employmentType) {
            $query->where('employment_type', $employmentType);
        }

        $jobs = $query->paginate(9);

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
                'required_skills' => $j->required_skills ?? [],
                'job_description_snippet' => \Illuminate\Support\Str::limit($j->job_description, 140),
                'days_remaining' => $j->days_remaining,
                'deadline' => $j->application_deadline ? $j->application_deadline->format('M d, Y') : 'Open until filled',
                'published_at' => $j->published_at ? $j->published_at->format('M d, Y') : 'Recently',
            ];
        });

        $departments = JobOpening::active()->whereNotNull('department')->distinct()->pluck('department');
        $employmentTypes = JobOpening::active()->whereNotNull('employment_type')->distinct()->pluck('employment_type');
        $totalActiveVacancies = JobOpening::active()->sum('vacancies');

        return response()->json([
            'jobs' => $formattedJobs,
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'total' => $jobs->total(),
                'per_page' => $jobs->perPage(),
            ],
            'departments' => $departments,
            'employment_types' => $employmentTypes,
            'total_active_vacancies' => $totalActiveVacancies,
        ]);
    }

    /**
     * Public details of a job vacancy.
     */
    public function show(Request $request, string $jobCode): JsonResponse
    {
        $job = JobOpening::where('job_code', $jobCode)->first();

        if (!$job) {
            return response()->json(['message' => 'Job vacancy not found.'], 404);
        }

        $relatedJobs = JobOpening::active()
            ->where('id', '!=', $job->id)
            ->where('department', $job->department)
            ->take(3)
            ->get()
            ->map(function ($r) {
                return [
                    'job_code' => $r->job_code,
                    'job_title' => $r->job_title,
                    'employment_type' => $r->employment_type ?? 'Full-time',
                    'salary_formatted' => $r->salary_formatted,
                ];
            });

        return response()->json([
            'job' => [
                'id' => $job->id,
                'job_code' => $job->job_code,
                'job_title' => $job->job_title,
                'department' => $job->department ?? 'General',
                'designation' => $job->designation ?? 'Not specified',
                'vacancies' => $job->vacancies,
                'employment_type' => $job->employment_type ?? 'Full-time',
                'salary_formatted' => $job->salary_formatted,
                'experience_formatted' => $job->experience_formatted,
                'job_description' => $job->job_description,
                'required_qualifications' => $job->required_qualifications,
                'required_skills' => $job->required_skills ?? [],
                'status' => $job->status,
                'is_expired' => $job->is_expired,
                'days_remaining' => $job->days_remaining,
                'deadline' => $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'Open until filled',
                'published_at' => $job->published_at ? $job->published_at->format('M d, Y') : 'Recently',
            ],
            'related_jobs' => $relatedJobs,
        ]);
    }

    /**
     * Candidate application submission API with duplicate check and document management.
     */
    public function apply(SubmitJobApplicationRequest $request, string $jobCode): JsonResponse
    {
        $job = JobOpening::where('job_code', $jobCode)->firstOrFail();

        // 1. Server-side deadline & status validation
        if ($job->is_expired || !in_array($job->status, [JobOpening::STATUS_PUBLISHED, JobOpening::STATUS_ACTIVE])) {
            return response()->json([
                'message' => 'This job vacancy has expired and is no longer accepting applications.',
            ], 422);
        }

        $validated = $request->validated();

        // 2. Duplicate application validation (by email or phone for this specific job opening)
        $existing = JobApplication::where('job_opening_id', $job->id)
            ->where(function ($q) use ($validated) {
                $q->where('email', $validated['email'])
                  ->orWhere('phone', $validated['phone']);
            })
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already submitted an application for this vacancy with this email address or phone number.',
                'errors' => [
                    'email' => ['An application for this position has already been received from this email or phone.'],
                ],
            ], 422);
        }

        $resumePath = null;
        $documentData = null;

        // 3. Process Resume Upload
        if ($request->hasFile('resume') && $request->file('resume')->isValid()) {
            $file = $request->file('resume');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType();
            $fileSize = $file->getSize();

            $storedPath = $file->store('resumes', 'public');
            $resumePath = $storedPath;

            $documentData = [
                'document_type' => 'resume',
                'file_name' => $originalName,
                'file_path' => $storedPath,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
            ];
        }

        $appCode = JobApplication::generateApplicationCode();
        $source = !empty($validated['source']) ? strtolower($validated['source']) : 'direct';

        // 4. Create Job Application
        $application = JobApplication::create([
            'application_code' => $appCode,
            'job_opening_id' => $job->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'current_location' => $validated['current_location'] ?? null,
            'highest_qualification' => $validated['highest_qualification'] ?? null,
            'total_experience_years' => $validated['total_experience_years'] ?? null,
            'current_ctc' => $validated['current_ctc'] ?? null,
            'expected_ctc' => $validated['expected_ctc'] ?? null,
            'source' => $source,
            'resume_path' => $resumePath,
            'status' => JobApplication::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'crm_transfer_status' => JobApplication::CRM_PENDING,
        ]);

        if ($documentData) {
            $documentData['application_id'] = $application->id;
            ApplicationDocument::create($documentData);
        }

        // Process Additional Documents if uploaded
        if ($request->hasFile('additional_documents')) {
            foreach ($request->file('additional_documents') as $doc) {
                if ($doc->isValid()) {
                    $stored = $doc->store('documents', 'public');
                    ApplicationDocument::create([
                        'application_id' => $application->id,
                        'document_type' => 'additional',
                        'file_name' => $doc->getClientOriginalName(),
                        'file_path' => $stored,
                        'mime_type' => $doc->getClientMimeType(),
                        'file_size' => $doc->getSize(),
                    ]);
                }
            }
        }

        // 5. Initial Status History Entry
        $sourceTitle = ucfirst($source);
        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'old_status' => null,
            'new_status' => JobApplication::STATUS_SUBMITTED,
            'remarks' => "Application submitted through public careers portal (Source: {$sourceTitle}).",
            'changed_by' => null,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully!',
            'application_code' => $appCode,
            'candidate_name' => "{$validated['first_name']} {$validated['last_name']}",
            'job_title' => $job->job_title,
            'source' => $sourceTitle,
        ], 201);
    }

    /**
     * Download or preview an application document.
     */
    public function downloadDocument(int $id): StreamedResponse|JsonResponse
    {
        $document = ApplicationDocument::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['message' => 'File not found on server.'], 404);
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->file_name
        );
    }
}
