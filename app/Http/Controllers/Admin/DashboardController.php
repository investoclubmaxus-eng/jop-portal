<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display recruitment dashboard with key metrics.
     */
    public function index(Request $request): View
    {
        // 1. Total job openings
        $totalJobOpenings = JobOpening::count();

        // 2. Active and expired vacancies
        $activeVacancies = JobOpening::active()->count();
        $expiredVacancies = JobOpening::expired()->count();
        $draftVacancies = JobOpening::draft()->count();

        // 3. Applications statistics
        $totalApplications = JobApplication::count();
        $applicationsToday = JobApplication::today()->count();
        $pendingApplications = JobApplication::pending()->count();
        $shortlistedApplications = JobApplication::shortlisted()->count();
        $rejectedApplications = JobApplication::rejected()->count();

        // 4. Applications transferred to CRM
        $crmTransferredApplications = JobApplication::transferredToCrm()->count();

        // 5. Recent applications list
        $recentApplications = JobApplication::with('jobOpening')
            ->latest('created_at')
            ->take(8)
            ->get();

        // 6. Vacancy-wise application statistics
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
        ->paginate(10, ['*'], 'vacancies_page')
        ->withQueryString();

        return view('admin.dashboard.index', compact(
            'totalJobOpenings',
            'activeVacancies',
            'expiredVacancies',
            'draftVacancies',
            'totalApplications',
            'applicationsToday',
            'pendingApplications',
            'shortlistedApplications',
            'rejectedApplications',
            'crmTransferredApplications',
            'recentApplications',
            'vacancyWiseStats'
        ));
    }
}
