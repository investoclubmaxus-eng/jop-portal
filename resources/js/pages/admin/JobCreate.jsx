import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../../api/client';
import { showSuccess, showError, showToast } from '../../utils/alerts';

export default function JobCreate() {
    const navigate = useNavigate();

    const [form, setForm] = useState({
        job_title: '',
        job_code: `JOB-${Math.floor(1000 + Math.random() * 9000)}`,
        department: '',
        designation: '',
        employment_type: 'Full-time',
        vacancies: 1,
        experience_min: '',
        experience_max: '',
        salary_min: '',
        salary_max: '',
        application_start_date: new Date().toISOString().split('T')[0],
        application_deadline: '',
        job_description: '',
        required_qualifications: '',
        required_skills: '',
        status: 'draft',
    });

    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm((prev) => ({ ...prev, [name]: value }));
        if (errors[name]) {
            setErrors((prev) => {
                const updated = { ...prev };
                delete updated[name];
                return updated;
            });
        }
    };

    const handleSubmit = async (actionType) => {
        setErrors({});
        setSubmitting(true);

        const targetStatus = actionType === 'publish' ? 'published' : (actionType === 'draft' ? 'draft' : form.status);

        const payload = {
            ...form,
            status: targetStatus,
            action_type: actionType,
            experience_min: form.experience_min === '' ? null : Number(form.experience_min),
            experience_max: form.experience_max === '' ? null : Number(form.experience_max),
            salary_min: form.salary_min === '' ? null : Number(form.salary_min),
            salary_max: form.salary_max === '' ? null : Number(form.salary_max),
            application_deadline: form.application_deadline === '' ? null : form.application_deadline,
        };

        try {
            const res = await api.post('/admin/jobs', payload);
            await showSuccess(
                actionType === 'publish' ? 'Vacancy Published Live!' : 'Draft Saved Successfully',
                `Job opening "${res.data.job.job_title}" (${res.data.job.job_code}) is now ${targetStatus}.`
            );
            navigate(`/admin/jobs/${res.data.job.id}`);
        } catch (err) {
            if (err.response?.status === 422 && err.response.data?.errors) {
                setErrors(err.response.data.errors);
                const firstError = Object.values(err.response.data.errors)[0]?.[0];
                showError('Validation Error', firstError || 'Please correct the highlighted fields.');
            } else {
                showError('Error', err.response?.data?.message || 'Failed to create job opening.');
            }
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="max-w-4xl mx-auto space-y-6">
            {/* Header */}
            <div className="flex items-center justify-between pb-4 border-b border-slate-800">
                <div>
                    <h1 className="text-2xl font-bold text-white tracking-tight">Post New Job Opening</h1>
                    <p className="text-sm text-slate-400 mt-1">Configure vacancy details, candidate requirements, and publish live</p>
                </div>
                <Link
                    to="/admin/jobs"
                    className="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition"
                >
                    Cancel & Back
                </Link>
            </div>

            <form className="space-y-6" onSubmit={(e) => e.preventDefault()}>
                {/* 1. Core Position Specs */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2 className="text-sm font-bold uppercase tracking-wider text-indigo-400 border-b border-slate-800 pb-2">
                        1. Position Specifications
                    </h2>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Job Title *
                            </label>
                            <input
                                type="text"
                                name="job_title"
                                value={form.job_title}
                                onChange={handleChange}
                                placeholder="e.g. Senior Full Stack Engineer"
                                className={`w-full px-3.5 py-2.5 bg-slate-800 border ${errors.job_title ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-700'} rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500`}
                            />
                            {errors.job_title && <p className="mt-1 text-xs text-rose-400">{errors.job_title[0]}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Job Code *
                            </label>
                            <input
                                type="text"
                                name="job_code"
                                value={form.job_code}
                                onChange={handleChange}
                                placeholder="e.g. JOB-1001"
                                className={`w-full px-3.5 py-2.5 bg-slate-800 border ${errors.job_code ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-700'} rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500`}
                            />
                            {errors.job_code && <p className="mt-1 text-xs text-rose-400">{errors.job_code[0]}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Department
                            </label>
                            <input
                                type="text"
                                name="department"
                                value={form.department}
                                onChange={handleChange}
                                placeholder="e.g. Engineering, Product, Marketing"
                                className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {errors.department && <p className="mt-1 text-xs text-rose-400">{errors.department[0]}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Designation
                            </label>
                            <input
                                type="text"
                                name="designation"
                                value={form.designation}
                                onChange={handleChange}
                                placeholder="e.g. Lead Technical Architect"
                                className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {errors.designation && <p className="mt-1 text-xs text-rose-400">{errors.designation[0]}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Employment Type
                            </label>
                            <select
                                name="employment_type"
                                value={form.employment_type}
                                onChange={handleChange}
                                className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Remote">Remote</option>
                                <option value="Internship">Internship</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Number of Vacancies *
                            </label>
                            <input
                                type="number"
                                min="1"
                                name="vacancies"
                                value={form.vacancies}
                                onChange={handleChange}
                                className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {errors.vacancies && <p className="mt-1 text-xs text-rose-400">{errors.vacancies[0]}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Initial Vacancy Status
                            </label>
                            <select
                                name="status"
                                value={form.status}
                                onChange={handleChange}
                                className="w-full px-3.5 py-2 bg-slate-800 border border-indigo-500/50 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="draft">Draft (Unpublished)</option>
                                <option value="published">Published (Live)</option>
                                <option value="closed">Closed</option>
                            </select>
                            {errors.status && <p className="mt-1 text-xs text-rose-400">{errors.status[0]}</p>}
                        </div>
                    </div>
                </div>

                {/* 2. Compensation & Experience Requirements */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2 className="text-sm font-bold uppercase tracking-wider text-indigo-400 border-b border-slate-800 pb-2">
                        2. Experience & Compensation
                    </h2>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Min Experience (Years)
                            </label>
                            <input
                                type="number"
                                min="0"
                                step="0.5"
                                name="experience_min"
                                value={form.experience_min}
                                onChange={handleChange}
                                placeholder="e.g. 2"
                                className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {errors.experience_min && <p className="mt-1 text-xs text-rose-400">{errors.experience_min[0]}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Max Experience (Years)
                            </label>
                            <input
                                type="number"
                                min="0"
                                step="0.5"
                                name="experience_max"
                                value={form.experience_max}
                                onChange={handleChange}
                                placeholder="e.g. 6"
                                className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {errors.experience_max && <p className="mt-1 text-xs text-rose-400">{errors.experience_max[0]}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Min Salary ($ Annual)
                            </label>
                            <input
                                type="number"
                                min="0"
                                name="salary_min"
                                value={form.salary_min}
                                onChange={handleChange}
                                placeholder="e.g. 80000"
                                className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {errors.salary_min && <p className="mt-1 text-xs text-rose-400">{errors.salary_min[0]}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Max Salary ($ Annual)
                            </label>
                            <input
                                type="number"
                                min="0"
                                name="salary_max"
                                value={form.salary_max}
                                onChange={handleChange}
                                placeholder="e.g. 130000"
                                className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {errors.salary_max && <p className="mt-1 text-xs text-rose-400">{errors.salary_max[0]}</p>}
                        </div>
                    </div>
                </div>

                {/* 3. Timeline / Dates */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2 className="text-sm font-bold uppercase tracking-wider text-indigo-400 border-b border-slate-800 pb-2">
                        3. Application Timeline & Deadlines
                    </h2>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Start Date
                            </label>
                            <input
                                type="date"
                                name="application_start_date"
                                value={form.application_start_date}
                                onChange={handleChange}
                                className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {errors.application_start_date && <p className="mt-1 text-xs text-rose-400">{errors.application_start_date[0]}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Application Closing Deadline
                            </label>
                            <input
                                type="date"
                                name="application_deadline"
                                value={form.application_deadline}
                                onChange={handleChange}
                                className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {errors.application_deadline && <p className="mt-1 text-xs text-rose-400">{errors.application_deadline[0]}</p>}
                        </div>
                    </div>
                </div>

                {/* 4. Description, Qualifications, Skills */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <h2 className="text-sm font-bold uppercase tracking-wider text-indigo-400 border-b border-slate-800 pb-2">
                        4. Role Description & Candidate Profile
                    </h2>

                    <div>
                        <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                            Job Description & Responsibilities *
                        </label>
                        <textarea
                            rows="6"
                            name="job_description"
                            value={form.job_description}
                            onChange={handleChange}
                            placeholder="Detail key responsibilities, deliverables, team culture, and day-to-day work..."
                            className={`w-full px-3.5 py-2.5 bg-slate-800 border ${errors.job_description ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-700'} rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans`}
                        ></textarea>
                        {errors.job_description && <p className="mt-1 text-xs text-rose-400">{errors.job_description[0]}</p>}
                    </div>

                    <div>
                        <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                            Eligibility Criteria & Qualifications
                        </label>
                        <textarea
                            rows="3"
                            name="required_qualifications"
                            value={form.required_qualifications}
                            onChange={handleChange}
                            placeholder="e.g. Bachelor's in Computer Science or equivalent practical experience..."
                            className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        ></textarea>
                    </div>

                    <div>
                        <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                            Required Skills (Comma separated)
                        </label>
                        <input
                            type="text"
                            name="required_skills"
                            value={form.required_skills}
                            onChange={handleChange}
                            placeholder="e.g. React, Laravel, TypeScript, MySQL, AWS, TailwindCSS"
                            className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p className="mt-1 text-[11px] text-slate-400">Separate multiple skills with commas</p>
                    </div>
                </div>

                {/* Action Buttons */}
                <div className="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4">
                    <button
                        type="button"
                        disabled={submitting}
                        onClick={() => handleSubmit('draft')}
                        className="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-700 hover:bg-slate-800 text-slate-300 text-xs font-semibold transition disabled:opacity-50"
                    >
                        Save as Draft (Unpublished)
                    </button>
                    <button
                        type="button"
                        disabled={submitting}
                        onClick={() => handleSubmit('publish')}
                        className="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <i className="fa-solid fa-paper-plane text-xs"></i>
                        <span>Publish Vacancy Live</span>
                    </button>
                </div>
            </form>
        </div>
    );
}
