import React, { useState, useEffect } from 'react';
import { Link, useParams, useSearchParams } from 'react-router-dom';
import api from '../../api/client';
import { showSuccess, showError, showToast } from '../../utils/alerts';

export default function DirectApply() {
    const { jobCode } = useParams();
    const [searchParams] = useSearchParams();
    // const sourceParam = searchParams.get('source') || 'direct';
    // ========== NEW SOURCE DECODING ==========
    const getSource = () => {
        const encoded = searchParams.get('ref');   // we now use "ref" instead of "source"

        if (!encoded) return 'direct';

        try {
            return atob(encoded);   // decode Base64 → "linkedin", "naukri", etc.
        } catch (e) {
            return 'direct';
        }
    };

    const sourceParam = getSource();

    const [job, setJob] = useState(null);
    const [jobLoading, setJobLoading] = useState(true);
    const [jobError, setJobError] = useState('');

    // Form fields
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    const [email, setEmail] = useState('');
    const [phone, setPhone] = useState('');
    const [location, setLocation] = useState('');
    const [qualification, setQualification] = useState('');
    const [experienceYears, setExperienceYears] = useState('');
    const [currentCtc, setCurrentCtc] = useState('');
    const [expectedCtc, setExpectedCtc] = useState('');
    const [resumeFile, setResumeFile] = useState(null);
    const [additionalFiles, setAdditionalFiles] = useState([]);

    // Submission states
    const [submitting, setSubmitting] = useState(false);
    const [errors, setErrors] = useState({});
    const [submissionSuccess, setSubmissionSuccess] = useState(null);

    useEffect(() => {
        const fetchJob = async () => {
            setJobLoading(true);
            try {
                const res = await api.get(`/jobs/${jobCode}`);
                setJob(res.data.job);
            } catch (err) {
                setJobError(err.response?.data?.message || 'Job vacancy not found or expired.');
            } finally {
                setJobLoading(false);
            }
        };

        fetchJob();
        window.scrollTo(0, 0);
    }, [jobCode]);

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                setErrors((prev) => ({ ...prev, resume: ['Resume file size cannot exceed 5MB.'] }));
                showError('File Too Large', 'Resume file size cannot exceed 5MB.');
                setResumeFile(null);
                return;
            }
            setErrors((prev) => {
                const updated = { ...prev };
                delete updated.resume;
                return updated;
            });
            setResumeFile(file);
            showToast(`Resume attached: ${file.name}`, 'info');
        }
    };

    const handleAdditionalFilesChange = (e) => {
        const files = Array.from(e.target.files);
        const oversized = files.filter((f) => f.size > 5 * 1024 * 1024);
        if (oversized.length > 0) {
            showError('File Too Large', 'Each additional document file size cannot exceed 5MB.');
            return;
        }
        setAdditionalFiles((prev) => [...prev, ...files].slice(0, 5));
        showToast(`${files.length} document(s) attached`, 'info');
    };

    const removeAdditionalFile = (indexToRemove) => {
        setAdditionalFiles((prev) => prev.filter((_, idx) => idx !== indexToRemove));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});

        if (!resumeFile) {
            setErrors({ resume: ['Please upload your resume in PDF, DOC, or DOCX format.'] });
            showError('Resume Required', 'Please attach your resume or CV before submitting.');
            return;
        }

        setSubmitting(true);

        try {
            const formData = new FormData();
            formData.append('first_name', firstName);
            formData.append('last_name', lastName);
            formData.append('email', email);
            formData.append('phone', phone);
            if (location) formData.append('current_location', location);
            if (qualification) formData.append('highest_qualification', qualification);
            if (experienceYears) formData.append('total_experience_years', experienceYears);
            if (currentCtc) formData.append('current_ctc', currentCtc);
            if (expectedCtc) formData.append('expected_ctc', expectedCtc);
            formData.append('source', sourceParam);
            formData.append('resume', resumeFile);

            if (additionalFiles && additionalFiles.length > 0) {
                additionalFiles.forEach((file) => {
                    formData.append('additional_documents[]', file);
                });
            }

            const res = await api.post(`/jobs/${jobCode}/apply`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            await showSuccess(
                'Application Submitted!',
                `Your application code is ${res.data.application_code}. We have received your profile for ${res.data.job_title}.`
            );

            setSubmissionSuccess(res.data);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (err) {
            if (err.response?.status === 422 && err.response.data?.errors) {
                setErrors(err.response.data.errors);
                const firstError = Object.values(err.response.data.errors)[0]?.[0];
                showError('Application Error', firstError || 'Please correct the highlighted errors.');
            } else {
                showError('Application Failed', err.response?.data?.message || 'Unable to submit your application. Please check your details and try again.');
            }
        } finally {
            setSubmitting(false);
        }
    };

    const getCampaignHeader = () => {
        const s = sourceParam.toLowerCase();
        if (s.includes('linkedin')) {
            return {
                title: 'Applying via LinkedIn Promotion',
                icon: 'fa-brands fa-linkedin',
                color: 'bg-[#0A66C2]/20 text-[#70B5F9] border-[#0A66C2]/40',
                note: 'Your application will be flagged as sourced from our LinkedIn campaign',
            };
        }
        if (s.includes('naukri')) {
            return {
                title: 'Applying via Naukri.com',
                icon: 'fa-solid fa-briefcase',
                color: 'bg-sky-500/20 text-sky-300 border-sky-500/40',
                note: 'Your profile will be attributed to our Naukri recruitment listing',
            };
        }
        if (s.includes('indeed')) {
            return {
                title: 'Applying via Indeed',
                icon: 'fa-solid fa-i',
                color: 'bg-blue-500/20 text-blue-300 border-blue-500/40',
                note: 'Your application is attributed to our Indeed campaign',
            };
        }
        if (s.includes('whatsapp')) {
            return {
                title: 'Applying via WhatsApp Referral',
                icon: 'fa-brands fa-whatsapp',
                color: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
                note: 'Your application was received via our direct messaging referral',
            };
        }
        if (sourceParam !== 'direct') {
            return {
                title: `Applying via Campaign: ${sourceParam}`,
                icon: 'fa-solid fa-bullhorn',
                color: 'bg-indigo-500/20 text-indigo-300 border-indigo-500/40',
                note: `Tracking Campaign Source: "${sourceParam}"`,
            };
        }
        return {
            title: 'Direct Application',
            icon: 'fa-solid fa-paper-plane',
            color: 'bg-slate-800 text-slate-300 border-slate-700',
            note: 'Official careers portal direct submission',
        };
    };

    const campaign = getCampaignHeader();

    if (jobLoading) {
        return (
            <div className="w-full min-h-[70vh] bg-slate-950 flex flex-col items-center justify-center text-slate-400 py-24">
                <div className="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p className="text-sm font-medium text-slate-300">Preparing application portal...</p>
                <p className="text-xs text-slate-500 mt-1">Fetching position {jobCode}</p>
            </div>
        );
    }

    if (jobError || !job) {
        return (
            <div className="w-full min-h-[70vh] bg-slate-950 py-16 px-4 flex items-center justify-center">
                <div className="max-w-xl w-full p-8 sm:p-10 rounded-3xl bg-slate-900 border border-slate-800 text-center shadow-2xl">
                    <div className="w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i className="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <h2 className="text-xl font-bold text-white">Application Closed</h2>
                    <p className="text-xs sm:text-sm text-slate-400 mt-2 mb-6 leading-relaxed">
                        {jobError || 'This vacancy is no longer accepting applications or has expired.'}
                    </p>
                    <Link
                        to="/jobs"
                        className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition"
                    >
                        <i className="fa-solid fa-arrow-left"></i>
                        <span>View Available Openings</span>
                    </Link>
                </div>
            </div>
        );
    }

    // Success Screen
    if (submissionSuccess) {
        return (
            <div className="w-full bg-slate-950 min-h-[calc(100vh-140px)] text-slate-100 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
                <div className="max-w-2xl w-full">
                    <div className="bg-slate-900 border border-emerald-500/40 rounded-3xl p-8 sm:p-12 shadow-2xl text-center space-y-6 relative overflow-hidden">
                        <div className="w-20 h-20 rounded-3xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-3xl mx-auto shadow-lg shadow-emerald-500/20">
                            <i className="fa-solid fa-check"></i>
                        </div>

                        <div>
                            <span className="text-xs font-bold uppercase tracking-wider px-3.5 py-1 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                Application Submitted Successfully
                            </span>
                            <h1 className="text-2xl sm:text-4xl font-extrabold text-white tracking-tight mt-4">
                                Thank You, {submissionSuccess.candidate_name}!
                            </h1>
                            <p className="text-xs sm:text-sm text-slate-300 mt-2 max-w-md mx-auto leading-relaxed">
                                Your application for <strong className="text-white font-semibold">{submissionSuccess.job_title}</strong> has been received by our recruitment team.
                            </p>
                        </div>

                        {/* Tracking Code Box */}
                        <div className="p-6 rounded-2xl bg-slate-950/80 border border-slate-800 max-w-md mx-auto text-center space-y-2">
                            <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                Application Reference Code
                            </p>
                            <p className="font-mono text-2xl sm:text-3xl font-extrabold text-indigo-400 select-all tracking-wide">
                                {submissionSuccess.application_code}
                            </p>
                            <p className="text-xs text-slate-400 pt-1">
                                Please save this code for future inquiries and communication with our talent team.
                            </p>
                            <div className="pt-3 border-t border-slate-800/80 text-[11px] text-slate-400 flex items-center justify-center gap-2">
                                <span>Acquisition Channel:</span>
                                <span className="text-slate-200 font-semibold px-2 py-0.5 rounded bg-slate-800 border border-slate-700">
                                    {submissionSuccess.source}
                                </span>
                            </div>
                        </div>

                        <div className="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                            <Link
                                to="/jobs"
                                className="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2"
                            >
                                <i className="fa-solid fa-briefcase"></i>
                                <span>Explore More Careers</span>
                            </Link>
                            <button
                                type="button"
                                onClick={() => window.print()}
                                className="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition flex items-center justify-center gap-2"
                            >
                                <i className="fa-solid fa-print"></i>
                                <span>Print Confirmation</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="w-full bg-slate-950 min-h-[calc(100vh-140px)] text-slate-100 py-8 px-4 sm:px-6 lg:px-8">
            <div className="max-w-4xl mx-auto space-y-8">
                {/* Breadcrumb Navigation */}
                <div className="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                    <Link to="/jobs" className="hover:text-indigo-400 transition font-medium">Careers</Link>
                    <span>/</span>
                    <Link to={`/jobs/${job.job_code}`} className="hover:text-indigo-400 transition font-medium">{job.job_title}</Link>
                    <span>/</span>
                    <span className="text-indigo-400 font-semibold">Submit Application</span>
                </div>

                {/* Campaign Header Banner */}
                <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                        <div className="space-y-2">
                            <div className="flex flex-wrap items-center gap-2">
                                <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border ${campaign.color}`}>
                                    <i className={campaign.icon}></i>
                                    <span>{campaign.title}</span>
                                </span>
                                <span className="text-xs text-slate-400 font-mono px-2.5 py-0.5 rounded-lg bg-slate-800 border border-slate-700/60">
                                    Ref: {job.job_code}
                                </span>
                            </div>

                            <h1 className="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                                Apply for {job.job_title}
                            </h1>
                            <p className="text-xs sm:text-sm text-slate-300 flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span>{job.department}</span>
                                <span className="text-slate-600">&bull;</span>
                                <span>{job.designation}</span>
                                <span className="text-slate-600">&bull;</span>
                                <span>{job.employment_type}</span>
                                <span className="text-slate-600">&bull;</span>
                                <span className="text-emerald-400 font-semibold">{job.salary_formatted}</span>
                            </p>
                        </div>

                        <div className="sm:text-right text-xs p-3 sm:p-0 rounded-xl bg-slate-950/60 sm:bg-transparent border sm:border-0 border-slate-800">
                            <p className="text-[11px] uppercase tracking-wider font-semibold text-slate-400">Application Deadline</p>
                            <p className="text-sm font-bold text-slate-100 mt-0.5">{job.deadline}</p>
                            {job.days_remaining !== null && (
                                <p className={`text-xs font-semibold mt-0.5 ${job.days_remaining < 3 ? 'text-rose-400' : 'text-emerald-400'}`}>
                                    {job.days_remaining} day{job.days_remaining === 1 ? '' : 's'} remaining
                                </p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Application Form Card */}
                <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        {/* Section 1: Personal Details */}
                        <div className="space-y-4">
                            <div className="flex items-center gap-2.5 pb-3 border-b border-slate-800">
                                <div className="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xs font-bold border border-indigo-500/20">
                                    1
                                </div>
                                <h2 className="text-sm font-bold uppercase tracking-wider text-white">
                                    Personal & Contact Details
                                </h2>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                                        First Name <span className="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={firstName}
                                        onChange={(e) => setFirstName(e.target.value)}
                                        placeholder="Jane"
                                        className={`w-full px-4 py-3 bg-slate-950 border ${errors.first_name ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-800'} rounded-xl text-white text-xs sm:text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition`}
                                    />
                                    {errors.first_name && <p className="mt-1 text-xs text-rose-400">{errors.first_name[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Last Name <span className="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={lastName}
                                        onChange={(e) => setLastName(e.target.value)}
                                        placeholder="Doe"
                                        className={`w-full px-4 py-3 bg-slate-950 border ${errors.last_name ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-800'} rounded-xl text-white text-xs sm:text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition`}
                                    />
                                    {errors.last_name && <p className="mt-1 text-xs text-rose-400">{errors.last_name[0]}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Email Address <span className="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        required
                                        value={email}
                                        onChange={(e) => setEmail(e.target.value)}
                                        placeholder="jane.doe@example.com"
                                        className={`w-full px-4 py-3 bg-slate-950 border ${errors.email ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-800'} rounded-xl text-white text-xs sm:text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition`}
                                    />
                                    {errors.email && <p className="mt-1 text-xs text-rose-400">{errors.email[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Phone Number <span className="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="tel"
                                        required
                                        value={phone}
                                        onChange={(e) => setPhone(e.target.value)}
                                        placeholder="+1 555 123 4567"
                                        className={`w-full px-4 py-3 bg-slate-950 border ${errors.phone ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-800'} rounded-xl text-white text-xs sm:text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition`}
                                    />
                                    {errors.phone && <p className="mt-1 text-xs text-rose-400">{errors.phone[0]}</p>}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Current City / Location
                                </label>
                                <input
                                    type="text"
                                    value={location}
                                    onChange={(e) => setLocation(e.target.value)}
                                    placeholder="e.g. San Francisco, CA / London, UK / Remote"
                                    className="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs sm:text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                                />
                            </div>
                        </div>

                        {/* Section 2: Professional Background */}
                        <div className="space-y-4 pt-4">
                            <div className="flex items-center gap-2.5 pb-3 border-b border-slate-800">
                                <div className="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xs font-bold border border-indigo-500/20">
                                    2
                                </div>
                                <h2 className="text-sm font-bold uppercase tracking-wider text-white">
                                    Professional Background
                                </h2>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Total Relevant Experience (Years)
                                    </label>
                                    <input
                                        type="number"
                                        step="0.5"
                                        min="0"
                                        value={experienceYears}
                                        onChange={(e) => setExperienceYears(e.target.value)}
                                        placeholder="e.g. 3.5"
                                        className="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs sm:text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                                    />
                                    {errors.total_experience_years && <p className="mt-1 text-xs text-rose-400">{errors.total_experience_years[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Highest Education / Qualification
                                    </label>
                                    <input
                                        type="text"
                                        value={qualification}
                                        onChange={(e) => setQualification(e.target.value)}
                                        placeholder="e.g. B.Tech Computer Science, MS, Diploma"
                                        className="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs sm:text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Current Annual CTC / Compensation (USD)
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        value={currentCtc}
                                        onChange={(e) => setCurrentCtc(e.target.value)}
                                        placeholder="e.g. 75000"
                                        className="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs sm:text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Expected Annual CTC (USD)
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        value={expectedCtc}
                                        onChange={(e) => setExpectedCtc(e.target.value)}
                                        placeholder="e.g. 95000"
                                        className="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs sm:text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Section 3: Resume Upload (Module 6) */}
                        <div className="space-y-4 pt-4">
                            <div className="flex items-center gap-2.5 pb-3 border-b border-slate-800">
                                <div className="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xs font-bold border border-indigo-500/20">
                                    3
                                </div>
                                <h2 className="text-sm font-bold uppercase tracking-wider text-white">
                                    Upload Resume / CV <span className="text-rose-400">*</span>
                                </h2>
                            </div>

                            <div className={`border-2 border-dashed ${errors.resume ? 'border-rose-500 bg-rose-500/5' : resumeFile ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-slate-800 hover:border-indigo-500/50 bg-slate-950/60'} rounded-2xl p-8 text-center transition`}>
                                <div className="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center mx-auto mb-3 text-2xl">
                                    <i className={`fa-solid ${resumeFile ? 'fa-file-circle-check text-emerald-400' : 'fa-cloud-arrow-up'}`}></i>
                                </div>

                                {resumeFile ? (
                                    <div className="space-y-1">
                                        <p className="text-sm font-bold text-white">{resumeFile.name}</p>
                                        <p className="text-xs text-emerald-400 font-medium">
                                            {(resumeFile.size / 1024).toFixed(1)} KB &bull; File attached ready for upload
                                        </p>
                                    </div>
                                ) : (
                                    <div className="space-y-1">
                                        <p className="text-sm font-bold text-white">Choose your CV or drag and drop file here</p>
                                        <p className="text-xs text-slate-400">Supported formats: PDF, DOC, DOCX (Max 5MB)</p>
                                    </div>
                                )}

                                <div className="mt-4">
                                    <label className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-indigo-300 text-xs font-semibold cursor-pointer transition border border-slate-700 shadow-md">
                                        <i className="fa-solid fa-folder-open text-xs"></i>
                                        <span>{resumeFile ? 'Change Resume File' : 'Browse Files on Computer'}</span>
                                        <input
                                            type="file"
                                            accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                            onChange={handleFileChange}
                                            className="hidden"
                                        />
                                    </label>
                                </div>
                            </div>
                            {errors.resume && <p className="text-xs text-rose-400">{errors.resume[0]}</p>}
                        </div>

                        {/* Section 4: Additional Documents */}
                        <div className="space-y-4 pt-4">
                            <div className="flex items-center gap-2.5 pb-3 border-b border-slate-800">
                                <div className="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xs font-bold border border-indigo-500/20">
                                    4
                                </div>
                                <h2 className="text-sm font-bold uppercase tracking-wider text-white">
                                    Additional Documents <span className="text-xs font-normal text-slate-400 lowercase">(optional)</span>
                                </h2>
                            </div>

                            <div className="border border-slate-800 rounded-2xl p-6 bg-slate-950/60 space-y-3">
                                <div>
                                    <p className="text-xs font-semibold text-slate-200">Certifications, Portfolio Files, or Reference Letters</p>
                                    <p className="text-xs text-slate-400 mt-0.5">Attach up to 5 additional documents (PDF, DOC, DOCX, JPG, PNG &bull; max 5MB each)</p>
                                </div>

                                <div>
                                    <label className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold cursor-pointer transition border border-slate-700">
                                        <i className="fa-solid fa-paperclip text-xs"></i>
                                        <span>{additionalFiles.length > 0 ? `${additionalFiles.length} file(s) attached` : 'Attach Additional Files'}</span>
                                        <input
                                            type="file"
                                            multiple
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                            onChange={handleAdditionalFilesChange}
                                            className="hidden"
                                        />
                                    </label>
                                </div>

                                {additionalFiles.length > 0 && (
                                    <div className="space-y-2 pt-2">
                                        {additionalFiles.map((file, idx) => (
                                            <div key={idx} className="flex items-center justify-between p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs">
                                                <div className="flex items-center gap-2 truncate">
                                                    <i className="fa-solid fa-file text-slate-400 text-xs"></i>
                                                    <span className="text-slate-200 truncate">{file.name}</span>
                                                    <span className="text-slate-500 text-[11px]">({(file.size / 1024).toFixed(0)} KB)</span>
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={() => removeAdditionalFile(idx)}
                                                    className="text-slate-400 hover:text-rose-400 p-1 transition"
                                                    title="Remove attachment"
                                                >
                                                    <i className="fa-solid fa-xmark"></i>
                                                </button>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Submit Button */}
                        <div className="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p className="text-xs text-slate-400 text-center sm:text-left">
                                By submitting, you agree to our recruitment review process and confirm the details submitted are accurate.
                            </p>

                            <button
                                type="submit"
                                disabled={submitting}
                                className="w-full sm:w-auto px-10 py-4 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-sm shadow-xl shadow-indigo-600/30 transition disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer"
                            >
                                {submitting ? (
                                    <>
                                        <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                        <span>Submitting Profile...</span>
                                    </>
                                ) : (
                                    <>
                                        <span>Submit Application</span>
                                        <i className="fa-solid fa-paper-plane text-xs"></i>
                                    </>
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
