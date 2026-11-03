import React, { useState, useEffect } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../../api/client';
import { showToast } from '../../utils/alerts';

export default function JobDetail() {
    const { jobCode } = useParams();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [shareCopied, setShareCopied] = useState(false);

    useEffect(() => {
        const fetchJob = async () => {
            setLoading(true);
            try {
                const res = await api.get(`/jobs/${jobCode}`);
                setData(res.data);
            } catch (err) {
                setError(err.response?.data?.message || 'Job vacancy not found.');
            } finally {
                setLoading(false);
            }
        };

        fetchJob();
        window.scrollTo(0, 0);
    }, [jobCode]);

    const handleShare = () => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href);
        }
        setShareCopied(true);
        showToast('Link copied to clipboard!', 'success');
        setTimeout(() => setShareCopied(false), 3000);
    };

    if (loading) {
        return (
            <div className="w-full min-h-[70vh] bg-slate-950 flex flex-col items-center justify-center text-slate-400 py-24">
                <div className="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p className="text-sm font-medium text-slate-300">Loading position specifications...</p>
                <p className="text-xs text-slate-500 mt-1">Fetching vacancy {jobCode}</p>
            </div>
        );
    }

    if (error || !data?.job) {
        return (
            <div className="w-full min-h-[70vh] bg-slate-950 py-16 px-4 flex items-center justify-center">
                <div className="max-w-xl w-full p-8 sm:p-10 rounded-3xl bg-slate-900 border border-slate-800 text-center shadow-2xl">
                    <div className="w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i className="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <h2 className="text-xl font-bold text-white">Opening Unavailable</h2>
                    <p className="text-xs sm:text-sm text-slate-400 mt-2 mb-6 leading-relaxed">
                        {error || 'This vacancy may have expired, reached its deadline, or been closed by our recruitment team.'}
                    </p>
                    <Link
                        to="/jobs"
                        className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition"
                    >
                        <i className="fa-solid fa-arrow-left"></i>
                        <span>Browse All Active Openings</span>
                    </Link>
                </div>
            </div>
        );
    }

    const { job, related_jobs: related } = data;

    return (
        <div className="w-full bg-slate-950 min-h-[calc(100vh-140px)] text-slate-100 py-8 px-4 sm:px-6 lg:px-8">
            <div className="max-w-6xl mx-auto space-y-8">
                {/* Breadcrumbs Navigation */}
                <div className="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                    <Link to="/jobs" className="hover:text-indigo-400 transition flex items-center gap-1.5 font-medium">
                        <i className="fa-solid fa-briefcase text-[11px]"></i>
                        <span>Careers Board</span>
                    </Link>
                    <span>/</span>
                    <span className="text-slate-300">{job.department || 'General'}</span>
                    <span>/</span>
                    <span className="text-indigo-400 font-mono font-semibold">{job.job_code}</span>
                </div>

                {/* Main Hero Header Card */}
                <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl relative overflow-hidden">
                    <div className="absolute top-0 right-0 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div className="absolute bottom-0 left-1/3 w-64 h-64 bg-violet-600/10 rounded-full blur-2xl pointer-events-none"></div>

                    <div className="flex flex-col md:flex-row md:items-start justify-between gap-6 relative z-10">
                        <div className="space-y-4 max-w-2xl">
                            {/* Badges */}
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="text-xs font-bold uppercase tracking-wider px-3.5 py-1 rounded-full bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">
                                    {job.department}
                                </span>
                                <span className="text-xs px-3.5 py-1 rounded-full bg-slate-800/90 text-slate-300 font-medium border border-slate-700/60">
                                    <i className="fa-solid fa-clock-rotate-left mr-1.5 text-slate-400"></i>
                                    {job.employment_type}
                                </span>
                                <span className="text-xs px-3.5 py-1 rounded-full bg-slate-800/90 text-slate-300 font-medium border border-slate-700/60">
                                    <i className="fa-solid fa-users mr-1.5 text-slate-400"></i>
                                    {job.vacancies} {job.vacancies === 1 ? 'Opening' : 'Openings'}
                                </span>
                            </div>

                            {/* Job Title & Subtitle */}
                            <div>
                                <h1 className="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                                    {job.job_title}
                                </h1>
                                <p className="text-sm sm:text-base text-slate-300 mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span>Designation: <strong className="text-white font-semibold">{job.designation}</strong></span>
                                    <span className="text-slate-600">&bull;</span>
                                    <span className="text-slate-400 text-xs">Posted: {job.published_at}</span>
                                </p>
                            </div>
                        </div>

                        {/* CTA Action Buttons */}
                        <div className="flex flex-col sm:flex-row md:flex-col items-stretch gap-3 self-stretch md:self-auto min-w-[220px]">
                            <Link
                                to={`/jobs/${job.job_code}/apply`}
                                className="px-6 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-sm shadow-xl shadow-indigo-600/30 text-center transition flex items-center justify-center gap-2 group"
                            >
                                <span>Apply for Position</span>
                                <i className="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                            </Link>

                            <button
                                type="button"
                                onClick={handleShare}
                                className="px-5 py-3 rounded-2xl border border-slate-700 hover:border-slate-600 bg-slate-800/90 hover:bg-slate-800 text-slate-200 text-xs font-semibold transition flex items-center justify-center gap-2"
                            >
                                <i className={`fa-solid ${shareCopied ? 'fa-check text-emerald-400' : 'fa-share-nodes'} text-xs`}></i>
                                <span>{shareCopied ? 'Link Copied!' : 'Share Position'}</span>
                            </button>
                        </div>
                    </div>

                    {/* Quick Attributes Metrics Banner */}
                    <div className="mt-8 pt-6 border-t border-slate-800/90 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                        <div className="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                            <p className="text-slate-400 text-[11px] uppercase tracking-wider font-semibold">Compensation</p>
                            <p className="text-base font-bold text-emerald-400 mt-1">{job.salary_formatted}</p>
                        </div>

                        <div className="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                            <p className="text-slate-400 text-[11px] uppercase tracking-wider font-semibold">Experience Level</p>
                            <p className="text-base font-bold text-slate-100 mt-1">{job.experience_formatted}</p>
                        </div>

                        <div className="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                            <p className="text-slate-400 text-[11px] uppercase tracking-wider font-semibold">Job Reference</p>
                            <p className="text-base font-mono font-bold text-indigo-400 mt-1">{job.job_code}</p>
                        </div>

                        <div className="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                            <p className="text-slate-400 text-[11px] uppercase tracking-wider font-semibold">Application Deadline</p>
                            <div className="text-base font-bold text-slate-100 mt-1 flex items-center gap-1.5">
                                <span>{job.deadline}</span>
                                {job.days_remaining !== null && (
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-bold ${job.days_remaining < 3 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'}`}>
                                        {job.days_remaining}d left
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Two-Column Content Layout */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Left Column: Responsibilities & Qualifications */}
                    <div className="lg:col-span-2 space-y-8">
                        {/* Role Overview */}
                        <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 shadow-xl">
                            <div className="flex items-center gap-3 pb-3 border-b border-slate-800/80">
                                <div className="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-sm">
                                    <i className="fa-solid fa-align-left"></i>
                                </div>
                                <div>
                                    <h2 className="text-lg font-bold text-white">Role Overview & Responsibilities</h2>
                                    <p className="text-xs text-slate-400">Detailed job description and day-to-day focus</p>
                                </div>
                            </div>
                            <div className="text-xs sm:text-sm text-slate-300 leading-relaxed whitespace-pre-line bg-slate-950/60 p-6 rounded-2xl border border-slate-800/80">
                                {job.job_description}
                            </div>
                        </div>

                        {/* Eligibility & Qualifications */}
                        {job.required_qualifications && (
                            <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 shadow-xl">
                                <div className="flex items-center gap-3 pb-3 border-b border-slate-800/80">
                                    <div className="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-sm">
                                        <i className="fa-solid fa-graduation-cap"></i>
                                    </div>
                                    <div>
                                        <h2 className="text-lg font-bold text-white">Eligibility & Qualifications</h2>
                                        <p className="text-xs text-slate-400">Academic and professional requirements</p>
                                    </div>
                                </div>
                                <div className="text-xs sm:text-sm text-slate-300 leading-relaxed whitespace-pre-line bg-slate-950/60 p-6 rounded-2xl border border-slate-800/80">
                                    {job.required_qualifications}
                                </div>
                            </div>
                        )}

                        {/* Bottom CTA Banner */}
                        <div className="bg-gradient-to-r from-indigo-950 via-slate-900 to-purple-950 border border-indigo-500/30 rounded-3xl p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-2xl relative overflow-hidden">
                            <div className="space-y-1 text-center sm:text-left">
                                <h3 className="text-xl font-extrabold text-white">Ready to take the next step?</h3>
                                <p className="text-xs sm:text-sm text-indigo-200/80">
                                    Submit your profile, resume, and experience in less than 2 minutes. No account needed.
                                </p>
                            </div>
                            <Link
                                to={`/jobs/${job.job_code}/apply`}
                                className="px-7 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs sm:text-sm shadow-xl shadow-indigo-600/30 transition flex items-center gap-2 whitespace-nowrap"
                            >
                                <span>Apply Now</span>
                                <i className="fa-solid fa-arrow-right text-xs"></i>
                            </Link>
                        </div>
                    </div>

                    {/* Right Column: Skills & Related Openings */}
                    <div className="space-y-6">
                        {/* Skills Card */}
                        <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-xl">
                            <div className="flex items-center gap-2.5">
                                <i className="fa-solid fa-wand-magic-sparkles text-indigo-400 text-sm"></i>
                                <h3 className="text-xs font-bold uppercase tracking-wider text-slate-300">
                                    Key Skills & Competencies
                                </h3>
                            </div>
                            <div className="flex flex-wrap gap-2 pt-1">
                                {Array.isArray(job.required_skills) && job.required_skills.length > 0 ? (
                                    job.required_skills.map((skill, idx) => (
                                        <span
                                            key={idx}
                                            className="px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 shadow-sm"
                                        >
                                            {skill}
                                        </span>
                                    ))
                                ) : (
                                    <span className="text-xs text-slate-500">No specific skills listed for this role</span>
                                )}
                            </div>
                        </div>

                        {/* Similar Openings */}
                        {related && related.length > 0 && (
                            <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-xl">
                                <div className="flex items-center gap-2.5">
                                    <i className="fa-solid fa-diagram-project text-indigo-400 text-sm"></i>
                                    <h3 className="text-xs font-bold uppercase tracking-wider text-slate-300">
                                        Similar Openings in {job.department}
                                    </h3>
                                </div>
                                <div className="space-y-3">
                                    {related.map((r) => (
                                        <Link
                                            key={r.job_code}
                                            to={`/jobs/${r.job_code}`}
                                            className="block p-4 rounded-2xl bg-slate-950/60 border border-slate-800 hover:border-indigo-500/40 hover:bg-slate-950 transition group"
                                        >
                                            <p className="text-xs font-bold text-white group-hover:text-indigo-400 transition">{r.job_title}</p>
                                            <div className="flex items-center justify-between text-[11px] text-slate-400 mt-2">
                                                <span>{r.employment_type}</span>
                                                <span className="text-emerald-400 font-semibold">{r.salary_formatted}</span>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Hiring Guarantee Card */}
                        <div className="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 space-y-3">
                            <div className="flex items-center gap-2 text-indigo-400 text-sm font-semibold">
                                <i className="fa-solid fa-shield-halved"></i>
                                <span>Recruiter Direct</span>
                            </div>
                            <p className="text-xs text-slate-400 leading-relaxed">
                                Applications submitted directly through this careers portal are reviewed directly by our internal hiring team within 3-5 business days.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
