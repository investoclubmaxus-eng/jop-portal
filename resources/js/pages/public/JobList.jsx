import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/client';

export default function JobList() {
    const [jobs, setJobs] = useState([]);
    const [departments, setDepartments] = useState([]);
    const [employmentTypes, setEmploymentTypes] = useState([]);
    const [totalVacancies, setTotalVacancies] = useState(0);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [loading, setLoading] = useState(true);

    const [search, setSearch] = useState('');
    const [departmentFilter, setDepartmentFilter] = useState('');
    const [typeFilter, setTypeFilter] = useState('');

    const fetchJobs = async (page = 1) => {
        setLoading(true);
        try {
            const params = {
                page,
                search: search || undefined,
                department: departmentFilter || undefined,
                employment_type: typeFilter || undefined,
            };
            const res = await api.get('/jobs', { params });
            setJobs(res.data.jobs);
            setDepartments(res.data.departments || []);
            setEmploymentTypes(res.data.employment_types || []);
            setTotalVacancies(res.data.total_active_vacancies || 0);
            setPagination(res.data.pagination);
        } catch {
            setJobs([]);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchJobs(1);
    }, [departmentFilter, typeFilter]);

    const handleSearch = (e) => {
        e.preventDefault();
        fetchJobs(1);
    };

    return (
        <div className="w-full bg-slate-950 min-h-[calc(100vh-140px)] space-y-10 pb-16">
            {/* Hero Section */}
            <section className="relative overflow-hidden bg-gradient-to-b from-indigo-950/60 via-slate-900 to-slate-900 py-16 sm:py-20 border-b border-slate-800">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                    <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold mb-4">
                        <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>{totalVacancies} Open Vacancies Available Now</span>
                    </div>

                    <h1 className="text-3xl sm:text-5xl font-extrabold text-white tracking-tight max-w-3xl mx-auto leading-tight">
                        Shape Your Future With Our Growing Global Team
                    </h1>
                    <p className="mt-3 text-sm sm:text-base text-slate-400 max-w-2xl mx-auto">
                        Explore open career opportunities across technology, management, and operations. Apply directly or via your favorite job platform.
                    </p>

                    {/* Search and Filters Bar */}
                    <div className="mt-8 max-w-3xl mx-auto bg-slate-900/90 border border-slate-700/80 p-2 sm:p-2.5 rounded-2xl shadow-2xl backdrop-blur">
                        <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-2">
                            <div className="relative flex-1">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i className="fa-solid fa-magnifying-glass text-xs"></i>
                                </div>
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search by job title, skill, or keyword..."
                                    className="w-full pl-10 pr-4 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs sm:text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <button
                                type="submit"
                                className="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs sm:text-sm shadow-md shadow-indigo-600/30 transition flex items-center justify-center gap-2"
                            >
                                <span>Search</span>
                                <i className="fa-solid fa-arrow-right text-xs"></i>
                            </button>
                        </form>
                    </div>

                    {/* Filter Pills */}
                    <div className="mt-6 flex flex-wrap items-center justify-center gap-2 max-w-4xl mx-auto">
                        <button
                            type="button"
                            onClick={() => {
                                setDepartmentFilter('');
                                setTypeFilter('');
                                setSearch('');
                                fetchJobs(1);
                            }}
                            className={`px-3 py-1 rounded-full text-xs font-semibold transition ${
                                !departmentFilter && !typeFilter ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'
                            }`}
                        >
                            All Positions
                        </button>

                        {departments.map((dept) => (
                            <button
                                key={dept}
                                type="button"
                                onClick={() => setDepartmentFilter(departmentFilter === dept ? '' : dept)}
                                className={`px-3 py-1 rounded-full text-xs font-semibold transition ${
                                    departmentFilter === dept ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'
                                }`}
                            >
                                {dept}
                            </button>
                        ))}
                    </div>
                </div>
            </section>

            {/* Job Listings Grid */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {loading ? (
                    <div className="py-20 text-center text-slate-400">
                        <div className="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p className="text-sm">Fetching verified career opportunities...</p>
                    </div>
                ) : jobs.length === 0 ? (
                    <div className="py-20 text-center bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-lg mx-auto">
                        <i className="fa-solid fa-magnifying-glass text-3xl text-slate-400 mb-3 block"></i>
                        <h3 className="text-base font-bold text-white">No Openings Found</h3>
                        <p className="text-xs text-slate-400 mt-1 mb-4">
                            There are currently no published vacancies matching your search criteria. Expired vacancies are automatically hidden.
                        </p>
                        <button
                            type="button"
                            onClick={() => {
                                setSearch('');
                                setDepartmentFilter('');
                                setTypeFilter('');
                                fetchJobs(1);
                            }}
                            className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-indigo-400 text-xs font-semibold transition"
                        >
                            Reset All Filters
                        </button>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {jobs.map((job) => (
                            <div
                                key={job.id}
                                className="bg-slate-900 border border-slate-800 hover:border-indigo-500/40 rounded-2xl p-6 flex flex-col justify-between transition-all duration-200 hover:-translate-y-1 shadow-lg shadow-black/20 group"
                            >
                                <div>
                                    {/* Badges */}
                                    <div className="flex items-center justify-between mb-3">
                                        <span className="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                            {job.department}
                                        </span>
                                        <span className="text-[11px] text-slate-400 font-mono">
                                            {job.job_code}
                                        </span>
                                    </div>

                                    {/* Title */}
                                    <h3 className="text-lg font-bold text-white group-hover:text-indigo-400 transition-colors line-clamp-1">
                                        {job.job_title}
                                    </h3>
                                    <p className="text-xs text-slate-400 mt-0.5 mb-3 font-medium">
                                        {job.designation} &bull; {job.vacancies} {job.vacancies === 1 ? 'opening' : 'openings'}
                                    </p>

                                    {/* Meta pills */}
                                    <div className="flex flex-wrap gap-2 text-xs mb-4">
                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300">
                                            <i className="fa-solid fa-briefcase text-[10px] text-slate-400"></i>
                                            {job.employment_type}
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300">
                                            <i className="fa-solid fa-clock text-[10px] text-slate-400"></i>
                                            {job.experience_formatted}
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">
                                            <i className="fa-solid fa-money-bill-wave text-[10px]"></i>
                                            {job.salary_formatted}
                                        </span>
                                    </div>

                                    {/* Snippet */}
                                    <p className="text-xs text-slate-400 line-clamp-2 mb-4 leading-relaxed">
                                        {job.job_description_snippet}
                                    </p>

                                    {/* Skills */}
                                    {Array.isArray(job.required_skills) && job.required_skills.length > 0 && (
                                        <div className="flex flex-wrap gap-1.5 mb-4">
                                            {job.required_skills.slice(0, 4).map((skill, idx) => (
                                                <span
                                                    key={idx}
                                                    className="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300"
                                                >
                                                    {skill}
                                                </span>
                                            ))}
                                            {job.required_skills.length > 4 && (
                                                <span className="text-[10px] text-slate-400 px-1 py-0.5">
                                                    +{job.required_skills.length - 4} more
                                                </span>
                                            )}
                                        </div>
                                    )}
                                </div>

                                <div className="pt-4 border-t border-slate-800/80">
                                    <div className="flex items-center justify-between mb-3 text-[11px] text-slate-400">
                                        <span>Deadline: {job.deadline}</span>
                                        {job.days_remaining !== null && (
                                            <span className={`font-semibold ${job.days_remaining < 3 ? 'text-rose-400' : 'text-emerald-400'}`}>
                                                {job.days_remaining > 0 ? `${job.days_remaining}d left` : 'Closes today'}
                                            </span>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-2 gap-2">
                                        <Link
                                            to={`/jobs/${job.job_code}`}
                                            className="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold text-center transition"
                                        >
                                            View Details
                                        </Link>
                                        <Link
                                            to={`/jobs/${job.job_code}/apply`}
                                            className="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold text-center shadow-md shadow-indigo-600/30 transition flex items-center justify-center gap-1.5"
                                        >
                                            <span>Apply Now</span>
                                            <i className="fa-solid fa-arrow-right text-[10px]"></i>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* Pagination */}
                {pagination.last_page > 1 && (
                    <div className="mt-8 flex items-center justify-center gap-2">
                        <button
                            disabled={pagination.current_page <= 1}
                            onClick={() => fetchJobs(pagination.current_page - 1)}
                            className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 disabled:opacity-40 text-xs font-semibold text-white transition"
                        >
                            &larr; Previous
                        </button>
                        <span className="text-xs text-slate-400 px-3">
                            Page {pagination.current_page} of {pagination.last_page}
                        </span>
                        <button
                            disabled={pagination.current_page >= pagination.last_page}
                            onClick={() => fetchJobs(pagination.current_page + 1)}
                            className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 disabled:opacity-40 text-xs font-semibold text-white transition"
                        >
                            Next &rarr;
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
