import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import api from '../../api/client';
import { showSuccess, showError, showConfirm, showToast, showWarning } from '../../utils/alerts';

export default function ApplicationsList() {
    const [searchParams, setSearchParams] = useSearchParams();
    const initialStatus = searchParams.get('status') || 'all';
    const initialJobId = searchParams.get('job_opening_id') || '';

    const [applications, setApplications] = useState([]);
    const [stats, setStats] = useState({
        total: 0,
        pending: 0,
        under_review: 0,
        shortlisted: 0,
        rejected: 0,
        on_hold: 0,
        crm_transferred: 0,
    });
    const [jobOpenings, setJobOpenings] = useState([]);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [loading, setLoading] = useState(true);

    // Filters
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState(initialStatus);
    const [jobFilter, setJobFilter] = useState(initialJobId);
    const [dateFrom, setDateFrom] = useState('');
    const [dateTo, setDateTo] = useState('');

    // Selection for bulk actions & CRM transfer
    const [selectedIds, setSelectedIds] = useState([]);

    // Screening & Details Modal (Module 8 & 10)
    const [activeModalApp, setActiveModalApp] = useState(null);
    const [modalLoading, setModalLoading] = useState(false);
    const [screeningRemarks, setScreeningRemarks] = useState('');
    const [updatingStatus, setUpdatingStatus] = useState(false);

    const fetchApplications = async (page = 1) => {
        setLoading(true);
        try {
            const params = {
                page,
                search: search || undefined,
                job_opening_id: jobFilter || undefined,
                status: statusFilter,
                date_from: dateFrom || undefined,
                date_to: dateTo || undefined,
            };
            const res = await api.get('/admin/applications', { params });
            setApplications(res.data.applications);
            setStats(res.data.stats);
            setJobOpenings(res.data.job_openings || []);
            setPagination(res.data.pagination);
            setSelectedIds([]); // clear selection on reload
        } catch {
            showError('Error', 'Failed to load candidate applications.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchApplications(1);
    }, [statusFilter, jobFilter, dateFrom, dateTo]);

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        fetchApplications(1);
    };

    const handleSelectAll = (e) => {
        if (e.target.checked) {
            setSelectedIds(applications.map((a) => a.id));
        } else {
            setSelectedIds([]);
        }
    };

    const handleSelectOne = (id) => {
        setSelectedIds((prev) =>
            prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
        );
    };

    // Open candidate screening & status history modal
    const openScreeningModal = async (appId) => {
        setModalLoading(true);
        setActiveModalApp(null);
        try {
            const res = await api.get(`/admin/applications/${appId}`);
            setActiveModalApp(res.data);
            setScreeningRemarks(res.data.application.screening_remarks || '');
        } catch {
            showError('Error', 'Unable to fetch candidate application profile.');
        } finally {
            setModalLoading(false);
        }
    };

    // Update status in screening modal (Module 8)
    const handleStatusTransition = async (newStatus) => {
        if (!activeModalApp) return;
        setUpdatingStatus(true);
        try {
            const res = await api.patch(`/admin/applications/${activeModalApp.application.id}/status`, {
                status: newStatus,
                screening_remarks: screeningRemarks,
            });
            showToast(res.data.message, 'success');
            // Refresh modal data to see updated timeline (Module 10)
            const updated = await api.get(`/admin/applications/${activeModalApp.application.id}`);
            setActiveModalApp(updated.data);
            fetchApplications(pagination.current_page);
        } catch (err) {
            showError('Update Failed', err.response?.data?.message || 'Could not update status.');
        } finally {
            setUpdatingStatus(false);
        }
    };

    // Bulk status update
    const handleBulkStatus = async (newStatus) => {
        if (selectedIds.length === 0) {
            showWarning('No Selection', 'Please select at least one candidate application.');
            return;
        }

        const confirmed = await showConfirm(
            `Update ${selectedIds.length} Candidate(s)?`,
            `Mark all selected applications as "${newStatus.replace('_', ' ').toUpperCase()}".`,
            `Yes, Mark as ${newStatus.replace('_', ' ')}`
        );

        if (!confirmed) return;

        try {
            const res = await api.post('/admin/applications/bulk-status', {
                application_ids: selectedIds,
                status: newStatus,
            });
            await showSuccess('Bulk Action Applied!', res.data.message);
            fetchApplications(pagination.current_page);
        } catch (err) {
            showError('Bulk Action Failed', err.response?.data?.message || 'Operation could not be completed.');
        }
    };

    // Transfer shortlisted candidates to CRM (Module 9)
    const handleCrmTransfer = async (singleAppId = null) => {
        const idsToTransfer = singleAppId ? [singleAppId] : selectedIds;

        if (idsToTransfer.length === 0) {
            showWarning('No Candidate Selected', 'Please select shortlisted candidates to transfer to CRM.');
            return;
        }

        // Verify only shortlisted candidates are being transferred
        const targetApps = singleAppId
            ? [activeModalApp?.application]
            : applications.filter((a) => idsToTransfer.includes(a.id));

        const nonShortlisted = targetApps.filter((a) => a && a.status !== 'shortlisted');

        if (nonShortlisted.length > 0) {
            showError(
                'CRM Transfer Restricted',
                `Only shortlisted applicants can be transferred to the CRM. "${nonShortlisted[0].full_name}" is currently "${nonShortlisted[0].status}". Please shortlist the candidate first.`
            );
            return;
        }

        const confirmed = await showConfirm(
            `Transfer ${idsToTransfer.length} Candidate(s) to CRM?`,
            'This will push candidate profile, qualifications, and resume to the Enterprise CRM for interview scheduling.',
            'Yes, Transfer to CRM',
            'info'
        );

        if (!confirmed) return;

        try {
            const res = await api.post('/admin/applications/crm-transfer', {
                application_ids: idsToTransfer,
            });

            await showSuccess(
                'Transferred to CRM Successfully!',
                `${res.data.transferred_count} candidate(s) are now synced with Enterprise CRM.`
            );

            if (activeModalApp && singleAppId) {
                const updated = await api.get(`/admin/applications/${singleAppId}`);
                setActiveModalApp(updated.data);
            }

            fetchApplications(pagination.current_page);
        } catch (err) {
            showError('Transfer Failed', err.response?.data?.message || 'CRM sync could not be completed.');
        }
    };

    // Export applications to CSV
    const handleExport = () => {
        const queryParams = new URLSearchParams({
            search,
            job_opening_id: jobFilter,
            status: statusFilter,
        }).toString();

        window.open(`/api/admin/applications/export?${queryParams}`, '_blank');
        showToast('Application export started...', 'info');
    };

    const getStatusBadge = (status) => {
        const map = {
            pending: 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            submitted: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
            under_review: 'bg-sky-500/10 text-sky-400 border-sky-500/30',
            shortlisted: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 font-bold',
            rejected: 'bg-rose-500/10 text-rose-400 border-rose-500/30',
            on_hold: 'bg-purple-500/10 text-purple-400 border-purple-500/30',
        };
        const cls = map[status] || 'bg-slate-800 text-slate-300 border-slate-700';
        return (
            <span className={`px-2.5 py-0.5 rounded-full text-[10px] uppercase tracking-wider font-semibold border ${cls}`}>
                {status ? status.replace('_', ' ') : 'Pending'}
            </span>
        );
    };

    const getSourceBadge = (source) => {
        const s = (source || 'direct').toLowerCase();
        if (s.includes('linkedin')) {
            return (
                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#0A66C2]/20 text-[#70B5F9] border border-[#0A66C2]/40">
                    <i className="fa-brands fa-linkedin text-[10px]"></i>
                    LinkedIn
                </span>
            );
        }
        if (s.includes('naukri')) {
            return (
                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-sky-500/20 text-sky-300 border border-sky-500/40">
                    Naukri
                </span>
            );
        }
        if (s.includes('indeed')) {
            return (
                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/40">
                    Indeed
                </span>
            );
        }
        if (s.includes('whatsapp')) {
            return (
                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                    <i className="fa-brands fa-whatsapp text-[10px]"></i>
                    WhatsApp
                </span>
            );
        }
        return (
            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                {source || 'Direct'}
            </span>
        );
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-white tracking-tight">Candidate Applications & Screening</h1>
                    <p className="text-sm text-slate-400 mt-1">Review applicant profiles, conduct screening, shortlist talent, and sync with CRM</p>
                </div>
                <div className="flex items-center gap-2.5">
                    <button
                        type="button"
                        onClick={handleExport}
                        className="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-slate-700 transition"
                    >
                        <i className="fa-solid fa-file-excel text-emerald-400"></i>
                        <span>Export CSV</span>
                    </button>
                    <button
                        type="button"
                        onClick={() => fetchApplications(pagination.current_page)}
                        className="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs border border-slate-700 transition"
                        title="Refresh"
                    >
                        <i className="fa-solid fa-arrows-rotate"></i>
                    </button>
                </div>
            </div>

            {/* Quick Status Funnel Cards */}
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
                <div
                    onClick={() => setStatusFilter('all')}
                    className={`cursor-pointer p-3 rounded-2xl border transition ${statusFilter === 'all' ? 'bg-indigo-950/60 border-indigo-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'}`}
                >
                    <p className="text-[10px] uppercase font-bold text-slate-400">All</p>
                    <p className="text-xl font-extrabold text-white mt-1">{stats.total}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('pending')}
                    className={`cursor-pointer p-3 rounded-2xl border transition ${statusFilter === 'pending' ? 'bg-amber-950/60 border-amber-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'}`}
                >
                    <p className="text-[10px] uppercase font-bold text-amber-400">Pending</p>
                    <p className="text-xl font-extrabold text-white mt-1">{stats.pending}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('under_review')}
                    className={`cursor-pointer p-3 rounded-2xl border transition ${statusFilter === 'under_review' ? 'bg-sky-950/60 border-sky-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'}`}
                >
                    <p className="text-[10px] uppercase font-bold text-sky-400">In Review</p>
                    <p className="text-xl font-extrabold text-white mt-1">{stats.under_review}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('shortlisted')}
                    className={`cursor-pointer p-3 rounded-2xl border transition ${statusFilter === 'shortlisted' ? 'bg-emerald-950/60 border-emerald-500 text-white shadow-lg shadow-emerald-950/50' : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'}`}
                >
                    <div className="flex items-center justify-between">
                        <p className="text-[10px] uppercase font-bold text-emerald-400">Shortlisted</p>
                        <i className="fa-solid fa-star text-[10px] text-amber-400"></i>
                    </div>
                    <p className="text-xl font-extrabold text-emerald-400 mt-1">{stats.shortlisted}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('on_hold')}
                    className={`cursor-pointer p-3 rounded-2xl border transition ${statusFilter === 'on_hold' ? 'bg-purple-950/60 border-purple-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'}`}
                >
                    <p className="text-[10px] uppercase font-bold text-purple-400">On Hold</p>
                    <p className="text-xl font-extrabold text-white mt-1">{stats.on_hold}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('rejected')}
                    className={`cursor-pointer p-3 rounded-2xl border transition ${statusFilter === 'rejected' ? 'bg-rose-950/60 border-rose-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'}`}
                >
                    <p className="text-[10px] uppercase font-bold text-rose-400">Rejected</p>
                    <p className="text-xl font-extrabold text-white mt-1">{stats.rejected}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('all')}
                    className="p-3 rounded-2xl border bg-slate-900 border-slate-800 text-slate-400"
                >
                    <div className="flex items-center justify-between">
                        <p className="text-[10px] uppercase font-bold text-violet-400">CRM Synced</p>
                        <i className="fa-solid fa-arrows-rotate text-[10px] text-violet-400"></i>
                    </div>
                    <p className="text-xl font-extrabold text-violet-300 mt-1">{stats.crm_transferred}</p>
                </div>
            </div>

            {/* Filter Bar */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row gap-3 justify-between items-center">
                <form onSubmit={handleSearchSubmit} className="w-full md:w-80 flex gap-2">
                    <div className="relative flex-1">
                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i className="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Candidate, email, phone, code..."
                            className="w-full pl-9 pr-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                    <button
                        type="submit"
                        className="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 rounded-xl transition"
                    >
                        Filter
                    </button>
                </form>

                <div className="w-full md:w-auto flex flex-wrap items-center gap-2.5">
                    {/* Vacancy dropdown */}
                    <select
                        value={jobFilter}
                        onChange={(e) => setJobFilter(e.target.value)}
                        className="px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 max-w-xs"
                    >
                        <option value="">All Vacancies</option>
                        {jobOpenings.map((j) => (
                            <option key={j.id} value={j.id}>
                                {j.job_title} ({j.job_code})
                            </option>
                        ))}
                    </select>

                    {/* Reset button */}
                    <button
                        type="button"
                        onClick={() => {
                            setSearch('');
                            setStatusFilter('all');
                            setJobFilter('');
                            setDateFrom('');
                            setDateTo('');
                            fetchApplications(1);
                        }}
                        className="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 text-xs"
                        title="Clear Filters"
                    >
                        <i className="fa-solid fa-arrow-rotate-left"></i>
                    </button>
                </div>
            </div>

            {/* Bulk Selection Actions Bar */}
            {selectedIds.length > 0 && (
                <div className="bg-indigo-950/70 border border-indigo-500/40 rounded-2xl p-3.5 flex flex-wrap items-center justify-between gap-3 shadow-xl animate-fade-in">
                    <div className="flex items-center gap-2">
                        <span className="w-6 h-6 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-xs text-white">
                            {selectedIds.length}
                        </span>
                        <span className="text-xs font-semibold text-indigo-200">Candidates Selected</span>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            onClick={() => handleBulkStatus('under_review')}
                            className="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-400 text-xs font-semibold transition"
                        >
                            Mark In Review
                        </button>
                        <button
                            type="button"
                            onClick={() => handleBulkStatus('shortlisted')}
                            className="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5"
                        >
                            <i className="fa-solid fa-star text-[10px]"></i>
                            <span>Shortlist Selected</span>
                        </button>
                        <button
                            type="button"
                            onClick={() => handleBulkStatus('on_hold')}
                            className="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-purple-400 text-xs font-semibold transition"
                        >
                            Put On Hold
                        </button>
                        <button
                            type="button"
                            onClick={() => handleBulkStatus('rejected')}
                            className="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-rose-400 text-xs font-semibold transition"
                        >
                            Reject
                        </button>
                        <button
                            type="button"
                            onClick={() => handleCrmTransfer(null)}
                            className="px-4 py-1.5 rounded-lg bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-violet-600/30 transition flex items-center gap-2"
                        >
                            <i className="fa-solid fa-arrows-split-up-and-left text-xs"></i>
                            <span>Transfer to CRM</span>
                        </button>
                    </div>
                </div>
            )}

            {/* Applications Table */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-slate-800/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                            <tr>
                                <th className="px-4 py-3.5 w-10 text-center">
                                    <input
                                        type="checkbox"
                                        checked={applications.length > 0 && selectedIds.length === applications.length}
                                        onChange={handleSelectAll}
                                        className="h-3.5 w-3.5 rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500"
                                    />
                                </th>
                                <th className="px-4 py-3.5">Candidate Details</th>
                                <th className="px-4 py-3.5">Target Position</th>
                                <th className="px-4 py-3.5">Experience & Edu</th>
                                <th className="px-4 py-3.5">Current / Expected</th>
                                <th className="px-4 py-3.5">Channel Source</th>
                                <th className="px-4 py-3.5">Status</th>
                                <th className="px-4 py-3.5">CRM Sync</th>
                                <th className="px-4 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-800/80">
                            {loading ? (
                                <tr>
                                    <td colSpan="9" className="px-6 py-12 text-center text-slate-400">
                                        <div className="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                                        <span>Fetching candidate applications...</span>
                                    </td>
                                </tr>
                            ) : applications.length === 0 ? (
                                <tr>
                                    <td colSpan="9" className="px-6 py-12 text-center text-slate-400">
                                        <i className="fa-solid fa-users text-2xl text-slate-400 mb-2 block"></i>
                                        <span>No candidate applications found matching current criteria.</span>
                                    </td>
                                </tr>
                            ) : (
                                applications.map((app) => (
                                    <tr key={app.id} className="hover:bg-slate-800/40 transition">
                                        <td className="px-4 py-3.5 text-center">
                                            <input
                                                type="checkbox"
                                                checked={selectedIds.includes(app.id)}
                                                onChange={() => handleSelectOne(app.id)}
                                                className="h-3.5 w-3.5 rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500"
                                            />
                                        </td>
                                        <td className="px-4 py-3.5">
                                            <p className="font-bold text-slate-100 hover:text-indigo-400 cursor-pointer" onClick={() => openScreeningModal(app.id)}>
                                                {app.full_name}
                                            </p>
                                            <p className="text-[11px] text-slate-400">{app.email} &bull; {app.phone}</p>
                                            <span className="font-mono text-[10px] text-indigo-400">{app.application_code}</span>
                                        </td>
                                        <td className="px-4 py-3.5">
                                            <p className="font-semibold text-slate-200">{app.job_title}</p>
                                            <p className="text-[11px] text-slate-400 font-mono">{app.job_code} &bull; {app.department}</p>
                                        </td>
                                        <td className="px-4 py-3.5 text-slate-300">
                                            <span className="font-medium">{app.total_experience}</span>
                                            <span className="block text-[11px] text-slate-400">{app.highest_qualification}</span>
                                        </td>
                                        <td className="px-4 py-3.5">
                                            <span className="text-slate-400 block text-[11px]">Cur: {app.current_ctc}</span>
                                            <span className="text-emerald-400 font-semibold block">Exp: {app.expected_ctc}</span>
                                        </td>
                                        <td className="px-4 py-3.5">
                                            {getSourceBadge(app.source)}
                                        </td>
                                        <td className="px-4 py-3.5">
                                            {getStatusBadge(app.status)}
                                        </td>
                                        <td className="px-4 py-3.5">
                                            {app.crm_transfer_status === 'transferred' ? (
                                                <span className="inline-flex items-center gap-1 text-[10px] font-bold text-violet-400 bg-violet-500/10 border border-violet-500/30 px-2 py-0.5 rounded-full">
                                                    <i className="fa-solid fa-check text-[9px]"></i>
                                                    CRM Synced
                                                </span>
                                            ) : (
                                                <span className="text-[10px] text-slate-500">Unsynced</span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3.5 text-right">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <button
                                                    type="button"
                                                    onClick={() => openScreeningModal(app.id)}
                                                    className="px-2.5 py-1 rounded-lg bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white text-xs font-medium transition flex items-center gap-1"
                                                    title="Screen & Review Dossier"
                                                >
                                                    <i className="fa-solid fa-eye text-xs"></i>
                                                    <span>Review</span>
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
                        <span>Showing page {pagination.current_page} of {pagination.last_page} ({pagination.total} total applicants)</span>
                        <div className="flex gap-1">
                            <button
                                disabled={pagination.current_page <= 1}
                                onClick={() => fetchApplications(pagination.current_page - 1)}
                                className="px-3 py-1 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-40 transition"
                            >
                                Prev
                            </button>
                            <button
                                disabled={pagination.current_page >= pagination.last_page}
                                onClick={() => fetchApplications(pagination.current_page + 1)}
                                className="px-3 py-1 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-40 transition"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Candidate Screening, Dossier & Timeline Modal (Modules 8 & 10) */}
            {(modalLoading || activeModalApp) && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm overflow-y-auto">
                    <div className="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-4xl p-6 sm:p-8 shadow-2xl relative max-h-[90vh] overflow-y-auto">
                        {modalLoading ? (
                            <div className="py-16 text-center text-slate-400">
                                <div className="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                                <p className="text-xs">Loading candidate dossier and status history...</p>
                            </div>
                        ) : activeModalApp && (
                            <div className="space-y-6">
                                {/* Modal Header */}
                                <div className="flex items-start justify-between pb-4 border-b border-slate-800">
                                    <div>
                                        <div className="flex items-center gap-2 mb-1">
                                            <span className="font-mono text-xs px-2.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-bold">
                                                {activeModalApp.application.application_code}
                                            </span>
                                            {getStatusBadge(activeModalApp.application.status)}
                                            {getSourceBadge(activeModalApp.application.source)}
                                        </div>
                                        <h2 className="text-2xl font-extrabold text-white tracking-tight">
                                            {activeModalApp.application.full_name}
                                        </h2>
                                        <p className="text-xs text-slate-400 mt-0.5">
                                            Applied for: <span className="text-slate-200 font-semibold">{activeModalApp.job_opening.job_title}</span> ({activeModalApp.job_opening.job_code}) &bull; {activeModalApp.job_opening.department}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => setActiveModalApp(null)}
                                        className="text-slate-400 hover:text-white p-1 rounded-lg"
                                    >
                                        <i className="fa-solid fa-xmark text-xl"></i>
                                    </button>
                                </div>

                                {/* Candidate Profile Grid */}
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                                    <div>
                                        <span className="text-[10px] font-bold uppercase text-slate-400">Email Address</span>
                                        <p className="text-xs font-semibold text-slate-200 mt-0.5">{activeModalApp.application.email}</p>
                                    </div>
                                    <div>
                                        <span className="text-[10px] font-bold uppercase text-slate-400">Phone Number</span>
                                        <p className="text-xs font-semibold text-slate-200 mt-0.5">{activeModalApp.application.phone}</p>
                                    </div>
                                    <div>
                                        <span className="text-[10px] font-bold uppercase text-slate-400">Location</span>
                                        <p className="text-xs font-semibold text-slate-200 mt-0.5">{activeModalApp.application.current_location}</p>
                                    </div>
                                    <div>
                                        <span className="text-[10px] font-bold uppercase text-slate-400">Experience</span>
                                        <p className="text-xs font-semibold text-emerald-400 mt-0.5">{activeModalApp.application.total_experience}</p>
                                    </div>
                                    <div>
                                        <span className="text-[10px] font-bold uppercase text-slate-400">Highest Education</span>
                                        <p className="text-xs font-semibold text-slate-200 mt-0.5">{activeModalApp.application.highest_qualification}</p>
                                    </div>
                                    <div>
                                        <span className="text-[10px] font-bold uppercase text-slate-400">Current CTC</span>
                                        <p className="text-xs font-semibold text-slate-200 mt-0.5">{activeModalApp.application.current_ctc_formatted}</p>
                                    </div>
                                    <div>
                                        <span className="text-[10px] font-bold uppercase text-slate-400">Expected CTC</span>
                                        <p className="text-xs font-semibold text-indigo-400 mt-0.5">{activeModalApp.application.expected_ctc_formatted}</p>
                                    </div>
                                    <div>
                                        <span className="text-[10px] font-bold uppercase text-slate-400">Submitted Date</span>
                                        <p className="text-xs font-semibold text-slate-200 mt-0.5">{activeModalApp.application.submitted_at}</p>
                                    </div>
                                </div>

                                {/* Documents Section (Module 6) */}
                                <div className="space-y-2">
                                    <h3 className="text-xs font-bold uppercase tracking-wider text-indigo-400">
                                        Uploaded Documents ({activeModalApp.documents.length})
                                    </h3>
                                    {activeModalApp.documents.length === 0 ? (
                                        <p className="text-xs text-slate-400">No documents uploaded.</p>
                                    ) : (
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            {activeModalApp.documents.map((doc) => (
                                                <div
                                                    key={doc.id}
                                                    className="flex items-center justify-between p-3 rounded-xl bg-slate-950/70 border border-slate-800"
                                                >
                                                    <div className="flex items-center gap-2.5 truncate">
                                                        <i className="fa-solid fa-file-pdf text-rose-400 text-lg"></i>
                                                        <div className="truncate">
                                                            <p className="text-xs font-semibold text-white truncate">{doc.file_name}</p>
                                                            <span className="text-[10px] text-slate-400 capitalize">{doc.document_type} &bull; {doc.file_size_formatted}</span>
                                                        </div>
                                                    </div>
                                                    <a
                                                        href={doc.download_url}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white text-xs font-semibold transition ml-2 flex items-center gap-1.5"
                                                    >
                                                        <i className="fa-solid fa-download text-xs"></i>
                                                        <span>Download</span>
                                                    </a>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>

                                {/* Screening Review Actions (Module 8) */}
                                <div className="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
                                    <div className="flex items-center justify-between">
                                        <h3 className="text-xs font-bold uppercase tracking-wider text-indigo-400">
                                            Application Screening Decision
                                        </h3>
                                        <span className="text-[11px] text-slate-400">
                                            Current Status: <strong className="text-white uppercase">{activeModalApp.application.status}</strong>
                                        </span>
                                    </div>

                                    <div>
                                        <label className="block text-[11px] font-semibold text-slate-400 mb-1">
                                            Screening Remarks / Interviewer Notes
                                        </label>
                                        <textarea
                                            rows="2"
                                            value={screeningRemarks}
                                            onChange={(e) => setScreeningRemarks(e.target.value)}
                                            placeholder="Enter notes on candidate background, communication, technical fit, or reasons for decision..."
                                            className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans"
                                        ></textarea>
                                    </div>

                                    <div className="flex flex-wrap items-center justify-between gap-2 pt-1">
                                        <div className="flex flex-wrap items-center gap-1.5">
                                            <button
                                                type="button"
                                                disabled={updatingStatus}
                                                onClick={() => handleStatusTransition('under_review')}
                                                className="px-3 py-1.5 rounded-lg bg-sky-600/20 hover:bg-sky-600 text-sky-300 hover:text-white text-xs font-semibold transition"
                                            >
                                                Mark In Review
                                            </button>
                                            <button
                                                type="button"
                                                disabled={updatingStatus}
                                                onClick={() => handleStatusTransition('shortlisted')}
                                                className="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5"
                                            >
                                                <i className="fa-solid fa-star text-[10px]"></i>
                                                <span>Shortlist Candidate</span>
                                            </button>
                                            <button
                                                type="button"
                                                disabled={updatingStatus}
                                                onClick={() => handleStatusTransition('on_hold')}
                                                className="px-3 py-1.5 rounded-lg bg-purple-600/20 hover:bg-purple-600 text-purple-300 hover:text-white text-xs font-semibold transition"
                                            >
                                                Put On Hold
                                            </button>
                                            <button
                                                type="button"
                                                disabled={updatingStatus}
                                                onClick={() => handleStatusTransition('rejected')}
                                                className="px-3 py-1.5 rounded-lg bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white text-xs font-semibold transition"
                                            >
                                                Reject Application
                                            </button>
                                        </div>

                                        {/* Transfer to CRM Action (Module 9) */}
                                        {activeModalApp.application.status === 'shortlisted' ? (
                                            <button
                                                type="button"
                                                disabled={activeModalApp.application.crm_transfer_status === 'transferred'}
                                                onClick={() => handleCrmTransfer(activeModalApp.application.id)}
                                                className="px-4 py-1.5 rounded-lg bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-violet-600/30 transition disabled:opacity-50 flex items-center gap-1.5"
                                            >
                                                <i className="fa-solid fa-arrows-split-up-and-left text-xs"></i>
                                                <span>{activeModalApp.application.crm_transfer_status === 'transferred' ? 'Transferred to CRM' : 'Transfer to CRM'}</span>
                                            </button>
                                        ) : (
                                            <span className="text-[11px] text-slate-500 italic">
                                                (Shortlist first to enable CRM transfer)
                                            </span>
                                        )}
                                    </div>
                                </div>

                                {/* Status History Timeline (Module 10) */}
                                <div className="space-y-3 pt-2">
                                    <h3 className="text-xs font-bold uppercase tracking-wider text-indigo-400 flex items-center gap-2">
                                        <i className="fa-solid fa-clock-rotate-left"></i>
                                        Candidate Timeline & Status Audit Log ({activeModalApp.status_history.length})
                                    </h3>

                                    <div className="space-y-2 border-l-2 border-slate-800 ml-2 pl-4">
                                        {activeModalApp.status_history.map((log) => (
                                            <div key={log.id} className="relative group">
                                                <div className="absolute -left-[21px] top-1.5 w-2.5 h-2.5 rounded-full bg-indigo-500 ring-4 ring-slate-900"></div>
                                                <div className="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80">
                                                    <div className="flex items-center justify-between text-xs">
                                                        <div className="flex items-center gap-2">
                                                            <span className="font-bold text-white capitalize">{log.new_status.replace('_', ' ')}</span>
                                                            {log.old_status && (
                                                                <span className="text-[10px] text-slate-400">
                                                                    (from {log.old_status.replace('_', ' ')})
                                                                </span>
                                                            )}
                                                        </div>
                                                        <span className="text-[10px] text-slate-400">{log.created_at} &bull; {log.time_relative}</span>
                                                    </div>
                                                    {log.remarks && (
                                                        <p className="text-xs text-slate-300 mt-1 leading-relaxed">{log.remarks}</p>
                                                    )}
                                                    <p className="text-[10px] text-slate-400 mt-1">
                                                        Action by: <span className="text-slate-300">{log.changed_by_name}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
