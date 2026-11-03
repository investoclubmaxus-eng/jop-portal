import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/client';

export default function JobsList() {
    const [jobs, setJobs] = useState([]);
    const [stats, setStats] = useState({ total: 0, active: 0, draft: 0, expired: 0, total_applications: 0 });
    const [departments, setDepartments] = useState([]);
    const [employmentTypes, setEmploymentTypes] = useState([]);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [loading, setLoading] = useState(true);

    const [statusFilter, setStatusFilter] = useState('all');
    const [departmentFilter, setDepartmentFilter] = useState('');
    const [typeFilter, setTypeFilter] = useState('');
    const [search, setSearch] = useState('');

    const [toastMessage, setToastMessage] = useState('');
    const [errorMessage, setErrorMessage] = useState('');

    const fetchJobs = async (page = 1) => {
        setLoading(true);
        try {
            const params = {
                page,
                status: statusFilter,
                department: departmentFilter || undefined,
                employment_type: typeFilter || undefined,
                search: search || undefined,
            };
            const res = await api.get('/admin/jobs', { params });
            setJobs(res.data.jobs);
            setStats(res.data.stats);
            setDepartments(res.data.departments || []);
            setEmploymentTypes(res.data.employment_types || []);
            setPagination(res.data.pagination);
        } catch {
            setErrorMessage('Failed to load vacancies.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchJobs(1);
    }, [statusFilter, departmentFilter, typeFilter]);

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        fetchJobs(1);
    };

    const showToast = (msg) => {
        setToastMessage(msg);
        setTimeout(() => setToastMessage(''), 4000);
    };

    const handleStatusChange = async (jobId, newStatus) => {
        try {
            const res = await api.patch(`/admin/jobs/${jobId}/status`, { status: newStatus });
            showToast(res.data.message);
            fetchJobs(pagination.current_page);
        } catch (err) {
            setErrorMessage(err.response?.data?.message || 'Failed to update vacancy status.');
            setTimeout(() => setErrorMessage(''), 4000);
        }
    };

    const handleDelete = async (job) => {
        if (!confirm(`Are you sure you want to move vacancy "${job.job_title}" to trash?`)) return;
        try {
            const res = await api.delete(`/admin/jobs/${job.id}`);
            showToast(res.data.message);
            fetchJobs(pagination.current_page);
        } catch (err) {
            setErrorMessage(err.response?.data?.message || 'Failed to delete vacancy.');
            setTimeout(() => setErrorMessage(''), 4000);
        }
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-white tracking-tight">Job Openings Management</h1>
                    <p className="text-sm text-slate-400 mt-1">Create, publish, edit vacancies and track candidate application volume</p>
                </div>
                <Link
                    to="/admin/jobs/create"
                    className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition self-start sm:self-auto"
                >
                    <i className="fa-solid fa-plus text-xs"></i>
                    <span>Create Job Opening</span>
                </Link>
            </div>

            {/* Notifications */}
            {toastMessage && (
                <div className="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center justify-between">
                    <span className="flex items-center gap-2">
                        <i className="fa-solid fa-circle-check"></i>
                        {toastMessage}
                    </span>
                    <button onClick={() => setToastMessage('')} className="text-emerald-400 hover:text-emerald-200">
                        <i className="fa-solid fa-xmark"></i>
                    </button>
                </div>
            )}
            {errorMessage && (
                <div className="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center justify-between">
                    <span className="flex items-center gap-2">
                        <i className="fa-solid fa-triangle-exclamation"></i>
                        {errorMessage}
                    </span>
                    <button onClick={() => setErrorMessage('')} className="text-rose-400 hover:text-rose-200">
                        <i className="fa-solid fa-xmark"></i>
                    </button>
                </div>
            )}

            {/* Status Statistics Cards */}
            <div className="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div
                    onClick={() => setStatusFilter('all')}
                    className={`cursor-pointer p-4 rounded-2xl border transition ${
                        statusFilter === 'all'
                            ? 'bg-indigo-950/40 border-indigo-500/50 text-white'
                            : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'
                    }`}
                >
                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Positions</p>
                    <p className="text-2xl font-bold text-white mt-1">{stats.total}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('active')}
                    className={`cursor-pointer p-4 rounded-2xl border transition ${
                        statusFilter === 'active'
                            ? 'bg-emerald-950/40 border-emerald-500/50 text-white'
                            : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'
                    }`}
                >
                    <p className="text-xs font-semibold uppercase tracking-wider text-emerald-400">Active / Live</p>
                    <p className="text-2xl font-bold text-white mt-1">{stats.active}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('draft')}
                    className={`cursor-pointer p-4 rounded-2xl border transition ${
                        statusFilter === 'draft'
                            ? 'bg-amber-950/40 border-amber-500/50 text-white'
                            : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'
                    }`}
                >
                    <p className="text-xs font-semibold uppercase tracking-wider text-amber-400">Drafts</p>
                    <p className="text-2xl font-bold text-white mt-1">{stats.draft}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('expired')}
                    className={`cursor-pointer p-4 rounded-2xl border transition ${
                        statusFilter === 'expired'
                            ? 'bg-rose-950/40 border-rose-500/50 text-white'
                            : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'
                    }`}
                >
                    <p className="text-xs font-semibold uppercase tracking-wider text-rose-400">Expired</p>
                    <p className="text-2xl font-bold text-white mt-1">{stats.expired}</p>
                </div>
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-4">
                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Applications</p>
                    <p className="text-2xl font-bold text-indigo-400 mt-1">{stats.total_applications}</p>
                </div>
            </div>

            {/* Filter Bar */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row gap-4 justify-between items-center">
                <form onSubmit={handleSearchSubmit} className="w-full md:w-80 flex gap-2">
                    <div className="relative flex-1">
                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i className="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search title, code, designation..."
                            className="w-full pl-9 pr-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                    <button
                        type="submit"
                        className="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 rounded-xl transition"
                    >
                        Search
                    </button>
                </form>

                <div className="w-full md:w-auto flex flex-wrap items-center gap-3">
                    {/* Department Dropdown */}
                    <select
                        value={departmentFilter}
                        onChange={(e) => setDepartmentFilter(e.target.value)}
                        className="px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Departments</option>
                        {departments.map((dept) => (
                            <option key={dept} value={dept}>{dept}</option>
                        ))}
                    </select>

                    {/* Employment Type Dropdown */}
                    <select
                        value={typeFilter}
                        onChange={(e) => setTypeFilter(e.target.value)}
                        className="px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Employment Types</option>
                        {employmentTypes.map((type) => (
                            <option key={type} value={type}>{type}</option>
                        ))}
                    </select>

                    <button
                        type="button"
                        onClick={() => {
                            setSearch('');
                            setDepartmentFilter('');
                            setTypeFilter('');
                            setStatusFilter('all');
                            fetchJobs(1);
                        }}
                        className="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 text-xs"
                        title="Reset Filters"
                    >
                        <i className="fa-solid fa-arrow-rotate-left"></i>
                    </button>
                </div>
            </div>

            {/* Vacancy Table */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-slate-800/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                            <tr>
                                <th className="px-6 py-3.5">Job Opening</th>
                                <th className="px-6 py-3.5">Type & Experience</th>
                                <th className="px-6 py-3.5">Salary Range</th>
                                <th className="px-6 py-3.5 text-center">Vacancies</th>
                                <th className="px-6 py-3.5 text-center">Applications</th>
                                <th className="px-6 py-3.5">Status</th>
                                <th className="px-6 py-3.5">Deadline</th>
                                <th className="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-800/80">
                            {loading ? (
                                <tr>
                                    <td colSpan="8" className="px-6 py-12 text-center text-slate-400">
                                        <div className="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                                        <span>Loading vacancies...</span>
                                    </td>
                                </tr>
                            ) : jobs.length === 0 ? (
                                <tr>
                                    <td colSpan="8" className="px-6 py-12 text-center text-slate-400">
                                        <i className="fa-solid fa-briefcase text-2xl text-slate-400 mb-2 block"></i>
                                        <span>No job openings match current filters.</span>
                                    </td>
                                </tr>
                            ) : (
                                jobs.map((job) => (
                                    <tr key={job.id} className="hover:bg-slate-800/40 transition">
                                        <td className="px-6 py-4">
                                            <Link
                                                to={`/admin/jobs/${job.id}`}
                                                className="font-bold text-slate-100 hover:text-indigo-400 transition"
                                            >
                                                {job.job_title}
                                            </Link>
                                            <div className="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400">
                                                <span className="font-mono text-indigo-400">{job.job_code}</span>
                                                <span>&bull;</span>
                                                <span>{job.department}</span>
                                                <span>&bull;</span>
                                                <span>{job.designation}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="font-medium text-slate-300">{job.employment_type}</span>
                                            <span className="block text-[11px] text-slate-400">{job.experience_formatted}</span>
                                        </td>
                                        <td className="px-6 py-4 text-slate-300 font-medium">
                                            {job.salary_formatted}
                                        </td>
                                        <td className="px-6 py-4 text-center font-bold text-slate-200">
                                            {job.vacancies}
                                        </td>
                                        <td className="px-6 py-4 text-center">
                                            <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                                {job.applications_count}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex flex-col gap-1 items-start">
                                                {job.status === 'published' ? (
                                                    <span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                        Published
                                                    </span>
                                                ) : job.status === 'draft' ? (
                                                    <span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-400 border border-slate-700">
                                                        Draft
                                                    </span>
                                                ) : (
                                                    <span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                        {job.status}
                                                    </span>
                                                )}
                                                {job.is_expired && (
                                                    <span className="text-[10px] font-semibold text-rose-400">Expired</span>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-slate-300">
                                            <span>{job.deadline}</span>
                                            {job.days_remaining !== null && (
                                                <span className={`block text-[10px] font-medium ${
                                                    job.days_remaining < 3 ? 'text-rose-400' : 'text-slate-400'
                                                }`}>
                                                    {job.days_remaining > 0 ? `${job.days_remaining} days left` : 'Deadline passed'}
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-1.5">
                                                {/* Promotion & Details */}
                                                <Link
                                                    to={`/admin/jobs/${job.id}`}
                                                    className="p-1.5 rounded-lg text-slate-400 hover:text-indigo-400 hover:bg-slate-800 transition"
                                                    title="View Details & Campaign URLs"
                                                >
                                                    <i className="fa-solid fa-share-nodes"></i>
                                                </Link>

                                                {/* Edit */}
                                                <Link
                                                    to={`/admin/jobs/${job.id}/edit`}
                                                    className="p-1.5 rounded-lg text-slate-400 hover:text-indigo-400 hover:bg-slate-800 transition"
                                                    title="Edit Vacancy"
                                                >
                                                    <i className="fa-solid fa-pen-to-square"></i>
                                                </Link>

                                                {/* Status quick toggle */}
                                                {job.status === 'published' ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleStatusChange(job.id, 'draft')}
                                                        className="p-1.5 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-800 transition"
                                                        title="Unpublish (Save as Draft)"
                                                    >
                                                        <i className="fa-solid fa-eye-slash"></i>
                                                    </button>
                                                ) : (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleStatusChange(job.id, 'published')}
                                                        className="p-1.5 rounded-lg text-slate-400 hover:text-emerald-400 hover:bg-slate-800 transition"
                                                        title="Publish Live"
                                                    >
                                                        <i className="fa-solid fa-paper-plane"></i>
                                                    </button>
                                                )}

                                                {/* Delete */}
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(job)}
                                                    className="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition"
                                                    title="Move to Trash"
                                                >
                                                    <i className="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {pagination.last_page > 1 && (
                    <div className="px-6 py-3.5 bg-slate-800/40 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <span>Showing page {pagination.current_page} of {pagination.last_page} ({pagination.total} total)</span>
                        <div className="flex gap-1">
                            <button
                                disabled={pagination.current_page <= 1}
                                onClick={() => fetchJobs(pagination.current_page - 1)}
                                className="px-3 py-1 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-40 transition"
                            >
                                Prev
                            </button>
                            <button
                                disabled={pagination.current_page >= pagination.last_page}
                                onClick={() => fetchJobs(pagination.current_page + 1)}
                                className="px-3 py-1 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-40 transition"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
