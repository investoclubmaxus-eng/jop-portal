<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Models\ApplicationStatusHistory;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicJobController extends Controller
{
    /**
     * Display public listing of active published vacancies.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $department = $request->query('department');
        $employmentType = $request->query('employment_type');

        // Automatically hide expired and draft vacancies!
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

        $jobs = $query->paginate(9)->withQueryString();

        // Get filter options from currently active vacancies
        $departments = JobOpening::active()
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department');

        $employmentTypes = JobOpening::active()
            ->whereNotNull('employment_type')
            ->distinct()
            ->pluck('employment_type');

        $totalActiveVacancies = JobOpening::active()->sum('vacancies');

        return view('public.jobs.index', compact(
            'jobs',
            'departments',
            'employmentTypes',
            'search',
            'department',
            'employmentType',
            'totalActiveVacancies'
        ));
    }

    /**
     * View complete job details and eligibility criteria.
     * Also supports ?apply=1 or ?source=linkedin to automatically open application form.
     */
    public function show(Request $request, string $jobCode): View
    {
        $job = JobOpening::where('job_code', $jobCode)
            ->firstOrFail();

        $source = strtolower(trim($request->query('source', 'direct')));
        $autoOpenApply = $request->boolean('apply') || $request->has('apply');

        // Related other active vacancies in the same department
        $relatedJobs = JobOpening::active()
            ->where('id', '!=', $job->id)
            ->where('department', $job->department)
            ->take(3)
            ->get();

        return view('public.jobs.show', compact('job', 'relatedJobs', 'source', 'autoOpenApply'));
    }

    /**
     * Dedicated direct application page for sharing on LinkedIn, Naukri, Indeed, etc.
     * Accessible directly via: /jobs/{jobCode}/apply?source=linkedin
     */
    public function showApplyForm(Request $request, string $jobCode): View
    {
        $job = JobOpening::where('job_code', $jobCode)
            ->firstOrFail();

        $source = strtolower(trim($request->query('source', 'direct')));

        return view('public.jobs.apply', compact('job', 'source'));
    }

    /**
     * Handle candidate application submission.
     */
    public function apply(Request $request, string $jobCode): RedirectResponse
    {
        $job = JobOpening::where('job_code', $jobCode)->firstOrFail();

        // Ensure vacancy is not expired or closed
        if ($job->is_expired || !in_array($job->status, [JobOpening::STATUS_PUBLISHED, JobOpening::STATUS_ACTIVE])) {
            return back()->with('error', 'Sorry, this job opening has expired and is no longer accepting new applications.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'highest_qualification' => ['nullable', 'string', 'max:255'],
            'total_experience_years' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'current_ctc' => ['nullable', 'numeric', 'min:0'],
            'expected_ctc' => ['nullable', 'numeric', 'min:0'],
            'source' => ['nullable', 'string', 'max:50'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB max
        ]);

        $resumePath = null;
        $documentData = null;

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

        // Generate application code
        $appCode = JobApplication::generateApplicationCode();
        $source = !empty($validated['source']) ? strtolower($validated['source']) : 'direct';

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

        // Create status history log
        $sourceTitle = ucfirst($source);
        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'old_status' => null,
            'new_status' => JobApplication::STATUS_SUBMITTED,
            'remarks' => "Application submitted through public careers portal (Source: {$sourceTitle}).",
            'changed_by' => null,
            'created_at' => now(),
        ]);

        return back()->with('application_success', [
            'code' => $appCode,
            'title' => $job->job_title,
            'name' => "{$validated['first_name']} {$validated['last_name']}",
            'source' => $sourceTitle,
        ]);
    }
}
