<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Return comprehensive recruitment metrics & statistics.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Vacancy metrics
        $totalJobOpenings = JobOpening::count();
        $activeVacancies = JobOpening::active()->count();
        $expiredVacancies = JobOpening::expired()->count();
        $draftVacancies = JobOpening::draft()->count();

        // 2. Application metrics
        $totalApplications = JobApplication::count();
        $applicationsToday = JobApplication::today()->count();
        $pendingApplications = JobApplication::pending()->count();
        $shortlistedApplications = JobApplication::shortlisted()->count();
        $rejectedApplications = JobApplication::rejected()->count();
        $crmTransferredApplications = JobApplication::transferredToCrm()->count();

        // 3. Recent applications (8 items)
        $recentApplications = JobApplication::with('jobOpening')
            ->latest('created_at')
            ->take(8)
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'application_code' => $app->application_code,
                    'full_name' => $app->full_name,
                    'email' => $app->email,
                    'phone' => $app->phone,
                    'job_title' => $app->jobOpening->job_title ?? 'Unknown Job',
                    'job_code' => $app->jobOpening->job_code ?? '',
                    'total_experience' => $app->total_experience_years ? $app->total_experience_years . ' yrs' : 'Fresher',
                    'current_location' => $app->current_location ?? 'Not specified',
                    'status' => $app->status,
                    'source' => $app->source ?? 'direct',
                    'crm_transfer_status' => $app->crm_transfer_status,
                    'created_at' => $app->created_at->format('M d, Y - h:i A'),
                    'created_relative' => $app->created_at->diffForHumans(),
                ];
            });

        // 4. Vacancy-wise application breakdown
        $vacancyWiseStats = JobOpening::withCount([
            'applications as total_applications_count',
            'applications as pending_count' => function ($q) {
                $q->whereIn('status', [
                    JobApplication::STATUS_PENDING,
                    JobApplication::STATUS_SUBMITTED,
                    JobApplication::STATUS_UNDER_REVIEW,
                ]);
            },
            'applications as shortlisted_count' => function ($q) {
                $q->where('status', JobApplication::STATUS_SHORTLISTED);
            },
            'applications as rejected_count' => function ($q) {
                $q->where('status', JobApplication::STATUS_REJECTED);
            },
            'applications as crm_transferred_count' => function ($q) {
                $q->whereIn('crm_transfer_status', [
                    JobApplication::CRM_TRANSFERRED,
                    JobApplication::CRM_COMPLETED,
                    'success',
                ]);
            },
        ])
        ->latest('id')
        ->paginate(10);

        $formattedVacancies = collect($vacancyWiseStats->items())->map(function ($v) {
            return [
                'id' => $v->id,
                'job_code' => $v->job_code,
                'job_title' => $v->job_title,
                'department' => $v->department ?? 'General',
                'employment_type' => $v->employment_type ?? 'Full-time',
                'vacancies' => $v->vacancies,
                'status' => $v->status,
                'is_expired' => $v->is_expired,
                'total_applications' => $v->total_applications_count,
                'pending_count' => $v->pending_count,
                'shortlisted_count' => $v->shortlisted_count,
                'rejected_count' => $v->rejected_count,
                'crm_transferred_count' => $v->crm_transferred_count,
                'deadline' => $v->application_deadline ? $v->application_deadline->format('M d, Y') : 'No deadline',
            ];
        });

        return response()->json([
            'metrics' => [
                'total_job_openings' => $totalJobOpenings,
                'active_vacancies' => $activeVacancies,
                'expired_vacancies' => $expiredVacancies,
                'draft_vacancies' => $draftVacancies,
                'total_applications' => $totalApplications,
                'applications_today' => $applicationsToday,
                'pending_applications' => $pendingApplications,
                'shortlisted_applications' => $shortlistedApplications,
                'rejected_applications' => $rejectedApplications,
                'crm_transferred_applications' => $crmTransferredApplications,
            ],
            'recent_applications' => $recentApplications,
            'vacancy_wise_stats' => [
                'data' => $formattedVacancies,
                'pagination' => [
                    'current_page' => $vacancyWiseStats->currentPage(),
                    'last_page' => $vacancyWiseStats->lastPage(),
                    'total' => $vacancyWiseStats->total(),
                    'per_page' => $vacancyWiseStats->perPage(),
                ],
            ],
        ]);
    }
}
