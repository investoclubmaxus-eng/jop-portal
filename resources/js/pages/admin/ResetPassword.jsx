import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import api from '../../api/client';

export default function ResetPassword() {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();

    const [token, setToken] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});
    const [generalError, setGeneralError] = useState('');
    const [successMessage, setSuccessMessage] = useState('');

    useEffect(() => {
        const urlToken = searchParams.get('token');
        const urlEmail = searchParams.get('email');
        if (urlToken) setToken(urlToken);
        if (urlEmail) setEmail(urlEmail);
    }, [searchParams]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setGeneralError('');
        setSuccessMessage('');
        setLoading(true);

        try {
            const res = await api.post('/admin/reset-password', {
                token,
                email,
                password,
                password_confirmation: passwordConfirmation,
            });

            setSuccessMessage(res.data?.message || 'Password has been reset successfully! Redirecting to login...');
            setTimeout(() => {
                navigate('/admin/login');
            }, 2500);
        } catch (err) {
            if (err.response?.status === 422 && err.response.data?.errors) {
                setErrors(err.response.data.errors);
            } else {
                setGeneralError(err.response?.data?.message || 'Invalid or expired password reset token.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
            <div className="sm:mx-auto sm:w-full sm:max-w-md text-center">
                <div className="inline-flex w-14 h-14 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 items-center justify-center text-indigo-400 mb-4">
                    <i className="fa-solid fa-shield-halved text-2xl"></i>
                </div>
                <h2 className="text-2xl font-extrabold text-white tracking-tight">Set New Password</h2>
                <p className="mt-1 text-sm text-slate-400">Enter your reset token and your new secure password</p>
            </div>

            <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
                <div className="bg-slate-900 border border-slate-800 py-8 px-6 shadow-2xl rounded-2xl sm:px-10">
                    {successMessage && (
                        <div className="mb-5 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2.5">
                            <i className="fa-solid fa-circle-check text-base"></i>
                            <span className="font-medium">{successMessage}</span>
                        </div>
                    )}

                    {generalError && (
                        <div className="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2.5">
                            <i className="fa-solid fa-circle-exclamation text-base"></i>
                            <span>{generalError}</span>
                        </div>
                    )}

                    <form className="space-y-4" onSubmit={handleSubmit}>
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Reset Token
                            </label>
                            <input
                                type="text"
                                required
                                value={token}
                                onChange={(e) => setToken(e.target.value)}
                                placeholder="Paste reset token"
                                className="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-400 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                            />
                            {errors.token && <p className="mt-1 text-xs text-rose-400">{errors.token[0]}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Email Address
                            </label>
                            <input
                                type="email"
                                required
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                placeholder="admin@jobportal.com"
                                className="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                            />
                            {errors.email && <p className="mt-1 text-xs text-rose-400">{errors.email[0]}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                New Password
                            </label>
                            <input
                                type="password"
                                required
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                placeholder="Minimum 8 characters"
                                className="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                            />
                            {errors.password && <p className="mt-1 text-xs text-rose-400">{errors.password[0]}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Confirm New Password
                            </label>
                            <input
                                type="password"
                                required
                                value={passwordConfirmation}
                                onChange={(e) => setPasswordConfirmation(e.target.value)}
                                placeholder="Re-type new password"
                                className="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={loading}
                            className="w-full mt-2 flex justify-center items-center gap-2 py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/30 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition disabled:opacity-50"
                        >
                            {loading ? 'Updating Password...' : 'Reset Password & Sign In'}
                        </button>
                    </form>

                    <div className="mt-6 pt-5 border-t border-slate-800 text-center text-xs text-slate-400">
                        <Link to="/admin/login" className="text-indigo-400 hover:text-indigo-300">
                            &larr; Back to Admin Login
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
