@extends('layouts.public')

@section('title', 'Explore Careers & Open Vacancies')

@section('content')
<div class="space-y-12">

    <!-- Hero Search Banner -->
    <section class="bg-gradient-to-b from-indigo-900 via-slate-900 to-slate-900 text-white pt-16 pb-20 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#818cf8_1px,transparent_1px)] [background-size:16px_16px]"></div>

        <div class="max-w-4xl mx-auto text-center relative z-10 space-y-4">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>{{ $totalActiveVacancies }} Active Positions Open</span>
            </span>

            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white leading-tight">
                Find Your Next Career Move
            </h1>
            <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto font-medium">
                Browse our actively published openings, check eligibility criteria, and apply directly to connect with our talent team.
            </p>

            <!-- Search & Filters Container -->
            <form method="GET" action="{{ route('public.jobs.index') }}" class="pt-6">
                <div class="bg-white/10 backdrop-blur-xl p-3 rounded-3xl border border-white/20 shadow-2xl grid grid-cols-1 sm:grid-cols-12 gap-3">
                    <!-- Keyword input -->
                    <div class="sm:col-span-5 relative text-left">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Job title, department, or keywords..."
                               class="w-full pl-11 pr-4 py-3 bg-white text-slate-900 placeholder-slate-400 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Department dropdown -->
                    <div class="sm:col-span-3 text-left">
                        <select name="department" class="w-full px-4 py-3 bg-white text-slate-800 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Employment type dropdown -->
                    <div class="sm:col-span-2 text-left">
                        <select name="employment_type" class="w-full px-4 py-3 bg-white text-slate-800 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            @foreach($employmentTypes as $type)
                                <option value="{{ $type }}" {{ $employmentType === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Submit search -->
                    <div class="sm:col-span-2">
                        <button type="submit" class="w-full h-full py-3 px-6 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-400 hover:to-violet-500 text-white font-bold rounded-2xl text-sm shadow-lg shadow-indigo-500/30 transition-all flex items-center justify-center gap-2">
                            <span>Search</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>

                @if($search || $department || $employmentType)
                    <div class="mt-3 flex items-center justify-center gap-2 text-xs text-indigo-200">
                        <span>Active search filters</span>
                        <a href="{{ route('public.jobs.index') }}" class="underline font-bold hover:text-white">Clear all</a>
                    </div>
                @endif
            </form>
        </div>
    </section>

    <!-- Vacancies Grid Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Current Openings</h2>
                <p class="text-xs text-slate-500 mt-1">Showing active, unexpired job vacancies accepting applications</p>
            </div>
            <span class="text-xs font-bold text-slate-500 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm">
                {{ $jobs->total() }} Vacancies Available
            </span>
        </div>

        <!-- Job Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($jobs as $job)
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:shadow-xl hover:border-indigo-400 transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <!-- Header Badges -->
                        <div class="flex items-center justify-between gap-2 mb-4">
                            <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $job->department ?? 'General' }}
                            </span>
                            <span class="px-2.5 py-1 rounded-xl text-[11px] font-semibold bg-slate-100 text-slate-700">
                                {{ $job->employment_type ?? 'Full-time' }}
                            </span>
                        </div>

                        <!-- Job Title & Code -->
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors leading-snug">
                            <a href="{{ route('public.jobs.show', $job->job_code) }}">
                                {{ $job->job_title }}
                            </a>
                        </h3>
                        <p class="text-xs font-mono text-slate-400 mt-1">{{ $job->job_code }}</p>

                        <!-- Excerpt Description -->
                        <p class="text-xs text-slate-500 mt-3 line-clamp-3 leading-relaxed">
                            {{ Str::limit($job->job_description, 140) }}
                        </p>

                        <!-- Key Metrics List -->
                        <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                            <!-- Experience -->
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-briefcase text-slate-400 w-4"></i>
                                <span>Experience: <strong>{{ $job->experience_formatted }}</strong></span>
                            </div>

                            <!-- Salary -->
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-money-bill-wave text-slate-400 w-4"></i>
                                <span>Salary: <strong>{{ $job->salary_formatted }}</strong></span>
                            </div>

                            <!-- Vacancies -->
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-users text-slate-400 w-4"></i>
                                <span>Vacancies: <strong>{{ $job->vacancies }} Openings</strong></span>
                            </div>
                        </div>

                        <!-- Skills preview -->
                        @if(!empty($job->required_skills) && is_array($job->required_skills))
                            <div class="flex flex-wrap gap-1.5 mt-4">
                                @foreach(array_slice($job->required_skills, 0, 3) as $skill)
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-semibold">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                                @if(count($job->required_skills) > 3)
                                    <span class="px-1.5 py-0.5 text-slate-400 text-[10px] font-semibold">
                                        +{{ count($job->required_skills) - 3 }} more
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Card Footer: Deadline & Action -->
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Deadline</span>
                            <span class="text-xs font-bold text-slate-700">
                                {{ $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'Open until filled' }}
                            </span>
                        </div>

                        <a href="{{ route('public.jobs.show', $job->job_code) }}" class="px-4 py-2 bg-indigo-600 group-hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 flex items-center gap-1.5 transition-all">
                            <span>View & Apply</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-200">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">No active vacancies match your criteria</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        We currently have no open positions matching the selected filters. Please try searching different keywords or check back soon.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('public.jobs.index') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold inline-flex items-center gap-2">
                            Reset Search Filters
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($jobs->hasPages())
            <div class="mt-10">
                {{ $jobs->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
