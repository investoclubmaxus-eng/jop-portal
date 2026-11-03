<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkUpdateApplicationStatusRequest;
use App\Http\Requests\Admin\TransferToCrmRequest;
use App\Http\Requests\Admin\UpdateApplicationStatusRequest;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStatusHistory;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    /**
     * List candidate applications with search, filters, pagination, and recruitment metrics.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $jobId = $request->query('job_opening_id');
        $status = $request->query('status');
        $crmStatus = $request->query('crm_transfer_status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = JobApplication::with(['jobOpening', 'documents'])->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('application_code', 'like', "%{$search}%");
            });
        }

        if ($jobId) {
            $query->where('job_opening_id', $jobId);
        }

        if ($status && $status !== 'all') {
            if ($status === 'pending') {
                $query->whereIn('status', [JobApplication::STATUS_PENDING, JobApplication::STATUS_SUBMITTED]);
            } else {
                $query->where('status', $status);
            }
        }

        if ($crmStatus && $crmStatus !== 'all') {
            $query->where('crm_transfer_status', $crmStatus);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($dateFrom));
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', Carbon::parse($dateTo));
        }

        $applications = $query->paginate(12);

        $formattedApps = collect($applications->items())->map(function ($app) {
            return [
                'id' => $app->id,
                'application_code' => $app->application_code,
                'full_name' => $app->full_name,
                'first_name' => $app->first_name,
                'last_name' => $app->last_name,
                'email' => $app->email,
                'phone' => $app->phone,
                'current_location' => $app->current_location ?? 'Not specified',
                'highest_qualification' => $app->highest_qualification ?? 'Not specified',
                'total_experience' => $app->total_experience_years ? $app->total_experience_years . ' yrs' : 'Fresher',
                'current_ctc' => $app->current_ctc ? '$' . number_format($app->current_ctc, 0) : 'N/A',
                'expected_ctc' => $app->expected_ctc ? '$' . number_format($app->expected_ctc, 0) : 'N/A',
                'job_opening_id' => $app->job_opening_id,
                'job_title' => $app->jobOpening->job_title ?? 'Unknown Vacancy',
                'job_code' => $app->jobOpening->job_code ?? '',
                'department' => $app->jobOpening->department ?? 'General',
                'status' => $app->status,
                'source' => $app->source ?? 'direct',
                'screening_remarks' => $app->screening_remarks,
                'has_resume' => !empty($app->resume_path) || $app->documents->where('document_type', 'resume')->isNotEmpty(),
                'documents_count' => $app->documents->count(),
                'crm_transfer_status' => $app->crm_transfer_status,
                'crm_application_id' => $app->crm_application_id,
                'transferred_at' => $app->transferred_at ? $app->transferred_at->format('M d, Y - h:i A') : null,
                'submitted_at' => $app->submitted_at ? $app->submitted_at->format('M d, Y - h:i A') : $app->created_at->format('M d, Y - h:i A'),
                'created_at' => $app->created_at->format('M d, Y'),
            ];
        });

        // Metrics counts
        $stats = [
            'total' => JobApplication::count(),
            'pending' => JobApplication::whereIn('status', [JobApplication::STATUS_PENDING, JobApplication::STATUS_SUBMITTED])->count(),
            'under_review' => JobApplication::where('status', JobApplication::STATUS_UNDER_REVIEW)->count(),
            'shortlisted' => JobApplication::where('status', JobApplication::STATUS_SHORTLISTED)->count(),
            'rejected' => JobApplication::where('status', JobApplication::STATUS_REJECTED)->count(),
            'on_hold' => JobApplication::where('status', 'on_hold')->count(),
            'crm_transferred' => JobApplication::whereIn('crm_transfer_status', [JobApplication::CRM_TRANSFERRED, JobApplication::CRM_COMPLETED])->count(),
        ];

        // Job openings for filter dropdown
        $jobOpenings = JobOpening::select('id', 'job_title', 'job_code')->latest('id')->get();

        return response()->json([
            'applications' => $formattedApps,
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'total' => $applications->total(),
                'per_page' => $applications->perPage(),
            ],
            'stats' => $stats,
            'job_openings' => $jobOpenings,
        ]);
    }

    /**
     * View detailed candidate application, screening notes, documents, and status history timeline.
     */
    public function show(JobApplication $application): JsonResponse
    {
        $application->load([
            'jobOpening',
            'documents',
            'statusHistory.changedBy',
        ]);

        $documents = $application->documents->map(function ($doc) {
            return [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'file_name' => $doc->file_name,
                'mime_type' => $doc->mime_type,
                'file_size_formatted' => $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : 'N/A',
                'download_url' => url("/api/documents/{$doc->id}/download"),
                'created_at' => $doc->created_at->format('M d, Y - h:i A'),
            ];
        });

        $timeline = $application->statusHistory->map(function ($h) {
            return [
                'id' => $h->id,
                'old_status' => $h->old_status,
                'new_status' => $h->new_status,
                'remarks' => $h->remarks,
                'changed_by_name' => $h->changedBy->name ?? 'System / Candidate Portal',
                'created_at' => $h->created_at ? $h->created_at->format('M d, Y - h:i A') : 'N/A',
                'time_relative' => $h->created_at ? $h->created_at->diffForHumans() : '',
            ];
        });

        return response()->json([
            'application' => [
                'id' => $application->id,
                'application_code' => $application->application_code,
                'full_name' => $application->full_name,
                'first_name' => $application->first_name,
                'last_name' => $application->last_name,
                'email' => $application->email,
                'phone' => $application->phone,
                'current_location' => $application->current_location ?? 'Not specified',
                'highest_qualification' => $application->highest_qualification ?? 'Not specified',
                'total_experience_years' => $application->total_experience_years,
                'total_experience' => $application->total_experience_years ? $application->total_experience_years . ' yrs' : 'Fresher',
                'current_ctc' => $application->current_ctc,
                'current_ctc_formatted' => $application->current_ctc ? '$' . number_format($application->current_ctc, 0) : 'N/A',
                'expected_ctc' => $application->expected_ctc,
                'expected_ctc_formatted' => $application->expected_ctc ? '$' . number_format($application->expected_ctc, 0) : 'N/A',
                'source' => $application->source ?? 'direct',
                'status' => $application->status,
                'screening_remarks' => $application->screening_remarks,
                'crm_transfer_status' => $application->crm_transfer_status,
                'crm_application_id' => $application->crm_application_id,
                'transferred_at' => $application->transferred_at ? $application->transferred_at->format('M d, Y - h:i A') : null,
                'resume_path' => $application->resume_path,
                'submitted_at' => $application->submitted_at ? $application->submitted_at->format('M d, Y - h:i A') : $application->created_at->format('M d, Y - h:i A'),
                'created_at' => $application->created_at->format('M d, Y'),
            ],
            'job_opening' => [
                'id' => $application->jobOpening->id,
                'job_code' => $application->jobOpening->job_code,
                'job_title' => $application->jobOpening->job_title,
                'department' => $application->jobOpening->department,
                'designation' => $application->jobOpening->designation,
                'employment_type' => $application->jobOpening->employment_type,
                'salary_formatted' => $application->jobOpening->salary_formatted,
                'status' => $application->jobOpening->status,
            ],
            'documents' => $documents,
            'status_history' => $timeline,
        ]);
    }

    /**
     * Update application status and screening remarks (Screening Module 8).
     */
    public function updateStatus(UpdateApplicationStatusRequest $request, JobApplication $application): JsonResponse
    {
        $oldStatus = $application->status;
        $newStatus = $request->validated('status');
        $remarks = $request->validated('screening_remarks');

        $application->update([
            'status' => $newStatus,
            'screening_remarks' => $remarks ?? $application->screening_remarks,
        ]);

        // Record status change in history
        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'remarks' => $remarks ?: "Status transitioned from " . ucfirst($oldStatus) . " to " . ucfirst($newStatus) . ".",
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Candidate {$application->full_name} status updated to " . ucfirst(str_replace('_', ' ', $newStatus)) . ".",
            'application' => $application,
        ]);
    }

    /**
     * Bulk update status for multiple selected candidates.
     */
    public function bulkUpdateStatus(BulkUpdateApplicationStatusRequest $request): JsonResponse
    {
        $ids = $request->validated('application_ids');
        $newStatus = $request->validated('status');
        $remarks = $request->validated('remarks');
        $adminId = Auth::id();

        $applications = JobApplication::whereIn('id', $ids)->get();

        foreach ($applications as $app) {
            $oldStatus = $app->status;
            $app->update([
                'status' => $newStatus,
                'screening_remarks' => $remarks ?: $app->screening_remarks,
            ]);

            ApplicationStatusHistory::create([
                'application_id' => $app->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'remarks' => $remarks ?: "Bulk action: status set to " . ucfirst(str_replace('_', ' ', $newStatus)) . ".",
                'changed_by' => $adminId,
                'created_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($applications) . " candidate applications marked as " . ucfirst(str_replace('_', ' ', $newStatus)) . ".",
        ]);
    }

    /**
     * Transfer shortlisted applicants to CRM (Shortlisting Module 9).
     * Constraint: Only shortlisted candidates can be transferred.
     */
    public function transferToCrm(TransferToCrmRequest $request): JsonResponse
    {
        $ids = $request->validated('application_ids');

        // Check if any non-shortlisted application is included
        $nonShortlisted = JobApplication::whereIn('id', $ids)
            ->where('status', '!=', JobApplication::STATUS_SHORTLISTED)
            ->get();

        if ($nonShortlisted->isNotEmpty()) {
            $invalidName = $nonShortlisted->first()->full_name;
            $invalidStatus = $nonShortlisted->first()->status;

            return response()->json([
                'message' => "Only shortlisted candidates can be transferred to CRM. Candidate \"{$invalidName}\" has status \"{$invalidStatus}\". Please shortlist candidate first.",
                'errors' => [
                    'application_ids' => ["Only shortlisted candidates can be transferred to CRM."],
                ],
            ], 422);
        }

        $applications = JobApplication::whereIn('id', $ids)->get();
        $adminId = Auth::id();
        $transferredCount = 0;

        foreach ($applications as $app) {
            $crmId = rand(10000, 99999);
            $app->update([
                'crm_transfer_status' => JobApplication::CRM_TRANSFERRED,
                'crm_application_id' => $crmId,
                'transferred_at' => now(),
            ]);

            ApplicationStatusHistory::create([
                'application_id' => $app->id,
                'old_status' => $app->status,
                'new_status' => $app->status,
                'remarks' => "Candidate profile successfully transferred to Enterprise CRM for interview scheduling (CRM Ref: #CRM-{$crmId}).",
                'changed_by' => $adminId,
                'created_at' => now(),
            ]);

            $transferredCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$transferredCount} shortlisted candidate(s) successfully transferred to CRM.",
            'transferred_count' => $transferredCount,
        ]);
    }

    /**
     * Export application records to Excel/CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $search = $request->query('search');
        $jobId = $request->query('job_opening_id');
        $status = $request->query('status');

        $query = JobApplication::with('jobOpening')->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('application_code', 'like', "%{$search}%");
            });
        }

        if ($jobId) {
            $query->where('job_opening_id', $jobId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="candidate_applications_' . date('Y-m-d_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // CSV Header Row
            fputcsv($handle, [
                'Application Code',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Location',
                'Job Code',
                'Job Title',
                'Department',
                'Highest Qualification',
                'Total Experience (Years)',
                'Current CTC',
                'Expected CTC',
                'Source',
                'Status',
                'Screening Remarks',
                'CRM Transfer Status',
                'Submitted At',
            ]);

            $query->chunk(100, function ($apps) use ($handle) {
                foreach ($apps as $app) {
                    fputcsv($handle, [
                        $app->application_code,
                        $app->first_name,
                        $app->last_name,
                        $app->email,
                        $app->phone,
                        $app->current_location ?? 'N/A',
                        $app->jobOpening->job_code ?? 'N/A',
                        $app->jobOpening->job_title ?? 'N/A',
                        $app->jobOpening->department ?? 'N/A',
                        $app->highest_qualification ?? 'N/A',
                        $app->total_experience_years ?? 0,
                        $app->current_ctc ?? 0,
                        $app->expected_ctc ?? 0,
                        $app->source ?? 'direct',
                        $app->status,
                        $app->screening_remarks ?? '',
                        $app->crm_transfer_status,
                        $app->submitted_at ? $app->submitted_at->format('Y-m-d H:i:s') : $app->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
