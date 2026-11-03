import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/client';

export default function ForgotPassword() {
    const [email, setEmail] = useState('');
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');
    const [resetToken, setResetToken] = useState('');
    const [error, setError] = useState('');

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setMessage('');
        setError('');

        try {
            const res = await api.post('/admin/forgot-password', { email });
            setMessage(res.data?.message || 'Password reset instructions have been generated.');
            if (res.data?.token) {
                setResetToken(res.data.token);
            }
        } catch (err) {
            setError(err.response?.data?.message || err.response?.data?.errors?.email?.[0] || 'Unable to process reset request.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
            <div className="sm:mx-auto sm:w-full sm:max-w-md text-center">
                <div className="inline-flex w-14 h-14 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 items-center justify-center text-indigo-400 mb-4">
                    <i className="fa-solid fa-key text-2xl"></i>
                </div>
                <h2 className="text-2xl font-extrabold text-white tracking-tight">Forgot Password</h2>
                <p className="mt-1 text-sm text-slate-400">Enter your registered admin email address to receive reset instructions</p>
            </div>

            <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
                <div className="bg-slate-900 border border-slate-800 py-8 px-6 shadow-2xl rounded-2xl sm:px-10">
                    {message && (
                        <div className="mb-5 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs">
                            <p className="font-semibold mb-1 flex items-center gap-2">
                                <i className="fa-solid fa-circle-check"></i>
                                {message}
                            </p>
                            {resetToken && (
                                <div className="mt-3 p-3 bg-slate-950/70 rounded-lg border border-emerald-500/20">
                                    <p className="text-[11px] text-slate-400 mb-1">Local / Sandbox Dev Token:</p>
                                    <code className="text-emerald-300 break-all text-xs font-mono select-all block bg-slate-900 p-1.5 rounded">
                                        {resetToken}
                                    </code>
                                    <Link
                                        to={`/admin/reset-password?token=${resetToken}&email=${encodeURIComponent(email)}`}
                                        className="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300"
                                    >
                                        <span>Proceed to Reset Password with this Token</span>
                                        <i className="fa-solid fa-arrow-right text-[10px]"></i>
                                    </Link>
                                </div>
                            )}
                        </div>
                    )}

                    {error && (
                        <div className="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2">
                            <i className="fa-solid fa-circle-exclamation"></i>
                            <span>{error}</span>
                        </div>
                    )}

                    <form className="space-y-5" onSubmit={handleSubmit}>
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                                Registered Email Address
                            </label>
                            <input
                                type="email"
                                required
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                placeholder="admin@jobportal.com"
                                className="w-full px-4 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={loading}
                            className="w-full flex justify-center items-center gap-2 py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/30 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition disabled:opacity-50"
                        >
                            {loading ? 'Sending request...' : 'Send Password Reset Link'}
                        </button>
                    </form>

                    <div className="mt-6 pt-5 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <Link to="/admin/login" className="text-indigo-400 hover:text-indigo-300 flex items-center gap-1.5">
                            <i className="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Back to Sign In</span>
                        </Link>
                        <Link to="/admin/reset-password" className="text-slate-400 hover:text-slate-300">
                            Have a reset token?
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
