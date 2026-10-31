@extends('layouts.admin')

@section('title', $job->job_title . ' - Details & Applications')
@section('page-title', $job->job_title)
@section('page-subtitle', 'Vacancies details, promotion & posting URLs, and candidate applications received')

@section('content')
<div class="space-y-8">

    <!-- Top Navigation & Action Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.jobs.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to All Openings</span>
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Public Listing Link -->
            <a href="{{ route('public.jobs.show', $job->job_code) }}" target="_blank" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Candidate View</span>
            </a>

            <!-- Direct Apply Page Link -->
            <a href="{{ route('public.jobs.apply-form', $job->job_code) }}" target="_blank" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 border border-indigo-200">
                <i class="fa-solid fa-file-signature"></i>
                <span>Direct Apply Form</span>
            </a>

            <!-- Edit Button -->
            <a href="{{ route('admin.jobs.edit', $job) }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Opening</span>
            </a>

            <!-- Quick Status Switch Form -->
            <form method="POST" action="{{ route('admin.jobs.update-status', $job) }}" class="inline-flex items-center gap-2">
                @csrf
                @method('PATCH')
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    <option value="published" {{ $job->status === 'published' ? 'selected' : '' }}>Set Status: Published</option>
                    <option value="active" {{ $job->status === 'active' ? 'selected' : '' }}>Set Status: Active</option>
                    <option value="draft" {{ $job->status === 'draft' ? 'selected' : '' }}>Set Status: Draft (Unpublish)</option>
                    <option value="closed" {{ $job->status === 'closed' ? 'selected' : '' }}>Set Status: Closed</option>
                    <option value="expired" {{ $job->status === 'expired' ? 'selected' : '' }}>Set Status: Expired</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Job Header Summary Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <div>
                <div class="flex flex-wrap items-center gap-2.5 mb-2">
                    <span class="font-mono text-xs font-bold text-indigo-600 px-2.5 py-1 bg-indigo-50 border border-indigo-200 rounded-lg">
                        {{ $job->job_code }}
                    </span>
                    @if($job->is_expired)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> Expired
                        </span>
                    @elseif($job->status === 'published' || $job->status === 'active')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Published & Active
                        </span>
                    @elseif($job->status === 'draft')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span> Draft (Hidden from Public)
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            {{ ucfirst($job->status) }}
                        </span>
                    @endif
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                        {{ $job->employment_type ?? 'Full-time' }}
                    </span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $job->job_title }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $job->department ?? 'General' }} &bull; Designation: {{ $job->designation ?? 'Not specified' }}
                </p>
            </div>

            <!-- Key Metric Mini Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/70 text-xs">
                <div>
                    <span class="text-slate-400 font-medium block">Vacancies:</span>
                    <span class="font-extrabold text-slate-900 text-sm">{{ $job->vacancies }} Openings</span>
                </div>
                <div>
                    <span class="text-slate-400 font-medium block">Applications:</span>
                    <span class="font-extrabold text-indigo-600 text-sm">{{ $appStats['total'] }} Total</span>
                </div>
                <div>
                    <span class="text-slate-400 font-medium block">Experience:</span>
                    <span class="font-extrabold text-slate-900 text-sm">{{ $job->experience_formatted }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-medium block">Closing Date:</span>
                    <span class="font-extrabold {{ $job->is_expired ? 'text-rose-600' : 'text-slate-900' }} text-sm">
                        {{ $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'No deadline' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Role Specifications -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-6">
            <!-- Left: Description & Qualifications -->
            <div class="lg:col-span-2 space-y-6">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Job Description</h3>
                    <div class="prose max-w-none text-sm text-slate-700 whitespace-pre-line bg-slate-50/50 p-5 rounded-2xl border border-slate-100 leading-relaxed">
                        {{ $job->job_description }}
                    </div>
                </div>

                @if($job->required_qualifications)
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Required Qualifications & Eligibility</h3>
                        <div class="text-sm text-slate-700 whitespace-pre-line bg-slate-50/50 p-5 rounded-2xl border border-slate-100 leading-relaxed">
                            {{ $job->required_qualifications }}
                        </div>
                    </div>
                @endif

                @if(!empty($job->required_skills) && is_array($job->required_skills))
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Required Skills & Stack</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($job->required_skills as $skill)
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-semibold text-xs rounded-xl border border-indigo-100">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right: Meta Information -->
            <div class="space-y-4">
                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 space-y-3.5 text-xs">
                    <h3 class="font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200 pb-2">Vacancy Parameters</h3>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Offered Salary:</span>
                        <span class="font-bold text-slate-800">{{ $job->salary_formatted }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Employment Type:</span>
                        <span class="font-bold text-slate-800">{{ $job->employment_type ?? 'Full-time' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Published Date:</span>
                        <span class="font-bold text-slate-800">{{ $job->published_at ? $job->published_at->format('M d, Y') : 'Not yet' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Application Deadline:</span>
                        <span class="font-bold {{ $job->is_expired ? 'text-rose-600' : 'text-slate-800' }}">
                            {{ $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'Open indefinitely' }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Created By Admin:</span>
                        <span class="font-bold text-slate-800">{{ $job->creator->name ?? 'Administrator' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Record Created:</span>
                        <span class="font-bold text-slate-800">{{ $job->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <!-- Screening Pipeline Summary -->
                <div class="bg-indigo-50/50 rounded-2xl p-5 border border-indigo-100 space-y-3 text-xs">
                    <h3 class="font-bold uppercase tracking-wider text-indigo-900 border-b border-indigo-100 pb-2">Screening Breakdown</h3>
                    <div class="grid grid-cols-2 gap-2 text-center">
                        <div class="bg-white p-2.5 rounded-xl border border-indigo-100">
                            <span class="text-slate-400 block text-[10px]">PENDING</span>
                            <span class="font-bold text-amber-600 text-sm">{{ $appStats['pending'] }}</span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-indigo-100">
                            <span class="text-slate-400 block text-[10px]">SHORTLISTED</span>
                            <span class="font-bold text-blue-600 text-sm">{{ $appStats['shortlisted'] }}</span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-indigo-100">
                            <span class="text-slate-400 block text-[10px]">REJECTED</span>
                            <span class="font-bold text-rose-600 text-sm">{{ $appStats['rejected'] }}</span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-indigo-100">
                            <span class="text-slate-400 block text-[10px]">CRM TRANSFERRED</span>
                            <span class="font-bold text-teal-600 text-sm">{{ $appStats['crm_transferred'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NEW SECTION: Job Posting & Social Promotion URL Generator (LinkedIn, Naukri, Indeed, Direct) -->
    @php
        $directJobUrl = route('public.jobs.show', $job->job_code);
        $directApplyUrl = route('public.jobs.apply-form', $job->job_code);
        $linkedinUrl = route('public.jobs.apply-form', ['jobCode' => $job->job_code, 'source' => 'linkedin']);
        $naukriUrl = route('public.jobs.apply-form', ['jobCode' => $job->job_code, 'source' => 'naukri']);
        $indeedUrl = route('public.jobs.apply-form', ['jobCode' => $job->job_code, 'source' => 'indeed']);
        $whatsappUrl = route('public.jobs.apply-form', ['jobCode' => $job->job_code, 'source' => 'whatsapp']);
    @endphp

    <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-300 flex items-center justify-center text-lg font-bold border border-indigo-400/30">
                    <i class="fa-solid fa-share-nodes"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white tracking-tight">Social & Job Board Promotion URLs</h3>
                    <p class="text-xs text-slate-400">Share these pre-configured links directly on LinkedIn, Naukri, or Indeed to open the application form immediately.</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-xl text-xs font-bold">
                Auto Source Tracking Enabled
            </span>
        </div>

        <!-- Pre-generated Portal Links Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- 1. LinkedIn Optimized Link -->
            <div class="p-4 bg-slate-800/80 rounded-2xl border border-slate-700/80 space-y-2 hover:border-sky-500/60 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-brands fa-linkedin text-sky-400 text-lg"></i>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">LinkedIn Post Link (Direct Apply)</span>
                    </div>
                    <span class="text-[10px] text-sky-300 font-mono bg-sky-950/60 px-2 py-0.5 rounded border border-sky-800">source=linkedin</span>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $linkedinUrl }}" id="url-linkedin"
                           class="w-full px-3 py-2 bg-slate-900/90 border border-slate-700 rounded-xl text-xs font-mono text-slate-300 focus:outline-none">
                    <button type="button" onclick="copyToClipboard('url-linkedin', 'LinkedIn Link Copied!')"
                            class="px-3 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-bold shrink-0 transition-colors flex items-center gap-1.5" title="Copy to clipboard">
                        <i class="fa-regular fa-copy"></i>
                        <span>Copy</span>
                    </button>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($linkedinUrl) }}" target="_blank"
                       class="p-2 bg-slate-700 hover:bg-slate-600 text-sky-300 rounded-xl text-xs shrink-0 transition-colors" title="Share directly on LinkedIn">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>

            <!-- 2. Naukri.com Link -->
            <div class="p-4 bg-slate-800/80 rounded-2xl border border-slate-700/80 space-y-2 hover:border-blue-500/60 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-briefcase text-blue-400 text-base"></i>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">Naukri.com Post Link</span>
                    </div>
                    <span class="text-[10px] text-blue-300 font-mono bg-blue-950/60 px-2 py-0.5 rounded border border-blue-800">source=naukri</span>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $naukriUrl }}" id="url-naukri"
                           class="w-full px-3 py-2 bg-slate-900/90 border border-slate-700 rounded-xl text-xs font-mono text-slate-300 focus:outline-none">
                    <button type="button" onclick="copyToClipboard('url-naukri', 'Naukri Link Copied!')"
                            class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold shrink-0 transition-colors flex items-center gap-1.5" title="Copy to clipboard">
                        <i class="fa-regular fa-copy"></i>
                        <span>Copy</span>
                    </button>
                </div>
            </div>

            <!-- 3. Indeed Link -->
            <div class="p-4 bg-slate-800/80 rounded-2xl border border-slate-700/80 space-y-2 hover:border-indigo-500/60 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-id-card-clip text-indigo-400 text-base"></i>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">Indeed Post Link</span>
                    </div>
                    <span class="text-[10px] text-indigo-300 font-mono bg-indigo-950/60 px-2 py-0.5 rounded border border-indigo-800">source=indeed</span>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $indeedUrl }}" id="url-indeed"
                           class="w-full px-3 py-2 bg-slate-900/90 border border-slate-700 rounded-xl text-xs font-mono text-slate-300 focus:outline-none">
                    <button type="button" onclick="copyToClipboard('url-indeed', 'Indeed Link Copied!')"
                            class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shrink-0 transition-colors flex items-center gap-1.5" title="Copy to clipboard">
                        <i class="fa-regular fa-copy"></i>
                        <span>Copy</span>
                    </button>
                </div>
            </div>

            <!-- 4. WhatsApp / Candidate Direct Message Link -->
            <div class="p-4 bg-slate-800/80 rounded-2xl border border-slate-700/80 space-y-2 hover:border-emerald-500/60 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-400 text-lg"></i>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">WhatsApp & Direct Messaging</span>
                    </div>
                    <span class="text-[10px] text-emerald-300 font-mono bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-800">source=whatsapp</span>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $whatsappUrl }}" id="url-whatsapp"
                           class="w-full px-3 py-2 bg-slate-900/90 border border-slate-700 rounded-xl text-xs font-mono text-slate-300 focus:outline-none">
                    <button type="button" onclick="copyToClipboard('url-whatsapp', 'WhatsApp Link Copied!')"
                            class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shrink-0 transition-colors flex items-center gap-1.5" title="Copy to clipboard">
                        <i class="fa-regular fa-copy"></i>
                        <span>Copy</span>
                    </button>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode('We are hiring for ' . $job->job_title . '! Apply directly here: ' . $whatsappUrl) }}" target="_blank"
                       class="p-2 bg-slate-700 hover:bg-slate-600 text-emerald-300 rounded-xl text-xs shrink-0 transition-colors" title="Open WhatsApp">
                        <i class="fa-solid fa-paper-plane"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Custom Channel URL Generator -->
        <div class="p-4 bg-slate-800/50 rounded-2xl border border-slate-700/60 space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-300 block">Create Custom Campaign Tracking URL</span>
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="w-full sm:w-1/3">
                    <input type="text" id="custom-source-input" placeholder="Type channel name (e.g. facebook, email_blast)"
                           onkeyup="updateCustomUrl()"
                           class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="w-full sm:w-2/3 flex items-center gap-2">
                    <input type="text" readonly id="custom-url-output" value="{{ $directApplyUrl }}?source=custom"
                           class="w-full px-3 py-2 bg-slate-900/90 border border-slate-700 rounded-xl text-xs font-mono text-slate-300 focus:outline-none">
                    <button type="button" onclick="copyToClipboard('custom-url-output', 'Custom Campaign URL Copied!')"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shrink-0 transition-colors flex items-center gap-1.5">
                        <i class="fa-regular fa-copy"></i>
                        <span>Copy</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Applications Received for Each Vacancy -->
    <div id="applications" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-users text-indigo-600"></i>
                    <span>Applications Received for this Vacancy</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Candidates who submitted applications for {{ $job->job_title }}</p>
            </div>
            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-bold rounded-xl text-xs">
                {{ $applications->total() }} Candidates
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Applicant</th>
                        <th class="py-3.5 px-4">Contact Info</th>
                        <th class="py-3.5 px-3">Acquisition Source</th>
                        <th class="py-3.5 px-4">Experience & Location</th>
                        <th class="py-3.5 px-4">Current / Expected CTC</th>
                        <th class="py-3.5 px-4">Screening Status</th>
                        <th class="py-3.5 px-4">CRM Sync</th>
                        <th class="py-3.5 px-6 text-right">Submitted Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($applications as $app)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Applicant Name & Code -->
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900 text-sm">{{ $app->full_name }}</div>
                                <div class="font-mono text-indigo-600 text-[11px] mt-0.5">{{ $app->application_code }}</div>
                            </td>

                            <!-- Contact -->
                            <td class="py-4 px-4 text-slate-600">
                                <div class="font-bold text-slate-800">{{ $app->email }}</div>
                                <div class="text-[11px] text-slate-400">{{ $app->phone }}</div>
                            </td>

                            <!-- Acquisition Source Badge -->
                            <td class="py-4 px-3">
                                @php
                                    $src = strtolower($app->source ?? 'direct');
                                @endphp
                                @if($src === 'linkedin')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                        <i class="fa-brands fa-linkedin text-sky-600"></i> LinkedIn
                                    </span>
                                @elseif($src === 'naukri')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fa-solid fa-briefcase text-blue-600"></i> Naukri
                                    </span>
                                @elseif($src === 'indeed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fa-solid fa-id-card-clip text-indigo-600"></i> Indeed
                                    </span>
                                @elseif($src === 'whatsapp')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-brands fa-whatsapp text-emerald-600"></i> WhatsApp
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="fa-solid fa-globe text-slate-400"></i> {{ ucfirst($src) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Experience & Location -->
                            <td class="py-4 px-4 text-slate-600">
                                <div>{{ $app->total_experience_years ? $app->total_experience_years . ' yrs' : 'Fresher' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $app->current_location ?? 'Not provided' }}</div>
                            </td>

                            <!-- CTC -->
                            <td class="py-4 px-4 text-slate-600">
                                <div>{{ $app->current_ctc ? '$' . number_format($app->current_ctc, 0) : 'N/A' }}</div>
                                <div class="text-[11px] text-indigo-600 font-semibold">Exp: {{ $app->expected_ctc ? '$' . number_format($app->expected_ctc, 0) : 'N/A' }}</div>
                            </td>

                            <!-- Screening Status -->
                            <td class="py-4 px-4">
                                @if(in_array($app->status, ['pending', 'submitted', 'under_review']))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                    </span>
                                @elseif($app->status === 'shortlisted')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Shortlisted
                                    </span>
                                @elseif($app->status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($app->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- CRM Sync -->
                            <td class="py-4 px-4">
                                @if(in_array($app->crm_transfer_status, ['transferred', 'completed', 'success']))
                                    <span class="inline-flex items-center gap-1 text-teal-700 font-bold">
                                        <i class="fa-solid fa-circle-check text-teal-500"></i> Synced
                                    </span>
                                @elseif($app->crm_transfer_status === 'failed')
                                    <span class="inline-flex items-center gap-1 text-rose-600 font-semibold" title="{{ $app->crm_transfer_error }}">
                                        <i class="fa-solid fa-circle-xmark"></i> Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-400">
                                        <i class="fa-regular fa-clock"></i> Pending
                                    </span>
                                @endif
                            </td>

                            <!-- Date -->
                            <td class="py-4 px-6 text-right text-slate-500">
                                <div class="font-bold text-slate-800">{{ $app->created_at->format('M d, Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $app->created_at->format('h:i A') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300"></i>
                                <p class="font-bold text-slate-700">No applications received yet for this vacancy</p>
                                <p class="text-xs text-slate-400 mt-1">Copy and post the promotion links above to LinkedIn, Naukri, or Indeed to start receiving candidate submissions.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $applications->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Copy Toast Notification Popup -->
<div id="copyToast" class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl border border-slate-700 flex items-center gap-3 text-xs font-bold hidden transform transition-all duration-300">
    <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
    <span id="copyToastMsg">Link copied to clipboard!</span>
</div>

@push('scripts')
<script>
    const baseUrl = @json($directApplyUrl);

    function copyToClipboard(elementId, msg) {
        const input = document.getElementById(elementId);
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(function() {
            showToast(msg || 'Copied to clipboard!');
        });
    }

    function updateCustomUrl() {
        const sourceVal = document.getElementById('custom-source-input').value.trim();
        const output = document.getElementById('custom-url-output');
        if (sourceVal) {
            output.value = baseUrl + '?source=' + encodeURIComponent(sourceVal);
        } else {
            output.value = baseUrl;
        }
    }

    function showToast(msg) {
        const toast = document.getElementById('copyToast');
        const toastMsg = document.getElementById('copyToastMsg');
        toastMsg.innerText = msg;
        toast.classList.remove('hidden');
        toast.classList.add('animate-fadeIn');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }
</script>
@endpush
@endsection
