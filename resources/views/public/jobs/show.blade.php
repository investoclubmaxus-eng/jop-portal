@extends('layouts.public')

@section('title', $job->job_title . ' - Career Opportunity')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

    <!-- Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('public.jobs.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to All Openings</span>
        </a>
        <span class="font-mono text-xs font-bold px-2.5 py-1 bg-slate-200 text-slate-800 rounded-lg">
            {{ $job->job_code }}
        </span>
    </div>

    <!-- Application Success Alert -->
    @if(session('application_success'))
        <div class="p-6 bg-emerald-50 border border-emerald-200 rounded-3xl text-emerald-900 shadow-sm animate-fadeIn">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-xl font-bold">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-emerald-900">Application Submitted Successfully!</h3>
                    <p class="text-xs text-emerald-700">
                        Thank you, <strong>{{ session('application_success')['name'] }}</strong>. Your application for <strong>{{ session('application_success')['title'] }}</strong> has been received by our recruitment team.
                    </p>
                    <div class="pt-2 flex items-center gap-2">
                        <span class="text-xs text-emerald-700 font-medium">Your Application Tracking Reference:</span>
                        <span class="font-mono text-xs font-bold px-2.5 py-1 bg-emerald-200/60 rounded-lg text-emerald-900">
                            {{ session('application_success')['code'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Expired Warning Banner (if visited via direct link) -->
    @if($job->is_expired)
        <div class="p-5 bg-rose-50 border border-rose-200 rounded-3xl text-rose-800 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 text-lg">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-rose-900">This Vacancy is Expired</h4>
                <p class="text-xs text-rose-700">
                    The application deadline for this opening passed on <strong>{{ $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'earlier' }}</strong>. New submissions are no longer accepted.
                </p>
            </div>
        </div>
    @endif

    <!-- Main Job Header Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-10 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-8 border-b border-slate-100">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ $job->department ?? 'General' }}
                    </span>
                    <span class="px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700">
                        {{ $job->employment_type ?? 'Full-time' }}
                    </span>
                    @if($job->is_expired)
                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            Closed / Expired
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Open for Applications
                        </span>
                    @endif
                </div>

                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                    {{ $job->job_title }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Designation: {{ $job->designation ?? 'Not specified' }} &bull; Position Ref: {{ $job->job_code }}
                </p>
            </div>

            <!-- Apply Now Action CTA -->
            <div>
                @if(!$job->is_expired)
                    <button type="button" onclick="openApplyModal()" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-extrabold rounded-2xl text-sm shadow-xl shadow-indigo-600/25 transition-all flex items-center justify-center gap-2">
                        <span>Apply for this Job</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                @else
                    <button type="button" disabled class="px-6 py-3 bg-slate-100 text-slate-400 font-bold rounded-2xl text-sm cursor-not-allowed">
                        Application Closed
                    </button>
                @endif
            </div>
        </div>

        <!-- Four Key Metrics Strip -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-6 border-b border-slate-100">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Salary Range</span>
                <span class="font-extrabold text-slate-900 text-sm mt-0.5 block">{{ $job->salary_formatted }}</span>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Experience Req.</span>
                <span class="font-extrabold text-slate-900 text-sm mt-0.5 block">{{ $job->experience_formatted }}</span>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Open Vacancies</span>
                <span class="font-extrabold text-slate-900 text-sm mt-0.5 block">{{ $job->vacancies }} Openings</span>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Application Deadline</span>
                <span class="font-extrabold {{ $job->is_expired ? 'text-rose-600' : 'text-indigo-600' }} text-sm mt-0.5 block">
                    {{ $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'Open until filled' }}
                </span>
                <span class="text-[11px] text-slate-400 font-medium">{{ $job->days_remaining }}</span>
            </div>
        </div>

        <!-- Two Column Detailed Specs -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 pt-8">

            <!-- Main Specs & Content -->
            <div class="lg:col-span-8 space-y-8">
                <!-- Description -->
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-align-left text-indigo-600"></i>
                        <span>Role Overview & Responsibilities</span>
                    </h3>
                    <div class="text-slate-700 text-sm whitespace-pre-line leading-relaxed bg-slate-50/50 p-6 rounded-3xl border border-slate-100">
                        {{ $job->job_description }}
                    </div>
                </div>

                <!-- Eligibility Criteria & Qualifications -->
                @if($job->required_qualifications)
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-graduation-cap text-indigo-600"></i>
                            <span>Eligibility & Required Qualifications</span>
                        </h3>
                        <div class="text-slate-700 text-sm whitespace-pre-line leading-relaxed bg-slate-50/50 p-6 rounded-3xl border border-slate-100">
                            {{ $job->required_qualifications }}
                        </div>
                    </div>
                @endif

                <!-- Required Skills -->
                @if(!empty($job->required_skills) && is_array($job->required_skills))
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-code text-indigo-600"></i>
                            <span>Required Skills & Tech Stack</span>
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($job->required_skills as $skill)
                                <span class="px-3.5 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 font-bold text-xs border border-indigo-100">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Sidebar: Quick Summary & Related Jobs -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-slate-50 rounded-3xl p-6 border border-slate-200/80 space-y-4 text-xs">
                    <h4 class="font-extrabold uppercase tracking-wider text-slate-900 border-b border-slate-200 pb-2">
                        Job Overview
                    </h4>
                    <div class="space-y-3 text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Position:</span>
                            <span class="font-bold text-slate-800">{{ $job->job_title }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Department:</span>
                            <span class="font-bold text-slate-800">{{ $job->department ?? 'General' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Type:</span>
                            <span class="font-bold text-slate-800">{{ $job->employment_type ?? 'Full-time' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Compensation:</span>
                            <span class="font-bold text-slate-800">{{ $job->salary_formatted }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Published:</span>
                            <span class="font-bold text-slate-800">{{ $job->published_at ? $job->published_at->format('M d, Y') : 'Recently' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Deadline:</span>
                            <span class="font-bold {{ $job->is_expired ? 'text-rose-600' : 'text-slate-800' }}">
                                {{ $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'Open until filled' }}
                            </span>
                        </div>
                    </div>

                    @if(!$job->is_expired)
                        <div class="pt-2">
                            <button type="button" onclick="openApplyModal()" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md shadow-indigo-600/20 transition-all">
                                Apply Now
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Related Positions -->
                @if($relatedJobs->isNotEmpty())
                    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 space-y-4">
                        <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                            Similar Opportunities
                        </h4>
                        <div class="space-y-3">
                            @foreach($relatedJobs as $rel)
                                <a href="{{ route('public.jobs.show', $rel->job_code) }}" class="block p-3 rounded-2xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100 group">
                                    <h5 class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                        {{ $rel->job_title }}
                                    </h5>
                                    <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                                        <span>{{ $rel->employment_type ?? 'Full-time' }}</span>
                                        <span>&bull;</span>
                                        <span class="text-indigo-600 font-semibold">{{ $rel->salary_formatted }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

</div>

<!-- Candidate Application Modal -->
@if(!$job->is_expired)
<div id="applyModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl border border-slate-200 w-full max-w-2xl overflow-hidden shadow-2xl my-8">
        <!-- Modal Header -->
        <div class="px-6 py-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Apply for {{ $job->job_title }}</h3>
                <p class="text-xs text-slate-500 font-mono">Job Code: {{ $job->job_code }}</p>
            </div>
            <button type="button" onclick="closeApplyModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Application Form -->
        <form method="POST" action="{{ route('public.jobs.apply', $job->job_code) }}" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            @csrf
            <input type="hidden" name="source" value="{{ old('source', $source ?? 'direct') }}">

            <!-- Name Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        First Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="first_name" required value="{{ old('first_name') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="John">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Last Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="last_name" required value="{{ old('last_name') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="Doe">
                </div>
            </div>

            <!-- Email & Phone -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Email Address <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="john.doe@example.com">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Phone Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="tel" name="phone" required value="{{ old('phone') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="+1 (555) 000-0000">
                </div>
            </div>

            <!-- Location & Qualification -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Current City / Location</label>
                    <input type="text" name="current_location" value="{{ old('current_location') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="e.g. New York, NY">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Highest Qualification</label>
                    <input type="text" name="highest_qualification" value="{{ old('highest_qualification') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="e.g. B.Tech in Computer Science">
                </div>
            </div>

            <!-- Total Experience & CTC -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Total Experience (Yrs)</label>
                    <input type="number" step="0.1" name="total_experience_years" value="{{ old('total_experience_years') }}" min="0"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="e.g. 4.5">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Current CTC ($)</label>
                    <input type="number" step="0.01" name="current_ctc" value="{{ old('current_ctc') }}" min="0"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="Annual current salary">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Expected CTC ($)</label>
                    <input type="number" step="0.01" name="expected_ctc" value="{{ old('expected_ctc') }}" min="0"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="Expected salary">
                </div>
            </div>

            <!-- Resume Upload -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Attach Resume / CV (PDF, DOC, DOCX - Max 5MB)
                </label>
                <input type="file" name="resume" accept=".pdf,.doc,.docx"
                       class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>

            <!-- Action buttons -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closeApplyModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Submit Application</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
    function openApplyModal() {
        const modal = document.getElementById('applyModal');
        if (modal) modal.classList.remove('hidden');
    }
    function closeApplyModal() {
        const modal = document.getElementById('applyModal');
        if (modal) modal.classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (@json($autoOpenApply ?? false) || window.location.hash === '#apply' || urlParams.has('apply')) {
            openApplyModal();
        }
    });
</script>
@endpush
@endsection
