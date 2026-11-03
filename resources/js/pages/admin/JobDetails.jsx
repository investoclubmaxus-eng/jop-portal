import React, { useState, useEffect } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../../api/client';

// ====================== ENCODING HELPERS ======================
const encodeSource = (source) => {
    // Simple Base64 – reversible and enough to hide the plain source
    return btoa(source);
};

const decodeSource = (encoded) => {
    try {
        return atob(encoded);
    } catch {
        return 'direct';
    }
};
// ==============================================================

export default function JobDetails() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [customSource, setCustomSource] = useState('');
    const [copiedKey, setCopiedKey] = useState('');
    const [toastMessage, setToastMessage] = useState('');

    const fetchJobDetails = async (page = 1) => {
        try {
            setLoading(true);
            const res = await api.get(`/admin/jobs/${id}`, { params: { page } });
            setData(res.data);
        } catch {
            setError('Failed to load vacancy specifications and applications.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchJobDetails(1);
    }, [id]);

    const copyToClipboard = (text, key) => {
        navigator.clipboard.writeText(text);
        setCopiedKey(key);
        setToastMessage(`Copied link to clipboard: ${text}`);
        setTimeout(() => setCopiedKey(''), 2500);
        setTimeout(() => setToastMessage(''), 3500);
    };

    const handleStatusChange = async (newStatus) => {
        try {
            const res = await api.patch(`/admin/jobs/${id}/status`, { status: newStatus });
            setToastMessage(res.data.message);
            fetchJobDetails(data?.applications?.pagination?.current_page || 1);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to update status.');
        }
    };

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
                {source || 'Direct'}
            </span>
        );
    };

    if (loading) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[60vh] text-slate-400">
                <div className="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                <p className="text-xs">Loading vacancy specifications & campaign links...</p>
            </div>
        );
    }

    if (error || !data) {
        return (
            <div className="p-6 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400">
                <p className="font-semibold">{error || 'Vacancy not found'}</p>
                <Link to="/admin/jobs" className="mt-3 inline-block text-xs font-semibold text-rose-300 underline">
                    &larr; Back to Job Openings
                </Link>
            </div>
        );
    }

    const { job, promotion_urls: urls, app_stats: stats, applications } = data;
    const origin = window.location.origin;
    const directJobUrl = `${origin}/jobs/${job.job_code}`;
    const directApplyUrl = `${origin}/jobs/${job.job_code}/apply`;
    // const linkedinUrl = `${directApplyUrl}?source=linkedin`;
    // const naukriUrl = `${directApplyUrl}?source=naukri`;
    // const indeedUrl = `${directApplyUrl}?source=indeed`;
    // const whatsappUrl = `${directApplyUrl}?source=whatsapp`;
    // const customUrl = customSource ? `${directApplyUrl}?source=${encodeURIComponent(customSource.trim().toLowerCase().replace(/\s+/g, '_'))}` : directApplyUrl;
    // ========== ENCODED CAMPAIGN URLS ==========
    const linkedinUrl = `${directApplyUrl}?ref=${encodeSource('linkedin')}`;
    const naukriUrl   = `${directApplyUrl}?ref=${encodeSource('naukri')}`;
    const indeedUrl   = `${directApplyUrl}?ref=${encodeSource('indeed')}`;
    const whatsappUrl = `${directApplyUrl}?ref=${encodeSource('whatsapp')}`;

    const customUrl = customSource
        ? `${directApplyUrl}?ref=${encodeSource(
              customSource.trim().toLowerCase().replace(/\s+/g, '_')
          )}`
        : directApplyUrl;
    // ==========================================

    return (
        <div className="max-w-6xl mx-auto space-y-8">
            {/* Toast Notification */}
            {toastMessage && (
                <div className="fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-800 border border-indigo-500/40 text-indigo-300 shadow-2xl flex items-center gap-3 text-xs animate-bounce">
                    <i className="fa-solid fa-check-circle text-emerald-400 text-base"></i>
                    <span>{toastMessage}</span>
                </div>
            )}

            {/* Top Bar / Header */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-xl">
                <div>
                    <div className="flex flex-wrap items-center gap-2 mb-2">
                        <span className="font-mono text-xs px-2.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-bold">
                            {job.job_code}
                        </span>
                        <span className="text-xs px-2.5 py-0.5 rounded bg-slate-800 text-slate-300 font-medium">
                            {job.department}
                        </span>
                        <span className="text-xs px-2.5 py-0.5 rounded bg-slate-800 text-slate-300 font-medium">
                            {job.employment_type}
                        </span>
                        <span className={`text-[10px] uppercase font-bold px-2.5 py-0.5 rounded-full ${
                            job.status === 'published' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' :
                            job.status === 'draft' ? 'bg-slate-800 text-slate-400 border border-slate-700' :
                            'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                        }`}>
                            {job.status}
                        </span>
                    </div>

                    <h1 className="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{job.job_title}</h1>
                    <p className="text-sm text-slate-400 mt-1">
                        Designation: <span className="text-slate-200 font-medium">{job.designation}</span> &bull; Vacancies: <span className="text-slate-200 font-medium">{job.vacancies} open</span> &bull; Posted by: <span className="text-slate-300">{job.creator_name}</span>
                    </p>
                </div>

                <div className="flex flex-wrap items-center gap-2.5 self-start md:self-auto">
                    <Link
                        to={`/admin/jobs/${job.id}/edit`}
                        className="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-2 transition"
                    >
                        <i className="fa-solid fa-pen-to-square"></i>
                        <span>Edit Vacancy</span>
                    </Link>

                    {job.status === 'published' ? (
                        <button
                            type="button"
                            onClick={() => handleStatusChange('draft')}
                            className="px-3.5 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-semibold flex items-center gap-2 transition"
                        >
                            <i className="fa-solid fa-eye-slash"></i>
                            <span>Unpublish (Draft)</span>
                        </button>
                    ) : (
                        <button
                            type="button"
                            onClick={() => handleStatusChange('published')}
                            className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition"
                        >
                            <i className="fa-solid fa-paper-plane"></i>
                            <span>Publish Live</span>
                        </button>
                    )}

                    <Link
                        to={`/jobs/${job.job_code}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="px-3.5 py-2 rounded-xl bg-indigo-950/60 hover:bg-indigo-900 border border-indigo-800/80 text-indigo-300 text-xs font-semibold flex items-center gap-1.5 transition"
                    >
                        <i className="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        <span>Public View</span>
                    </Link>
                </div>
            </div>

            {/* Promotion & Social Campaign URLs Box */}
            <div className="bg-gradient-to-br from-indigo-950/40 via-slate-900 to-slate-900 border border-indigo-500/30 rounded-2xl p-6 sm:p-8 shadow-2xl">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-indigo-500/20 gap-3">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-indigo-400">
                            <i className="fa-solid fa-share-nodes text-lg"></i>
                        </div>
                        <div>
                            <h2 className="text-base font-bold text-white">Social Campaign & Job Board Promotion URLs</h2>
                            <p className="text-xs text-indigo-300/80">
                                Share these custom links on LinkedIn, Naukri, or Indeed. When candidates apply via these links, the application source is automatically tracked!
                            </p>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    {/* LinkedIn Link Card */}
                    <div className="p-4 rounded-xl bg-slate-900/90 border border-slate-800 hover:border-[#0A66C2]/50 transition">
                        <div className="flex items-center justify-between mb-2">
                            <div className="flex items-center gap-2">
                                <span className="w-7 h-7 rounded-lg bg-[#0A66C2] text-white flex items-center justify-center text-xs">
                                    <i className="fa-brands fa-linkedin-in"></i>
                                </span>
                                <span className="text-xs font-bold text-white">LinkedIn Campaign Link</span>
                            </div>
                            <span className="text-[10px] px-2 py-0.5 rounded bg-[#0A66C2]/20 text-[#70B5F9] font-mono">
                                source=linkedin
                            </span>
                        </div>
                        <input
                            type="text"
                            readOnly
                            value={linkedinUrl}
                            className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs font-mono text-slate-300 select-all focus:outline-none mb-3"
                        />
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] text-slate-400">Opens direct apply form</span>
                            <button
                                type="button"
                                onClick={() => copyToClipboard(linkedinUrl, 'linkedin')}
                                className="px-3 py-1.5 rounded-lg bg-[#0A66C2] hover:bg-[#084e96] text-white text-xs font-semibold flex items-center gap-1.5 transition"
                            >
                                <i className={`fa-solid ${copiedKey === 'linkedin' ? 'fa-check' : 'fa-copy'} text-xs`}></i>
                                <span>{copiedKey === 'linkedin' ? 'Copied!' : 'Copy Link'}</span>
                            </button>
                        </div>
                    </div>

                    {/* Naukri Link Card */}
                    <div className="p-4 rounded-xl bg-slate-900/90 border border-slate-800 hover:border-sky-500/50 transition">
                        <div className="flex items-center justify-between mb-2">
                            <div className="flex items-center gap-2">
                                <span className="w-7 h-7 rounded-lg bg-sky-600 text-white flex items-center justify-center text-xs font-bold">
                                    N
                                </span>
                                <span className="text-xs font-bold text-white">Naukri.com Campaign Link</span>
                            </div>
                            <span className="text-[10px] px-2 py-0.5 rounded bg-sky-500/20 text-sky-300 font-mono">
                                source=naukri
                            </span>
                        </div>
                        <input
                            type="text"
                            readOnly
                            value={naukriUrl}
                            className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs font-mono text-slate-300 select-all focus:outline-none mb-3"
                        />
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] text-slate-400">Post in job portal description</span>
                            <button
                                type="button"
                                onClick={() => copyToClipboard(naukriUrl, 'naukri')}
                                className="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold flex items-center gap-1.5 transition"
                            >
                                <i className={`fa-solid ${copiedKey === 'naukri' ? 'fa-check' : 'fa-copy'} text-xs`}></i>
                                <span>{copiedKey === 'naukri' ? 'Copied!' : 'Copy Link'}</span>
                            </button>
                        </div>
                    </div>

                    {/* Indeed Link Card */}
                    <div className="p-4 rounded-xl bg-slate-900/90 border border-slate-800 hover:border-blue-500/50 transition">
                        <div className="flex items-center justify-between mb-2">
                            <div className="flex items-center gap-2">
                                <span className="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-bold">
                                    in
                                </span>
                                <span className="text-xs font-bold text-white">Indeed Campaign Link</span>
                            </div>
                            <span className="text-[10px] px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 font-mono">
                                source=indeed
                            </span>
                        </div>
                        <input
                            type="text"
                            readOnly
                            value={indeedUrl}
                            className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs font-mono text-slate-300 select-all focus:outline-none mb-3"
                        />
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] text-slate-400">Indeed external redirect URL</span>
                            <button
                                type="button"
                                onClick={() => copyToClipboard(indeedUrl, 'indeed')}
                                className="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold flex items-center gap-1.5 transition"
                            >
                                <i className={`fa-solid ${copiedKey === 'indeed' ? 'fa-check' : 'fa-copy'} text-xs`}></i>
                                <span>{copiedKey === 'indeed' ? 'Copied!' : 'Copy Link'}</span>
                            </button>
                        </div>
                    </div>

                    {/* WhatsApp Link Card */}
                    <div className="p-4 rounded-xl bg-slate-900/90 border border-slate-800 hover:border-emerald-500/50 transition">
                        <div className="flex items-center justify-between mb-2">
                            <div className="flex items-center gap-2">
                                <span className="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs">
                                    <i className="fa-brands fa-whatsapp"></i>
                                </span>
                                <span className="text-xs font-bold text-white">WhatsApp & Chat Share Link</span>
                            </div>
                            <span className="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono">
                                source=whatsapp
                            </span>
                        </div>
                        <input
                            type="text"
                            readOnly
                            value={whatsappUrl}
                            className="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs font-mono text-slate-300 select-all focus:outline-none mb-3"
                        />
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] text-slate-400">Quick share via messaging</span>
                            <button
                                type="button"
                                onClick={() => copyToClipboard(whatsappUrl, 'whatsapp')}
                                className="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition"
                            >
                                <i className={`fa-solid ${copiedKey === 'whatsapp' ? 'fa-check' : 'fa-copy'} text-xs`}></i>
                                <span>{copiedKey === 'whatsapp' ? 'Copied!' : 'Copy Link'}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {/* Custom Campaign Source Generator */}
                <div className="mt-5 p-4 rounded-xl bg-slate-950/70 border border-slate-800">
                    <p className="text-xs font-bold text-white mb-1">Generate Custom Campaign Promotion URL</p>
                    <p className="text-[11px] text-slate-400 mb-3">Create links for campus drives, email blasts, QR codes, or agency partners</p>
                    <div className="flex flex-col sm:flex-row gap-2">
                        <input
                            type="text"
                            placeholder="Enter campaign source tag (e.g. campus_hire_2026, tech_summit)"
                            value={customSource}
                            onChange={(e) => setCustomSource(e.target.value)}
                            className="flex-1 px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <button
                            type="button"
                            onClick={() => copyToClipboard(customUrl, 'custom')}
                            className="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold flex items-center justify-center gap-2 transition"
                        >
                            <i className={`fa-solid ${copiedKey === 'custom' ? 'fa-check' : 'fa-link'} text-xs`}></i>
                            <span>{copiedKey === 'custom' ? 'Copied!' : 'Copy Custom URL'}</span>
                        </button>
                    </div>
                    {customSource && (
                        <p className="mt-2 text-[11px] font-mono text-indigo-400 break-all">
                            Target URL: {customUrl}
                        </p>
                    )}
                </div>
            </div>

            {/* Position Overview & Specs */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                        <h2 className="text-base font-bold text-white mb-4 flex items-center gap-2">
                            <i className="fa-solid fa-align-left text-indigo-400"></i>
                            Job Description & Deliverables
                        </h2>
                        <div className="text-xs text-slate-300 leading-relaxed whitespace-pre-line bg-slate-950/50 p-4 rounded-xl border border-slate-800/80">
                            {job.job_description}
                        </div>
                    </div>

                    {job.required_qualifications && (
                        <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                            <h2 className="text-base font-bold text-white mb-3 flex items-center gap-2">
                                <i className="fa-solid fa-graduation-cap text-indigo-400"></i>
                                Required Qualifications & Eligibility
                            </h2>
                            <p className="text-xs text-slate-300 leading-relaxed whitespace-pre-line bg-slate-950/50 p-4 rounded-xl border border-slate-800/80">
                                {job.required_qualifications}
                            </p>
                        </div>
                    )}
                </div>

                <div className="space-y-6">
                    {/* Key Attributes Card */}
                    <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                        <h2 className="text-sm font-bold uppercase tracking-wider text-indigo-400 border-b border-slate-800 pb-2">
                            Position Attributes
                        </h2>

                        <div className="space-y-3 text-xs">
                            <div className="flex justify-between items-center py-1 border-b border-slate-800/60">
                                <span className="text-slate-400">Salary Package</span>
                                <span className="font-semibold text-slate-200">{job.salary_formatted}</span>
                            </div>
                            <div className="flex justify-between items-center py-1 border-b border-slate-800/60">
                                <span className="text-slate-400">Experience Needed</span>
                                <span className="font-semibold text-slate-200">{job.experience_formatted}</span>
                            </div>
                            <div className="flex justify-between items-center py-1 border-b border-slate-800/60">
                                <span className="text-slate-400">Application Deadline</span>
                                <span className="font-semibold text-slate-200">{job.application_deadline || 'Open until filled'}</span>
                            </div>
                            {job.days_remaining !== null && (
                                <div className="flex justify-between items-center py-1 border-b border-slate-800/60">
                                    <span className="text-slate-400">Time Left</span>
                                    <span className={`font-bold ${job.days_remaining < 3 ? 'text-rose-400' : 'text-emerald-400'}`}>
                                        {job.days_remaining > 0 ? `${job.days_remaining} days remaining` : 'Expired'}
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Required Skills */}
                        <div className="pt-2">
                            <span className="text-xs font-semibold text-slate-400 block mb-2">Required Skills & Tech:</span>
                            <div className="flex flex-wrap gap-1.5">
                                {Array.isArray(job.required_skills) && job.required_skills.length > 0 ? (
                                    job.required_skills.map((skill, idx) => (
                                        <span
                                            key={idx}
                                            className="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20"
                                        >
                                            {skill}
                                        </span>
                                    ))
                                ) : (
                                    <span className="text-xs text-slate-400">None specified</span>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Quick Conversion Summary */}
                    <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                        <h2 className="text-sm font-bold uppercase tracking-wider text-indigo-400 border-b border-slate-800 pb-2 mb-3">
                            Applicant Pipeline
                        </h2>
                        <div className="grid grid-cols-2 gap-3 text-center">
                            <div className="p-3 rounded-xl bg-slate-950 border border-slate-800">
                                <p className="text-xl font-bold text-white">{stats?.total || 0}</p>
                                <p className="text-[10px] uppercase text-slate-400 font-semibold mt-0.5">Total</p>
                            </div>
                            <div className="p-3 rounded-xl bg-slate-950 border border-slate-800">
                                <p className="text-xl font-bold text-amber-400">{stats?.pending || 0}</p>
                                <p className="text-[10px] uppercase text-slate-400 font-semibold mt-0.5">Pending</p>
                            </div>
                            <div className="p-3 rounded-xl bg-slate-950 border border-slate-800">
                                <p className="text-xl font-bold text-emerald-400">{stats?.shortlisted || 0}</p>
                                <p className="text-[10px] uppercase text-slate-400 font-semibold mt-0.5">Shortlisted</p>
                            </div>
                            <div className="p-3 rounded-xl bg-slate-950 border border-slate-800">
                                <p className="text-xl font-bold text-violet-400">{stats?.crm_transferred || 0}</p>
                                <p className="text-[10px] uppercase text-slate-400 font-semibold mt-0.5">CRM Synced</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Received Candidate Applications Table */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
                <div className="p-6 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 className="text-base font-bold text-white flex items-center gap-2">
                            <i className="fa-solid fa-users text-indigo-400"></i>
                            Candidates Applied for this Vacancy ({applications?.pagination?.total || 0})
                        </h2>
                        <p className="text-xs text-slate-400 mt-0.5">All applications submitted directly or via promotion campaigns</p>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-slate-800/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                            <tr>
                                <th className="px-6 py-3.5">Candidate</th>
                                <th className="px-6 py-3.5">Experience & Edu</th>
                                <th className="px-6 py-3.5">Expected CTC</th>
                                <th className="px-6 py-3.5">Campaign Source</th>
                                <th className="px-6 py-3.5">Status</th>
                                <th className="px-6 py-3.5">Applied Date</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-800/80">
                            {(!applications?.data || applications.data.length === 0) ? (
                                <tr>
                                    <td colSpan="6" className="px-6 py-10 text-center text-slate-400">
                                        <i className="fa-regular fa-folder-open text-2xl text-slate-400 mb-2 block"></i>
                                        <span>No applications received yet for this vacancy. Share the campaign links above!</span>
                                    </td>
                                </tr>
                            ) : (
                                applications.data.map((app) => (
                                    <tr key={app.id} className="hover:bg-slate-800/40 transition">
                                        <td className="px-6 py-4">
                                            <p className="font-semibold text-slate-100">{app.full_name}</p>
                                            <p className="text-[11px] text-slate-400">{app.email} &bull; {app.phone}</p>
                                            <span className="text-[10px] font-mono text-indigo-400">{app.application_code}</span>
                                        </td>
                                        <td className="px-6 py-4 text-slate-300">
                                            <span>{app.total_experience}</span>
                                            <span className="block text-[11px] text-slate-400">{app.highest_qualification}</span>
                                        </td>
                                        <td className="px-6 py-4 text-slate-300 font-medium">
                                            {app.expected_ctc}
                                        </td>
                                        <td className="px-6 py-4">
                                            {getSourceBadge(app.source)}
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                                {app.status ? app.status.replace('_', ' ') : 'Pending'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-slate-400">
                                            {app.submitted_at}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {applications?.pagination?.last_page > 1 && (
                    <div className="px-6 py-3.5 bg-slate-800/40 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <span>Page {applications.pagination.current_page} of {applications.pagination.last_page}</span>
                        <div className="flex gap-1">
                            <button
                                disabled={applications.pagination.current_page <= 1}
                                onClick={() => fetchJobDetails(applications.pagination.current_page - 1)}
                                className="px-3 py-1 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-40"
                            >
                                Prev
                            </button>
                            <button
                                disabled={applications.pagination.current_page >= applications.pagination.last_page}
                                onClick={() => fetchJobDetails(applications.pagination.current_page + 1)}
                                className="px-3 py-1 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-40"
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
