@extends('layouts.admin')

@section('title', 'Create Job Opening')
@section('page-title', 'Create Job Opening')
@section('page-subtitle', 'Post a new job vacancy, set requirements, application deadline, and publish to the candidate careers board')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.jobs.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Job Openings</span>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.jobs.store') }}" class="space-y-6">
        @csrf

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
                    <input type="text" id="job_title" name="job_title" value="{{ old('job_title') }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                           placeholder="e.g. Senior Full-Stack Laravel Developer">
                </div>

                <!-- Job Code -->
                <div class="sm:col-span-4">
                    <label for="job_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Job Reference Code <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="job_code" name="job_code" value="{{ old('job_code', $suggestedCode) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>

                <!-- Department -->
                <div class="sm:col-span-4">
                    <label for="department" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Department
                    </label>
                    <input type="text" id="department" name="department" value="{{ old('department') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                           placeholder="e.g. Information Technology">
                </div>

                <!-- Designation -->
                <div class="sm:col-span-4">
                    <label for="designation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Designation / Role Level
                    </label>
                    <input type="text" id="designation" name="designation" value="{{ old('designation') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                           placeholder="e.g. Senior Software Engineer">
                </div>

                <!-- Number of Vacancies -->
                <div class="sm:col-span-4">
                    <label for="vacancies" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Number of Vacancies <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="vacancies" name="vacancies" value="{{ old('vacancies', 1) }}" min="1" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>

                <!-- Employment Type -->
                <div class="sm:col-span-4">
                    <label for="employment_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Employment Type
                    </label>
                    <select id="employment_type" name="employment_type" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        @foreach($employmentTypes as $type)
                            <option value="{{ $type }}" {{ old('employment_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Experience Requirements (Min - Max) -->
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Experience Range (Years)
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="experience_min" value="{{ old('experience_min', 0) }}" min="0" placeholder="Min Yrs"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <input type="number" name="experience_max" value="{{ old('experience_max') }}" min="0" placeholder="Max Yrs"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    </div>
                </div>

                <!-- Salary Range (Min - Max) -->
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Salary Range ($ USD / Annual)
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" name="salary_min" value="{{ old('salary_min') }}" placeholder="Min Salary"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <input type="number" step="0.01" name="salary_max" value="{{ old('salary_max') }}" placeholder="Max Salary"
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
                <!-- Job Description -->
                <div>
                    <label for="job_description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Job Description & Responsibilities <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="job_description" name="job_description" rows="6" required
                              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white leading-relaxed"
                              placeholder="Describe the day-to-day responsibilities, mission, and expectations for this role...">{{ old('job_description') }}</textarea>
                </div>

                <!-- Required Qualifications -->
                <div>
                    <label for="required_qualifications" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Required Qualifications & Eligibility
                    </label>
                    <textarea id="required_qualifications" name="required_qualifications" rows="3"
                              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                              placeholder="e.g. Bachelor's degree in Computer Science, 4+ years software development experience...">{{ old('required_qualifications') }}</textarea>
                </div>

                <!-- Required Skills -->
                <div>
                    <label for="required_skills" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Required Skills (comma separated)
                    </label>
                    <input type="text" id="required_skills" name="required_skills" value="{{ is_array(old('required_skills')) ? implode(', ', old('required_skills')) : old('required_skills') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                           placeholder="e.g. PHP, Laravel, MySQL, REST APIs, Vue.js, Tailwind CSS">
                    <p class="text-[11px] text-slate-400 mt-1">Separate key skills with commas. These will be displayed as badge tags.</p>
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
                    <input type="date" id="published_at" name="published_at" value="{{ old('published_at', date('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                </div>

                <!-- Application Closing Date -->
                <div>
                    <label for="application_deadline" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Application Closing Deadline
                    </label>
                    <input type="date" id="application_deadline" name="application_deadline" value="{{ old('application_deadline') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    <p class="text-[11px] text-slate-400 mt-1">The job listing will automatically expire once this date passes.</p>
                </div>

                <!-- Initial Status -->
                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Publication Status <span class="text-rose-500">*</span>
                    </label>
                    <select id="status" name="status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published (Live on Careers Site)</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Save without publishing)</option>
                        <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed / Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Form Actions Footer -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
            <a href="{{ route('admin.jobs.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs uppercase tracking-wider transition-colors">
                Cancel
            </a>

            <div class="flex items-center gap-3">
                <button type="submit" name="action_type" value="draft" class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-2">
                    <i class="fa-solid fa-file-pen text-slate-400"></i>
                    <span>Save as Draft</span>
                </button>

                <button type="submit" name="action_type" value="publish" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Publish Vacancy</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
