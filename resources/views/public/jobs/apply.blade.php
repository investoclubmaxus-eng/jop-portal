@extends('layouts.public')

@section('title', 'Apply for ' . $job->job_title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

    <!-- Top Breadcrumb Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('public.jobs.show', $job->job_code) }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>View Complete Job Description</span>
        </a>
        <div class="flex items-center gap-2">
            @if($source && $source !== 'direct')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <i class="fa-solid fa-bullhorn text-[11px]"></i>
                    <span>Channel: {{ ucfirst($source) }}</span>
                </span>
            @endif
            <span class="font-mono text-xs font-bold px-2.5 py-1 bg-slate-200 text-slate-800 rounded-lg">
                {{ $job->job_code }}
            </span>
        </div>
    </div>

    <!-- Application Success Alert -->
    @if(session('application_success'))
        <div class="p-8 bg-emerald-50 border border-emerald-200 rounded-3xl text-emerald-900 shadow-sm animate-fadeIn">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-2xl font-bold">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="space-y-2">
                    <h2 class="text-xl font-black text-emerald-900">Application Submitted Successfully!</h2>
                    <p class="text-sm text-emerald-800">
                        Thank you, <strong>{{ session('application_success')['name'] }}</strong>. Your application for <strong>{{ session('application_success')['title'] }}</strong> has been officially logged into our candidate database.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <span class="text-xs text-emerald-700 font-semibold">Tracking Code:</span>
                        <span class="font-mono text-sm font-black px-3 py-1.5 bg-emerald-200/80 rounded-xl text-emerald-950 border border-emerald-300">
                            {{ session('application_success')['code'] }}
                        </span>
                    </div>
                    <div class="pt-4 flex items-center gap-4">
                        <a href="{{ route('public.jobs.index') }}" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition-colors">
                            Explore Other Openings
                        </a>
                        <a href="{{ route('public.jobs.show', $job->job_code) }}" class="text-xs font-bold text-emerald-800 hover:underline">
                            Return to Position Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Expired Warning Banner (if visited after deadline) -->
    @if($job->is_expired)
        <div class="p-6 bg-rose-50 border border-rose-200 rounded-3xl text-rose-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 text-xl">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <div>
                <h3 class="font-bold text-base text-rose-900">This Position is Closed</h3>
                <p class="text-xs text-rose-700 mt-0.5">
                    The application deadline for <strong>{{ $job->job_title }}</strong> passed on <strong>{{ $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'earlier' }}</strong>.
                </p>
                <div class="mt-3">
                    <a href="{{ route('public.jobs.index') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-2">
                        Browse Other Active Openings
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Job Highlight Header Card -->
        <div class="bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-2.5 py-0.5 rounded-lg bg-indigo-500/30 text-indigo-200 font-bold uppercase tracking-wider border border-indigo-400/30">
                            {{ $job->department ?? 'General' }}
                        </span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-slate-300 font-semibold">{{ $job->employment_type ?? 'Full-time' }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $job->job_title }}</h1>
                    <p class="text-xs text-slate-300">
                        Designation: {{ $job->designation ?? 'Not specified' }} &bull; Openings: {{ $job->vacancies }}
                    </p>
                </div>

                <div class="text-left sm:text-right bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10 text-xs">
                    <span class="text-indigo-200 font-medium block">Compensation:</span>
                    <span class="text-white font-extrabold text-sm">{{ $job->salary_formatted }}</span>
                    <span class="text-slate-400 block text-[11px] mt-0.5">Exp: {{ $job->experience_formatted }}</span>
                </div>
            </div>

            <div class="pt-4 flex flex-wrap items-center justify-between text-xs text-slate-300 gap-2">
                <span>Application Deadline: <strong class="text-white">{{ $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'Open until filled' }}</strong> ({{ $job->days_remaining }})</span>
                @if($source && $source !== 'direct')
                    <span class="text-indigo-300 font-bold">Referral Source: {{ ucfirst($source) }}</span>
                @endif
            </div>
        </div>

        <!-- Direct Application Form Card -->
        @if(!session('application_success'))
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-10 shadow-sm space-y-6">
            <div class="pb-4 border-b border-slate-100">
                <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-file-signature text-indigo-600"></i>
                    <span>Candidate Application Form</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1">Please provide accurate personal, contact, and professional details to submit your candidacy.</p>
            </div>

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs space-y-1">
                    <div class="font-bold flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                        <span>Please correct the errors before submitting:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 pl-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('public.jobs.apply', $job->job_code) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="source" value="{{ old('source', $source) }}">

                <!-- Full Name -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="first_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            First Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="first_name" name="first_name" required value="{{ old('first_name') }}"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                               placeholder="e.g. John">
                    </div>

                    <div>
                        <label for="last_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Last Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="last_name" name="last_name" required value="{{ old('last_name') }}"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                               placeholder="e.g. Doe">
                    </div>
                </div>

                <!-- Email & Phone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" required value="{{ old('email') }}"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                               placeholder="john.doe@example.com">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Phone / Mobile Number <span class="text-rose-500">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" required value="{{ old('phone') }}"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                               placeholder="+1 (555) 000-0000">
                    </div>
                </div>

                <!-- Location & Qualification -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="current_location" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Current City / Country
                        </label>
                        <input type="text" id="current_location" name="current_location" value="{{ old('current_location') }}"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                               placeholder="e.g. San Francisco, CA">
                    </div>

                    <div>
                        <label for="highest_qualification" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Highest Educational Qualification
                        </label>
                        <input type="text" id="highest_qualification" name="highest_qualification" value="{{ old('highest_qualification') }}"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                               placeholder="e.g. B.Tech in Computer Science / MBA">
                    </div>
                </div>

                <!-- Total Experience, Current CTC, Expected CTC -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label for="total_experience_years" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Experience (Years)
                        </label>
                        <input type="number" step="0.1" id="total_experience_years" name="total_experience_years" value="{{ old('total_experience_years') }}" min="0"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                               placeholder="e.g. 4.5">
                    </div>

                    <div>
                        <label for="current_ctc" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Current CTC ($ USD / Year)
                        </label>
                        <input type="number" step="0.01" id="current_ctc" name="current_ctc" value="{{ old('current_ctc') }}" min="0"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                               placeholder="Current salary">
                    </div>

                    <div>
                        <label for="expected_ctc" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Expected CTC ($ USD / Year)
                        </label>
                        <input type="number" step="0.01" id="expected_ctc" name="expected_ctc" value="{{ old('expected_ctc') }}" min="0"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                               placeholder="Expected compensation">
                    </div>
                </div>

                <!-- Resume Upload Box -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Attach Resume / CV (PDF, DOC, DOCX - Max 5MB)
                    </label>
                    <div class="p-6 bg-slate-50 border-2 border-dashed border-slate-300 rounded-2xl text-center hover:border-indigo-500 transition-colors">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-indigo-500 mb-2"></i>
                        <p class="text-xs font-bold text-slate-800">Upload your latest resume document</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Supports PDF, DOC, DOCX up to 5 Megabytes</p>
                        <input type="file" name="resume" accept=".pdf,.doc,.docx"
                               class="mt-3 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 file:cursor-pointer cursor-pointer">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-400">
                        By submitting, you agree to have your profile reviewed by our recruitment team.
                    </p>

                    <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-black rounded-2xl text-sm shadow-xl shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
                        <span>Submit Job Application</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>
        </div>
        @endif
    @endif

</div>
@endsection
