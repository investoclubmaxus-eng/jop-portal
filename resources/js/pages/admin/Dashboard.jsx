import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/client';

export default function Dashboard() {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    const fetchDashboard = async () => {
        try {
            setLoading(true);
            const res = await api.get('/admin/dashboard');
            setData(res.data);
        } catch {
            setError('Failed to load recruitment dashboard analytics.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchDashboard();
    }, []);

    const getSourceBadge = (source) => {
        const s = (source || 'direct').toLowerCase();
        if (s.includes('linkedin')) {
            return (
                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#0A66C2]/20 text-[#70B5F9] border border-[#0A66C2]/40">
                    <i className="fa-brands fa-linkedin"></i>
                    LinkedIn
                </span>
            );
        }
        if (s.includes('naukri')) {
            return (
                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-sky-500/20 text-sky-300 border border-sky-500/40">
                    <i className="fa-solid fa-briefcase"></i>
                    Naukri
                </span>
            );
        }
        if (s.includes('indeed')) {
            return (
                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/40">
                    <i className="fa-solid fa-i"></i>
                    Indeed
                </span>
            );
        }
        if (s.includes('whatsapp')) {
            return (
                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                    <i className="fa-brands fa-whatsapp"></i>
                    WhatsApp
                </span>
            );
        }
        return (
            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                <i className="fa-solid fa-globe"></i>
                {source || 'Direct Portal'}
            </span>
        );
    };

    const getStatusBadge = (status) => {
        const map = {
            pending: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            submitted: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
            under_review: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            shortlisted: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            rejected: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
        };
        const cls = map[status] || 'bg-slate-800 text-slate-400 border-slate-700';
        return (
            <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border ${cls}`}>
                {status ? status.replace('_', ' ') : 'Pending'}
            </span>
        );
    };

    if (loading) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[60vh] text-slate-400">
                <div className="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                <p className="text-sm font-medium">Aggregating recruitment metrics & statistics...</p>
            </div>
        );
    }

    if (error) {
        return (
            <div className="p-6 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400">
                <p className="font-semibold">{error}</p>
                <button
                    onClick={fetchDashboard}
                    className="mt-3 px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-semibold hover:bg-rose-500 transition"
                >
                    Retry Loading
                </button>
            </div>
        );
    }

    const m = data?.metrics || {};

    return (
        <div className="space-y-8">
            {/* Page Header */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-white tracking-tight">Recruitment Operations Dashboard</h1>
                    <p className="text-sm text-slate-400 mt-1">Real-time candidate pipeline, vacancy health, and multi-channel acquisition tracking</p>
                </div>
                <div className="flex items-center gap-3">
                    <button
                        onClick={fetchDashboard}
                        className="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition text-xs flex items-center gap-2"
                        title="Refresh metrics"
                    >
                        <i className="fa-solid fa-arrows-rotate"></i>
                        <span className="hidden sm:inline">Refresh Data</span>
                    </button>
                    <Link
                        to="/admin/jobs/create"
                        className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center gap-2"
                    >
                        <i className="fa-solid fa-plus text-xs"></i>
                        <span>Create Job Opening</span>
                    </Link>
                </div>
            </div>

            {/* 10 Key Metrics Cards Grid */}
            <div className="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                {/* 1. Total Job Openings */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm hover:border-slate-700 transition">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Openings</span>
                        <div className="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                            <i className="fa-solid fa-briefcase text-xs"></i>
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-white mt-2">{m.total_job_openings || 0}</p>
                    <p className="text-[11px] text-slate-400 mt-1">Total created positions</p>
                </div>

                {/* 2. Active Vacancies */}
                <div className="bg-slate-900 border border-emerald-900/30 rounded-2xl p-4 shadow-sm hover:border-emerald-800/40 transition">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Active Vacancies</span>
                        <div className="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <i className="fa-solid fa-bolt text-xs"></i>
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-emerald-400 mt-2">{m.active_vacancies || 0}</p>
                    <p className="text-[11px] text-slate-400 mt-1">Currently open to candidates</p>
                </div>

                {/* 3. Expired Vacancies */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm hover:border-slate-700 transition">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Expired Vacancies</span>
                        <div className="w-8 h-8 rounded-lg bg-slate-800 text-slate-400 flex items-center justify-center">
                            <i className="fa-solid fa-clock-rotate-left text-xs"></i>
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-slate-300 mt-2">{m.expired_vacancies || 0}</p>
                    <p className="text-[11px] text-slate-400 mt-1">Past deadline or closed</p>
                </div>

                {/* 4. Total Applications */}
                <div className="bg-slate-900 border border-indigo-900/40 rounded-2xl p-4 shadow-sm hover:border-indigo-800/50 transition">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Total Applications</span>
                        <div className="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                            <i className="fa-solid fa-file-invoice text-xs"></i>
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-white mt-2">{m.total_applications || 0}</p>
                    <p className="text-[11px] text-slate-400 mt-1">Across all campaigns</p>
                </div>

                {/* 5. Received Today */}
                <div className="bg-slate-900 border border-sky-900/40 rounded-2xl p-4 shadow-sm hover:border-sky-800/50 transition">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-sky-400 uppercase tracking-wider">Received Today</span>
                        <div className="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center">
                            <i className="fa-solid fa-calendar-day text-xs"></i>
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-sky-400 mt-2">{m.applications_today || 0}</p>
                    <p className="text-[11px] text-slate-400 mt-1">Submitted in last 24h</p>
                </div>

                {/* 6. Pending Review */}
                <div className="bg-slate-900 border border-amber-900/30 rounded-2xl p-4 shadow-sm hover:border-amber-800/40 transition">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-amber-400 uppercase tracking-wider">Pending Review</span>
                        <div className="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                            <i className="fa-solid fa-hourglass-half text-xs"></i>
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-amber-400 mt-2">{m.pending_applications || 0}</p>
                    <p className="text-[11px] text-slate-400 mt-1">Awaiting recruiter action</p>
                </div>

                {/* 7. Shortlisted */}
                <div className="bg-slate-900 border border-emerald-900/40 rounded-2xl p-4 shadow-sm hover:border-emerald-800/50 transition">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Shortlisted</span>
                        <div className="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <i className="fa-solid fa-user-check text-xs"></i>
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-emerald-400 mt-2">{m.shortlisted_applications || 0}</p>
                    <p className="text-[11px] text-slate-400 mt-1">Cleared initial screening</p>
                </div>

                {/* 8. Rejected */}
                <div className="bg-slate-900 border border-rose-900/30 rounded-2xl p-4 shadow-sm hover:border-rose-800/40 transition">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-rose-400 uppercase tracking-wider">Rejected</span>
                        <div className="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
                            <i className="fa-solid fa-user-xmark text-xs"></i>
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-rose-400 mt-2">{m.rejected_applications || 0}</p>
                    <p className="text-[11px] text-slate-400 mt-1">Not meeting criteria</p>
                </div>

                {/* 9. Transferred to CRM */}
                <div className="bg-slate-900 border border-violet-900/40 rounded-2xl p-4 shadow-sm hover:border-violet-800/50 transition">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-violet-400 uppercase tracking-wider">CRM Transferred</span>
                        <div className="w-8 h-8 rounded-lg bg-violet-500/10 text-violet-400 flex items-center justify-center">
                            <i className="fa-solid fa-arrows-split-up-and-left text-xs"></i>
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-violet-400 mt-2">{m.crm_transferred_applications || 0}</p>
                    <p className="text-[11px] text-slate-400 mt-1">Synced to Enterprise CRM</p>
                </div>

                {/* 10. Draft Vacancies */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm hover:border-slate-700 transition">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Draft Openings</span>
                        <div className="w-8 h-8 rounded-lg bg-slate-800 text-slate-400 flex items-center justify-center">
                            <i className="fa-solid fa-pen-ruler text-xs"></i>
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-slate-300 mt-2">{m.draft_vacancies || 0}</p>
                    <p className="text-[11px] text-slate-400 mt-1">Unpublished vacancies</p>
                </div>
            </div>

            {/* Pipeline Visual Funnel */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <div className="flex items-center justify-between mb-4">
                    <h2 className="text-base font-semibold text-white flex items-center gap-2">
                        <i className="fa-solid fa-filter text-indigo-400"></i>
                        Recruitment Pipeline Throughput
                    </h2>
                    <span className="text-xs text-slate-400">Total pipeline: {m.total_applications || 0} candidates</span>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div className="p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                        <div className="flex justify-between items-center mb-1">
                            <span className="text-xs font-semibold text-slate-400">1. Applications Received</span>
                            <span className="text-xs font-bold text-white">{m.total_applications || 0}</span>
                        </div>
                        <div className="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div className="bg-indigo-500 h-full rounded-full" style={{ width: '100%' }}></div>
                        </div>
                    </div>

                    <div className="p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                        <div className="flex justify-between items-center mb-1">
                            <span className="text-xs font-semibold text-slate-400">2. In Review / Pending</span>
                            <span className="text-xs font-bold text-amber-400">{m.pending_applications || 0}</span>
                        </div>
                        <div className="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div
                                className="bg-amber-500 h-full rounded-full transition-all duration-500"
                                style={{ width: `${m.total_applications ? Math.round((m.pending_applications / m.total_applications) * 100) : 0}%` }}
                            ></div>
                        </div>
                    </div>

                    <div className="p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                        <div className="flex justify-between items-center mb-1">
                            <span className="text-xs font-semibold text-slate-400">3. Shortlisted Candidates</span>
                            <span className="text-xs font-bold text-emerald-400">{m.shortlisted_applications || 0}</span>
                        </div>
                        <div className="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div
                                className="bg-emerald-500 h-full rounded-full transition-all duration-500"
                                style={{ width: `${m.total_applications ? Math.round((m.shortlisted_applications / m.total_applications) * 100) : 0}%` }}
                            ></div>
                        </div>
                    </div>

                    <div className="p-4 rounded-xl bg-slate-950/60 border border-slate-800">
                        <div className="flex justify-between items-center mb-1">
                            <span className="text-xs font-semibold text-slate-400">4. CRM Transferred</span>
                            <span className="text-xs font-bold text-violet-400">{m.crm_transferred_applications || 0}</span>
                        </div>
                        <div className="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div
                                className="bg-violet-500 h-full rounded-full transition-all duration-500"
                                style={{ width: `${m.total_applications ? Math.round((m.crm_transferred_applications / m.total_applications) * 100) : 0}%` }}
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Recent Applications Table */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
                <div className="p-6 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 className="text-base font-semibold text-white flex items-center gap-2">
                            <i className="fa-solid fa-clock-rotate-left text-indigo-400"></i>
                            Recent Candidate Applications
                        </h2>
                        <p className="text-xs text-slate-400 mt-0.5">Latest submissions with acquisition source tracking</p>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-slate-800/60 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                            <tr>
                                <th className="px-6 py-3.5">Candidate</th>
                                <th className="px-6 py-3.5">Position</th>
                                <th className="px-6 py-3.5">Source</th>
                                <th className="px-6 py-3.5">Experience</th>
                                <th className="px-6 py-3.5">Status</th>
                                <th className="px-6 py-3.5">Applied At</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-800/80">
                            {(!data?.recent_applications || data.recent_applications.length === 0) ? (
                                <tr>
                                    <td colSpan="6" className="px-6 py-8 text-center text-slate-400">
                                        No recent candidate applications recorded.
                                    </td>
                                </tr>
                            ) : (
                                data.recent_applications.map((app) => (
                                    <tr key={app.id} className="hover:bg-slate-800/40 transition">
                                        <td className="px-6 py-3.5">
                                            <p className="font-semibold text-slate-100">{app.full_name}</p>
                                            <p className="text-slate-400 text-[11px]">{app.email} &bull; {app.phone}</p>
                                        </td>
                                        <td className="px-6 py-3.5 font-medium text-slate-300">
                                            {app.job_title}
                                            {app.job_code && <span className="block text-[10px] text-slate-400 font-mono">{app.job_code}</span>}
                                        </td>
                                        <td className="px-6 py-3.5">
                                            {getSourceBadge(app.source)}
                                        </td>
                                        <td className="px-6 py-3.5 text-slate-300">
                                            {app.total_experience}
                                        </td>
                                        <td className="px-6 py-3.5">
                                            {getStatusBadge(app.status)}
                                        </td>
                                        <td className="px-6 py-3.5 text-slate-400">
                                            <span>{app.created_at}</span>
                                            <span className="block text-[10px] text-slate-400">{app.created_relative}</span>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Vacancy-Wise Application Breakdown Table */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
                <div className="p-6 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 className="text-base font-semibold text-white flex items-center gap-2">
                            <i className="fa-solid fa-chart-column text-indigo-400"></i>
                            Vacancy-Wise Application Performance
                        </h2>
                        <p className="text-xs text-slate-400 mt-0.5">Application count breakdown per opening position</p>
                    </div>
                    <Link
                        to="/admin/jobs"
                        className="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1.5"
                    >
                        <span>Manage All Openings</span>
                        <i className="fa-solid fa-arrow-right text-[10px]"></i>
                    </Link>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-slate-800/60 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                            <tr>
                                <th className="px-6 py-3.5">Job Opening</th>
                                <th className="px-6 py-3.5">Department</th>
                                <th className="px-6 py-3.5 text-center">Vacancies</th>
                                <th className="px-6 py-3.5 text-center">Total Apps</th>
                                <th className="px-6 py-3.5 text-center">Shortlisted</th>
                                <th className="px-6 py-3.5 text-center">CRM Synced</th>
                                <th className="px-6 py-3.5">Status</th>
                                <th className="px-6 py-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-800/80">
                            {(!data?.vacancy_wise_stats?.data || data.vacancy_wise_stats.data.length === 0) ? (
                                <tr>
                                    <td colSpan="8" className="px-6 py-8 text-center text-slate-400">
                                        No job openings currently registered.
                                    </td>
                                </tr>
                            ) : (
                                data.vacancy_wise_stats.data.map((v) => (
                                    <tr key={v.id} className="hover:bg-slate-800/40 transition">
                                        <td className="px-6 py-3.5">
                                            <p className="font-semibold text-slate-100">{v.job_title}</p>
                                            <p className="text-[11px] text-slate-400 font-mono">{v.job_code}</p>
                                        </td>
                                        <td className="px-6 py-3.5 text-slate-300">
                                            {v.department}
                                        </td>
                                        <td className="px-6 py-3.5 text-center font-bold text-slate-200">
                                            {v.vacancies}
                                        </td>
                                        <td className="px-6 py-3.5 text-center">
                                            <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                                {v.total_applications}
                                            </span>
                                        </td>
                                        <td className="px-6 py-3.5 text-center font-semibold text-emerald-400">
                                            {v.shortlisted_count}
                                        </td>
                                        <td className="px-6 py-3.5 text-center font-semibold text-violet-400">
                                            {v.crm_transferred_count}
                                        </td>
                                        <td className="px-6 py-3.5">
                                            <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${
                                                v.status === 'published' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' :
                                                v.status === 'draft' ? 'bg-slate-800 text-slate-400 border border-slate-700' :
                                                'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                                            }`}>
                                                {v.status}
                                            </span>
                                        </td>
                                        <td className="px-6 py-3.5 text-right">
                                            <Link
                                                to={`/admin/jobs/${v.id}`}
                                                className="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-400 hover:text-indigo-300 text-xs font-medium inline-flex items-center gap-1.5 transition"
                                            >
                                                <span>View & Promote</span>
                                                <i className="fa-solid fa-arrow-right text-[10px]"></i>
                                            </Link>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}
