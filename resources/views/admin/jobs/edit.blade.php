@extends('layouts.admin')

@section('title', 'Edit Job Opening - ' . $job->job_title)
@section('page-title', 'Edit Job Opening')
@section('page-subtitle', 'Modify requirements, update salary/experience parameters, and adjust publication dates')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.jobs.show', $job) }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Job Details</span>
        </a>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500">Job Code:</span>
            <span class="font-mono text-xs font-bold px-2 py-1 bg-slate-200 text-slate-800 rounded-lg">{{ $job->job_code }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.jobs.update', $job) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Position Overview -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Position Details</h2>
                    <p class="text-xs text-slate-500">Essential job identifiers, department, and available openings</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-6">
                <!-- Job Title -->
                <div class="sm:col-span-8">
                    <label for="job_title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Job Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="job_title" name="job_title" value="{{ old('job_title', $job->job_title) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>

                <!-- Job Code -->
                <div class="sm:col-span-4">
                    <label for="job_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Job Reference Code <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="job_code" name="job_code" value="{{ old('job_code', $job->job_code) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>

                <!-- Department -->
                <div class="sm:col-span-4">
                    <label for="department" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Department
                    </label>
                    <input type="text" id="department" name="department" value="{{ old('department', $job->department) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>

                <!-- Designation -->
                <div class="sm:col-span-4">
                    <label for="designation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Designation / Role Level
                    </label>
                    <input type="text" id="designation" name="designation" value="{{ old('designation', $job->designation) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>

                <!-- Number of Vacancies -->
                <div class="sm:col-span-4">
                    <label for="vacancies" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Number of Vacancies <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="vacancies" name="vacancies" value="{{ old('vacancies', $job->vacancies) }}" min="1" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>

                <!-- Employment Type -->
                <div class="sm:col-span-4">
                    <label for="employment_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Employment Type
                    </label>
                    <select id="employment_type" name="employment_type" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        @foreach($employmentTypes as $type)
                            <option value="{{ $type }}" {{ old('employment_type', $job->employment_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Experience Requirements (Min - Max) -->
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Experience Range (Years)
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="experience_min" value="{{ old('experience_min', $job->experience_min) }}" min="0" placeholder="Min Yrs"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <input type="number" name="experience_max" value="{{ old('experience_max', $job->experience_max) }}" min="0" placeholder="Max Yrs"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    </div>
                </div>

                <!-- Salary Range (Min - Max) -->
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Salary Range ($ USD / Annual)
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" name="salary_min" value="{{ old('salary_min', $job->salary_min) }}" placeholder="Min Salary"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <input type="number" step="0.01" name="salary_max" value="{{ old('salary_max', $job->salary_max) }}" placeholder="Max Salary"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Description, Qualifications & Skills -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center font-bold text-base">
                    <i class="fa-solid fa-list-ul"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Job Description & Qualifications</h2>
                    <p class="text-xs text-slate-500">Provide role expectations, candidate criteria, and required skill stack</p>
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="job_description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Job Description & Responsibilities <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="job_description" name="job_description" rows="6" required
                              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white leading-relaxed">{{ old('job_description', $job->job_description) }}</textarea>
                </div>

                <div>
                    <label for="required_qualifications" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Required Qualifications & Eligibility
                    </label>
                    <textarea id="required_qualifications" name="required_qualifications" rows="3"
                              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">{{ old('required_qualifications', $job->required_qualifications) }}</textarea>
                </div>

                <div>
                    <label for="required_skills" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Required Skills (comma separated)
                    </label>
                    <input type="text" id="required_skills" name="required_skills" value="{{ is_array(old('required_skills', $job->required_skills)) ? implode(', ', old('required_skills', $job->required_skills)) : old('required_skills', $job->required_skills) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>
            </div>
        </div>

        <!-- 3. Scheduling & Status Settings -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Application Timeline & Status</h2>
                    <p class="text-xs text-slate-500">Set start and deadline closing dates, and specify vacancy publication state</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Published Start Date -->
                <div>
                    <label for="published_at" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Publication / Opening Date
                    </label>
                    <input type="date" id="published_at" name="published_at" value="{{ old('published_at', $job->published_at ? $job->published_at->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>

                <!-- Application Closing Date -->
                <div>
                    <label for="application_deadline" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Application Closing Deadline
                    </label>
                    <input type="date" id="application_deadline" name="application_deadline" value="{{ old('application_deadline', $job->application_deadline ? $job->application_deadline->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Publication Status <span class="text-rose-500">*</span>
                    </label>
                    <select id="status" name="status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <option value="published" {{ old('status', $job->status) === 'published' ? 'selected' : '' }}>Published (Live on Careers Site)</option>
                        <option value="active" {{ old('status', $job->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="draft" {{ old('status', $job->status) === 'draft' ? 'selected' : '' }}>Draft (Unpublished)</option>
                        <option value="closed" {{ old('status', $job->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="expired" {{ old('status', $job->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Form Actions Footer -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
            <a href="{{ route('admin.jobs.show', $job) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs uppercase tracking-wider transition-colors">
                Cancel
            </a>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Update Job Opening</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
