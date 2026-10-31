@extends('layouts.admin')

@section('title', 'Job Openings & Vacancies')
@section('page-title', 'Job Openings Management')
@section('page-subtitle', 'Create, publish, edit vacancies, track application deadlines, and monitor candidate submissions')

@section('content')
<div class="space-y-6">

    <!-- Top Summary Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <a href="{{ route('admin.jobs.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-indigo-500 transition-colors {{ $status === 'all' ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Openings</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold"><i class="fa-solid fa-briefcase"></i></span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $stats['total'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">All vacancy records</p>
        </a>

        <a href="{{ route('admin.jobs.index', ['status' => 'active']) }}" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-emerald-500 transition-colors {{ $status === 'active' ? 'ring-2 ring-emerald-500' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active & Open</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold"><i class="fa-solid fa-circle-check"></i></span>
            </div>
            <div class="text-2xl font-black text-emerald-600 mt-2">{{ $stats['active'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Accepting candidates</p>
        </a>

        <a href="{{ route('admin.jobs.index', ['status' => 'draft']) }}" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-slate-500 transition-colors {{ $status === 'draft' ? 'ring-2 ring-slate-500' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Drafts</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-sm font-bold"><i class="fa-solid fa-file-pen"></i></span>
            </div>
            <div class="text-2xl font-black text-slate-700 mt-2">{{ $stats['draft'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Not yet published</p>
        </a>

        <a href="{{ route('admin.jobs.index', ['status' => 'expired']) }}" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-rose-500 transition-colors {{ $status === 'expired' ? 'ring-2 ring-rose-500' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Expired / Closed</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold"><i class="fa-solid fa-calendar-xmark"></i></span>
            </div>
            <div class="text-2xl font-black text-rose-600 mt-2">{{ $stats['expired'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Deadline passed</p>
        </a>

        <div class="p-4 bg-gradient-to-tr from-violet-600 to-indigo-600 text-white rounded-2xl shadow-sm col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between text-indigo-100">
                <span class="text-xs font-bold uppercase tracking-wider">Applications</span>
                <span class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-sm font-bold"><i class="fa-solid fa-users"></i></span>
            </div>
            <div class="text-2xl font-black text-white mt-2">{{ $stats['total_applications'] }}</div>
            <p class="text-[11px] text-indigo-200 mt-1">Total candidates pool</p>
        </div>
    </div>

    <!-- Toolbar & Filter Area -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="text-base font-bold text-slate-900">Vacancies Directory</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                    {{ $jobs->total() }} openings found
                </span>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('public.jobs.index') }}" target="_blank" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-eye"></i>
                    <span>Preview Careers Page</span>
                </a>
                <a href="{{ route('admin.jobs.create') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-plus"></i>
                    <span>Create Job Opening</span>
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.jobs.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by job title, code, department..."
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
            </div>

            <div class="sm:col-span-3">
                <select name="department" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="employment_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    <option value="">All Types</option>
                    @foreach($employmentTypes as $type)
                        <option value="{{ $type }}" {{ $employmentType === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors">
                    Filter
                </button>
                @if($search || $department || $employmentType || $status !== 'all')
                    <a href="{{ route('admin.jobs.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Vacancies List Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Job Position & Code</th>
                        <th class="py-3.5 px-4">Department & Designation</th>
                        <th class="py-3.5 px-3 text-center">Type</th>
                        <th class="py-3.5 px-3 text-center">Vacancies</th>
                        <th class="py-3.5 px-4 text-center">Applications Received</th>
                        <th class="py-3.5 px-4">Application Deadline</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($jobs as $job)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Job Title & Code -->
                            <td class="py-4 px-6">
                                <a href="{{ route('admin.jobs.show', $job) }}" class="font-bold text-slate-900 text-sm hover:text-indigo-600 transition-colors block">
                                    {{ $job->job_title }}
                                </a>
                                <div class="flex items-center gap-2 mt-0.5 text-slate-400 text-xs">
                                    <span class="font-mono text-indigo-600 font-bold">{{ $job->job_code }}</span>
                                    <span>&bull;</span>
                                    <span>Salary: {{ $job->salary_formatted }}</span>
                                </div>
                            </td>

                            <!-- Department & Designation -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800">{{ $job->department ?? 'General' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $job->designation ?? 'Not specified' }}</div>
                            </td>

                            <!-- Employment Type -->
                            <td class="py-4 px-3 text-center">
                                <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-700 font-semibold text-[11px]">
                                    {{ $job->employment_type ?? 'Full-time' }}
                                </span>
                            </td>

                            <!-- Vacancies -->
                            <td class="py-4 px-3 text-center font-black text-slate-900">
                                {{ $job->vacancies }}
                            </td>

                            <!-- Applications Received -->
                            <td class="py-4 px-4 text-center">
                                <a href="{{ route('admin.jobs.show', $job) }}#applications" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold {{ $job->applications_count > 0 ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200' : 'bg-slate-100 text-slate-500' }} transition-colors">
                                    <i class="fa-solid fa-users text-[11px]"></i>
                                    <span>{{ $job->applications_count }} Candidates</span>
                                </a>
                            </td>

                            <!-- Application Deadline -->
                            <td class="py-4 px-4">
                                @if($job->application_deadline)
                                    <div class="font-bold {{ $job->is_expired ? 'text-rose-600' : 'text-slate-800' }}">
                                        {{ $job->application_deadline->format('M d, Y') }}
                                    </div>
                                    <div class="text-[11px] {{ $job->is_expired ? 'text-rose-400 font-semibold' : 'text-slate-400' }}">
                                        {{ $job->days_remaining }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">No closing date</span>
                                @endif
                            </td>

                            <!-- Status & Quick Status Changer -->
                            <td class="py-4 px-4">
                                @if($job->is_expired)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Expired
                                    </span>
                                @elseif($job->status === 'published' || $job->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Published
                                    </span>
                                @elseif($job->status === 'draft')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draft
                                    </span>
                                @elseif($job->status === 'closed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Closed
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- View Details & Candidates -->
                                    <a href="{{ route('admin.jobs.show', $job) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View Job & Applications">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('admin.jobs.edit', $job) }}" class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" title="Edit Opening">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <!-- Quick Status Toggle Dropdown / Button -->
                                    <div class="relative inline-block text-left" id="status-container-{{ $job->id }}">
                                        @if($job->status === 'published' || $job->status === 'active')
                                            <form method="POST" action="{{ route('admin.jobs.update-status', $job) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="draft">
                                                <button type="submit" onclick="return confirm('Unpublish this vacancy and move to draft? Candidates will no longer see it.')" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Unpublish / Set as Draft">
                                                    <i class="fa-solid fa-eye-slash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.jobs.update-status', $job) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="published">
                                                <button type="submit" onclick="return confirm('Publish this vacancy now? It will appear on the public careers portal.')" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Publish Vacancy">
                                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <!-- Delete -->
                                    <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Are you sure you want to move job opening \'{{ $job->job_title }}\' to trash?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Move to Trash">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    <i class="fa-solid fa-briefcase"></i>
                                </div>
                                <p class="font-bold text-slate-700">No job openings found</p>
                                <p class="text-xs text-slate-400 mt-1">Create your first vacancy to begin receiving candidate applications.</p>
                                <div class="mt-4">
                                    <a href="{{ route('admin.jobs.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-2">
                                        <i class="fa-solid fa-plus"></i> Create Job Opening
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
