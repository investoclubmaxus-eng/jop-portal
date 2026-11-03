import React, { useState, useEffect } from 'react';
import api from '../../api/client';
import { useAuth } from '../../context/AuthContext';

export default function Profile() {
    const { user, refreshUser } = useAuth();

    // Profile form state
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [phone, setPhone] = useState('');
    const [profileLoading, setProfileLoading] = useState(false);
    const [profileSuccess, setProfileSuccess] = useState('');
    const [profileErrors, setProfileErrors] = useState({});

    // Password form state
    const [currentPassword, setCurrentPassword] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [passwordLoading, setPasswordLoading] = useState(false);
    const [passwordSuccess, setPasswordSuccess] = useState('');
    const [passwordErrors, setPasswordErrors] = useState({});

    useEffect(() => {
        if (user) {
            setName(user.name || '');
            setEmail(user.email || '');
            setPhone(user.phone || '');
        }
    }, [user]);

    const handleProfileSubmit = async (e) => {
        e.preventDefault();
        setProfileLoading(true);
        setProfileSuccess('');
        setProfileErrors({});

        try {
            const res = await api.put('/admin/profile', { name, email, phone });
            setProfileSuccess(res.data?.message || 'Profile updated successfully.');
            await refreshUser();
        } catch (err) {
            if (err.response?.status === 422 && err.response.data?.errors) {
                setProfileErrors(err.response.data.errors);
            } else {
                setProfileErrors({ general: [err.response?.data?.message || 'Failed to update profile.'] });
            }
        } finally {
            setProfileLoading(false);
        }
    };

    const handlePasswordSubmit = async (e) => {
        e.preventDefault();
        setPasswordLoading(true);
        setPasswordSuccess('');
        setPasswordErrors({});

        try {
            const res = await api.put('/admin/profile/password', {
                current_password: currentPassword,
                password,
                password_confirmation: passwordConfirmation,
            });
            setPasswordSuccess(res.data?.message || 'Password changed successfully.');
            setCurrentPassword('');
            setPassword('');
            setPasswordConfirmation('');
        } catch (err) {
            if (err.response?.status === 422 && err.response.data?.errors) {
                setPasswordErrors(err.response.data.errors);
            } else {
                setPasswordErrors({ general: [err.response?.data?.message || 'Failed to change password.'] });
            }
        } finally {
            setPasswordLoading(false);
        }
    };

    return (
        <div className="max-w-5xl mx-auto space-y-6">
            {/* Header info */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div className="flex items-center gap-4">
                    <div className="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 flex items-center justify-center text-white text-2xl font-bold shadow-xl shadow-indigo-600/30 uppercase">
                        {user?.name?.charAt(0) || 'A'}
                    </div>
                    <div>
                        <div className="flex items-center gap-2.5">
                            <h1 className="text-xl font-bold text-white">{user?.name}</h1>
                            <span className="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 uppercase">
                                {user?.role?.replace('_', ' ')}
                            </span>
                        </div>
                        <p className="text-sm text-slate-400 mt-0.5">{user?.email}</p>
                        <div className="flex items-center gap-4 mt-2 text-xs text-slate-400">
                            <span>
                                <i className="fa-solid fa-clock mr-1.5 text-slate-400"></i>
                                Last Login: {user?.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'First session'}
                            </span>
                            {user?.last_login_ip && (
                                <span>
                                    <i className="fa-solid fa-network-wired mr-1.5 text-slate-400"></i>
                                    IP: {user.last_login_ip}
                                </span>
                            )}
                        </div>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <span className="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <span className="text-xs font-semibold text-emerald-400 uppercase tracking-wide">Account Active</span>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Profile Information Form */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div className="flex items-center gap-2.5 mb-5 pb-4 border-b border-slate-800">
                        <i className="fa-solid fa-user-pen text-indigo-400"></i>
                        <h2 className="text-base font-semibold text-white">Personal Information</h2>
                    </div>

                    {profileSuccess && (
                        <div className="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
                            <i className="fa-solid fa-circle-check"></i>
                            <span>{profileSuccess}</span>
                        </div>
                    )}
                    {profileErrors.general && (
                        <div className="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2">
                            <i className="fa-solid fa-circle-exclamation"></i>
                            <span>{profileErrors.general[0]}</span>
                        </div>
                    )}

                    <form onSubmit={handleProfileSubmit} className="space-y-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Full Name
                            </label>
                            <input
                                type="text"
                                required
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {profileErrors.name && <p className="mt-1 text-xs text-rose-400">{profileErrors.name[0]}</p>}
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
                                className="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {profileErrors.email && <p className="mt-1 text-xs text-rose-400">{profileErrors.email[0]}</p>}
                        </div>

                        {/* <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Phone Number (Optional)
                            </label>
                            <input
                                type="text"
                                value={phone}
                                onChange={(e) => {
                                    // Remove non-numeric characters and slice to a max of 10 digits
                                    const value = e.target.value.replace(/\D/g, '').slice(0, 10);
                                    setPhone(value);
                                }}
                                placeholder="+1 234 567 8900"
                                className="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {profileErrors.phone && <p className="mt-1 text-xs text-rose-400">{profileErrors.phone[0]}</p>}
                        </div> */}

                        <div className="pt-2">
                            <button
                                type="submit"
                                disabled={profileLoading}
                                className="w-full py-2.5 px-4 rounded-xl font-semibold text-xs text-white bg-indigo-600 hover:bg-indigo-500 transition shadow-md shadow-indigo-600/30 disabled:opacity-50"
                            >
                                {profileLoading ? 'Saving Changes...' : 'Save Profile Details'}
                            </button>
                        </div>
                    </form>
                </div>

                {/* Change Password Form */}
                <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div className="flex items-center gap-2.5 mb-5 pb-4 border-b border-slate-800">
                        <i className="fa-solid fa-lock text-indigo-400"></i>
                        <h2 className="text-base font-semibold text-white">Change Password</h2>
                    </div>

                    {passwordSuccess && (
                        <div className="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
                            <i className="fa-solid fa-circle-check"></i>
                            <span>{passwordSuccess}</span>
                        </div>
                    )}
                    {passwordErrors.general && (
                        <div className="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2">
                            <i className="fa-solid fa-circle-exclamation"></i>
                            <span>{passwordErrors.general[0]}</span>
                        </div>
                    )}

                    <form onSubmit={handlePasswordSubmit} className="space-y-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                Current Password
                            </label>
                            <input
                                type="password"
                                required
                                value={currentPassword}
                                onChange={(e) => setCurrentPassword(e.target.value)}
                                placeholder="••••••••"
                                className="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {passwordErrors.current_password && (
                                <p className="mt-1 text-xs text-rose-400">{passwordErrors.current_password[0]}</p>
                            )}
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
                                className="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {passwordErrors.password && (
                                <p className="mt-1 text-xs text-rose-400">{passwordErrors.password[0]}</p>
                            )}
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
                                className="w-full px-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div className="pt-2">
                            <button
                                type="submit"
                                disabled={passwordLoading}
                                className="w-full py-2.5 px-4 rounded-xl font-semibold text-xs text-white bg-indigo-600 hover:bg-indigo-500 transition shadow-md shadow-indigo-600/30 disabled:opacity-50"
                            >
                                {passwordLoading ? 'Updating Password...' : 'Update Password'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
