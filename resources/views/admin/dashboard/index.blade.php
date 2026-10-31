@extends('layouts.admin')

@section('title', 'Recruitment Dashboard')
@section('page-title', 'Recruitment Dashboard')
@section('page-subtitle', 'Real-time overview of vacancies, candidate applications, screening pipeline, and CRM sync')

@section('content')
<div class="space-y-8">

    <!-- KPI Metrics Section: 8 Primary Counters -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-indigo-600"></i>
                <span>Key Recruitment Metrics</span>
            </h2>
            <span class="text-xs text-slate-400">Refreshed live</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-4">

            <!-- 1. Total Job Openings -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Job Openings</span>
                    <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base font-bold shadow-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-briefcase"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900">{{ number_format($totalJobOpenings) }}</span>
                    <span class="text-xs font-semibold text-slate-400">Total</span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5 text-emerald-600 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $activeVacancies }} Active
                    </span>
                    <span class="flex items-center gap-1.5 text-rose-500 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span> {{ $expiredVacancies }} Expired
                    </span>
                </div>
            </div>

            <!-- 2. Active Vacancies -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Vacancies</span>
                    <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold shadow-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-600">{{ number_format($activeVacancies) }}</span>
                    <span class="text-xs font-semibold text-emerald-500">Open for Apply</span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Expired / Closed:</span>
                    <span class="font-bold text-slate-700">{{ $expiredVacancies }}</span>
                </div>
            </div>

            <!-- 3. Total Applications Received -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Applications</span>
                    <span class="w-10 h-10 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-base font-bold shadow-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-file-lines"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900">{{ number_format($totalApplications) }}</span>
                    <span class="text-xs font-semibold text-slate-400">All Time</span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Candidates Pool</span>
                    <span class="font-bold text-violet-600">Active Pipeline</span>
                </div>
            </div>

            <!-- 4. Applications Received Today -->
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 text-white rounded-3xl p-5 shadow-lg shadow-indigo-600/20 relative overflow-hidden group">
                <div class="flex items-center justify-between text-indigo-100">
                    <span class="text-xs font-bold uppercase tracking-wider">Received Today</span>
                    <span class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur-md text-white flex items-center justify-center text-base font-bold group-hover:rotate-12 transition-transform">
                        <i class="fa-solid fa-bolt"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-white">{{ number_format($applicationsToday) }}</span>
                    <span class="text-xs font-medium text-indigo-200">New submissions</span>
                </div>
                <div class="mt-3 pt-3 border-t border-white/15 flex items-center justify-between text-xs text-indigo-100">
                    <span>Today's Date:</span>
                    <span class="font-bold text-white">{{ now()->format('d M Y') }}</span>
                </div>
            </div>

            <!-- 5. Pending Applications -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Review</span>
                    <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold shadow-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-amber-600">{{ number_format($pendingApplications) }}</span>
                    <span class="text-xs font-semibold text-amber-600/80">Awaiting screening</span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-400">
                    {{ $totalApplications > 0 ? round(($pendingApplications / $totalApplications) * 100, 1) : 0 }}% of total applications
                </div>
            </div>

            <!-- 6. Shortlisted Applications -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Shortlisted</span>
                    <span class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shadow-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-user-check"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-blue-600">{{ number_format($shortlistedApplications) }}</span>
                    <span class="text-xs font-semibold text-blue-500">Qualified</span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-400">
                    {{ $totalApplications > 0 ? round(($shortlistedApplications / $totalApplications) * 100, 1) : 0 }}% qualification rate
                </div>
            </div>

            <!-- 7. Rejected Applications -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rejected</span>
                    <span class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-base font-bold shadow-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-user-xmark"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-rose-600">{{ number_format($rejectedApplications) }}</span>
                    <span class="text-xs font-semibold text-rose-500">Disqualified</span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-400">
                    {{ $totalApplications > 0 ? round(($rejectedApplications / $totalApplications) * 100, 1) : 0 }}% rejection rate
                </div>
            </div>

            <!-- 8. Transferred to CRM -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Transferred to CRM</span>
                    <span class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-base font-bold shadow-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-teal-600">{{ number_format($crmTransferredApplications) }}</span>
                    <span class="text-xs font-semibold text-teal-600/80">Synced</span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-teal-500"></span> CRM Integration Active
                </div>
            </div>

        </div>
    </div>

    <!-- Pipeline Funnel Visual Strip -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i class="fa-solid fa-filter text-indigo-600"></i>
            <span>Application Screening Funnel</span>
        </h3>

        @php
            $totalCount = max($totalApplications, 1);
            $pendingPercent = round(($pendingApplications / $totalCount) * 100, 1);
            $shortlistPercent = round(($shortlistedApplications / $totalCount) * 100, 1);
            $crmPercent = round(($crmTransferredApplications / $totalCount) * 100, 1);
            $rejectedPercent = round(($rejectedApplications / $totalCount) * 100, 1);
        @endphp

        <!-- Multi-segment Progress Bar -->
        <div class="w-full h-4 bg-slate-100 rounded-full overflow-hidden flex shadow-inner">
            <div style="width: {{ $pendingPercent }}%" class="bg-amber-400 h-full transition-all duration-500" title="Pending: {{ $pendingApplications }} ({{ $pendingPercent }}%)"></div>
            <div style="width: {{ $shortlistPercent }}%" class="bg-blue-500 h-full transition-all duration-500" title="Shortlisted: {{ $shortlistedApplications }} ({{ $shortlistPercent }}%)"></div>
            <div style="width: {{ $crmPercent }}%" class="bg-teal-500 h-full transition-all duration-500" title="CRM Transferred: {{ $crmTransferredApplications }} ({{ $crmPercent }}%)"></div>
            <div style="width: {{ $rejectedPercent }}%" class="bg-rose-400 h-full transition-all duration-500" title="Rejected: {{ $rejectedApplications }} ({{ $rejectedPercent }}%)"></div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4 pt-2 text-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-amber-400 shrink-0"></span>
                <div>
                    <span class="font-bold text-slate-800">{{ $pendingApplications }}</span>
                    <span class="text-slate-500">Pending ({{ $pendingPercent }}%)</span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-blue-500 shrink-0"></span>
                <div>
                    <span class="font-bold text-slate-800">{{ $shortlistedApplications }}</span>
                    <span class="text-slate-500">Shortlisted ({{ $shortlistPercent }}%)</span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-teal-500 shrink-0"></span>
                <div>
                    <span class="font-bold text-slate-800">{{ $crmTransferredApplications }}</span>
                    <span class="text-slate-500">CRM Transferred ({{ $crmPercent }}%)</span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-rose-400 shrink-0"></span>
                <div>
                    <span class="font-bold text-slate-800">{{ $rejectedApplications }}</span>
                    <span class="text-slate-500">Rejected ({{ $rejectedPercent }}%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Vacancy-wise Application Statistics -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-indigo-600"></i>
                    <span>Vacancy-Wise Application Statistics</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Detailed breakdown of applications across individual job openings</p>
            </div>
            <div class="text-xs text-slate-500 font-semibold bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200/80">
                Total Listed: {{ $vacancyWiseStats->total() }} openings
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Job Vacancy</th>
                        <th class="py-3.5 px-4">Department</th>
                        <th class="py-3.5 px-3 text-center">Status</th>
                        <th class="py-3.5 px-3 text-center">Vacancies</th>
                        <th class="py-3.5 px-3 text-center">Total Applied</th>
                        <th class="py-3.5 px-3 text-center text-amber-600">Pending</th>
                        <th class="py-3.5 px-3 text-center text-blue-600">Shortlisted</th>
                        <th class="py-3.5 px-3 text-center text-rose-600">Rejected</th>
                        <th class="py-3.5 px-3 text-center text-teal-600">CRM Transferred</th>
                        <th class="py-3.5 px-6 text-right">Deadline</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($vacancyWiseStats as $vacancy)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900 text-sm">{{ $vacancy->job_title }}</div>
                                <div class="flex items-center gap-2 text-slate-400 text-xs mt-0.5">
                                    <span class="font-mono text-indigo-600 font-semibold">{{ $vacancy->job_code }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $vacancy->employment_type ?? 'Full-time' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-slate-600">
                                <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-700 font-semibold">
                                    {{ $vacancy->department ?? 'General' }}
                                </span>
                            </td>
                            <td class="py-4 px-3 text-center">
                                @if($vacancy->is_expired)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Expired
                                    </span>
                                @elseif(in_array($vacancy->status, ['published', 'active']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ ucfirst($vacancy->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-3 text-center font-bold text-slate-800">
                                {{ $vacancy->vacancies }}
                            </td>
                            <td class="py-4 px-3 text-center font-black text-slate-900 bg-slate-50/40">
                                {{ $vacancy->total_applications_count }}
                            </td>
                            <td class="py-4 px-3 text-center font-bold text-amber-600">
                                {{ $vacancy->pending_count }}
                            </td>
                            <td class="py-4 px-3 text-center font-bold text-blue-600">
                                {{ $vacancy->shortlisted_count }}
                            </td>
                            <td class="py-4 px-3 text-center font-bold text-rose-600">
                                {{ $vacancy->rejected_count }}
                            </td>
                            <td class="py-4 px-3 text-center font-bold text-teal-600">
                                {{ $vacancy->crm_transferred_count }}
                            </td>
                            <td class="py-4 px-6 text-right text-slate-500">
                                @if($vacancy->application_deadline)
                                    <span class="{{ $vacancy->is_expired ? 'text-rose-500 font-semibold' : 'text-slate-700 font-medium' }}">
                                        {{ $vacancy->application_deadline->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">No deadline</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-10 text-center text-slate-400">
                                <i class="fa-solid fa-briefcase text-3xl mb-2 text-slate-300"></i>
                                <p class="font-bold text-slate-700">No job openings created yet</p>
                                <p class="text-xs text-slate-400 mt-1">Start by publishing vacancy openings to view detailed metrics.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vacancyWiseStats->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $vacancyWiseStats->links() }}
            </div>
        @endif
    </div>

    <!-- Section: Recent Applications Received -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-regular fa-clock text-indigo-600"></i>
                    <span>Recent Applications Received</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Latest applicant submissions requiring screening</p>
            </div>
            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-xl">
                Showing {{ $recentApplications->count() }} recent
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Candidate</th>
                        <th class="py-3.5 px-4">Applied Position</th>
                        <th class="py-3.5 px-4">Experience & Location</th>
                        <th class="py-3.5 px-4">Screening Status</th>
                        <th class="py-3.5 px-4">CRM Sync Status</th>
                        <th class="py-3.5 px-6 text-right">Submission Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($recentApplications as $app)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900 text-sm">{{ $app->full_name }}</div>
                                <div class="text-slate-400 text-xs flex items-center gap-2 mt-0.5">
                                    <span class="text-indigo-600 font-mono">{{ $app->application_code }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $app->email }}</span>
                                    @if($app->source && $app->source !== 'direct')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $app->source === 'linkedin' ? 'bg-sky-50 text-sky-700' : ($app->source === 'naukri' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                            {{ $app->source }}
                                        </span>
                                    @endif
                                    <span>&bull;</span>
                                    <span>{{ $app->phone }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-800 block text-xs">{{ $app->jobOpening->job_title ?? 'Unknown Job' }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $app->jobOpening->job_code ?? '' }}</span>
                            </td>
                            <td class="py-4 px-4 text-slate-600">
                                <div>{{ $app->total_experience_years ? $app->total_experience_years . ' yrs exp' : 'Fresher' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $app->current_location ?? 'Not specified' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                @if(in_array($app->status, ['pending', 'submitted', 'under_review']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                    </span>
                                @elseif($app->status === 'shortlisted')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Shortlisted
                                    </span>
                                @elseif($app->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Rejected
                                    </span>
                                @elseif($app->status === 'hired')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Hired
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($app->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if(in_array($app->crm_transfer_status, ['transferred', 'completed', 'success']))
                                    <span class="inline-flex items-center gap-1 text-teal-700 font-bold">
                                        <i class="fa-solid fa-circle-check text-teal-500"></i> Synced to CRM
                                    </span>
                                @elseif($app->crm_transfer_status === 'failed')
                                    <span class="inline-flex items-center gap-1 text-rose-600 font-semibold" title="{{ $app->crm_transfer_error }}">
                                        <i class="fa-solid fa-circle-xmark"></i> Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-400">
                                        <i class="fa-regular fa-clock"></i> Pending Sync
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right text-slate-500">
                                <div class="font-bold text-slate-800">{{ $app->created_at->format('M d, Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $app->created_at->format('h:i A') }} ({{ $app->created_at->diffForHumans() }})</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300"></i>
                                <p class="font-bold text-slate-700">No applications received yet</p>
                                <p class="text-xs text-slate-400 mt-1">New applicant submissions will be listed here automatically.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
