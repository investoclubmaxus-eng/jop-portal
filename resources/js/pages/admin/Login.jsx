import React, { useState } from 'react';
import { Link, useNavigate, useLocation, Navigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import './Login.css';

export default function Login() {
    const { user, login } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();

    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [remember, setRemember] = useState(false);
    const [errors, setErrors] = useState({});
    const [generalError, setGeneralError] = useState('');
    const [loading, setLoading] = useState(false);

    // If already authenticated, redirect to admin dashboard
    if (user) {
        const from = location.state?.from?.pathname || '/admin/dashboard';
        return <Navigate to={from} replace />;
    }

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setGeneralError('');
        setLoading(true);

        try {
            await login(email, password, remember);
            const from = location.state?.from?.pathname || '/admin/dashboard';
            navigate(from, { replace: true });
        } catch (err) {
            if (err.response?.status === 422 && err.response.data?.errors) {
                setErrors(err.response.data.errors);
            } else if (err.response?.data?.message) {
                setGeneralError(err.response.data.message);
            } else {
                setGeneralError('Invalid login credentials or server error. Please try again.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="login-container">
            <div className="login-wrapper">
                {/* Header Branding */}
                <div className="brand-header">
                    <div className="lock-icon-box">
                        <i className="fa-solid fa-lock text-2xl"></i>
                    </div>
                    <h2 className="title">Admin & Recruiter Portal</h2>
                    <p className="subtitle">Sign in to manage vacancies, applicants, and team access</p>
                </div>

                {/* Main Card */}
                <div className="card">
                    {generalError && (
                        <div className="error-alert">
                            <i className="fa-solid fa-circle-exclamation text-base mt-0.5 flex-shrink-0"></i>
                            <div>
                                <p className="font-semibold">Authentication Failed</p>
                                <p>{generalError}</p>
                            </div>
                        </div>
                    )}

                    <form className="form-space" onSubmit={handleSubmit}>
                        {/* Email Field */}
                        <div>
                            <label className="input-label">
                                Email Address
                            </label>
                            <div className="input-wrapper">
                                <div className="input-icon">
                                    <i className="fa-solid fa-envelope text-xs"></i>
                                </div>
                                <input
                                    type="email"
                                    required
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                    placeholder="admin@jobportal.com"
                                    className={`text-input ${errors.email ? 'input-error' : ''}`}
                                />
                            </div>
                            {errors.email && (
                                <p className="field-error-text">{errors.email[0]}</p>
                            )}
                        </div>

                        {/* Password Field with Eye Toggle */}
                        <div>
                            <div className="password-header">
                                <label className="input-label">
                                    Password
                                </label>
                                <Link
                                    to="/admin/forgot-password"
                                    className="forgot-link"
                                >
                                    Forgot password?
                                </Link>
                            </div>
                            <div className="input-wrapper">
                                <div className="input-icon">
                                    <i className="fa-solid fa-key text-xs"></i>
                                </div>
                                <input
                                    type={showPassword ? 'text' : 'password'}
                                    required
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                    placeholder="••••••••"
                                    className={`text-input password-input-padding ${errors.password ? 'input-error' : ''}`}
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="eye-toggle-btn"
                                    aria-label="Toggle password visibility"
                                >
                                    <i className={`fa-solid ${showPassword ? 'fa-eye-slash' : 'fa-eye'} text-xs`}></i>
                                </button>
                            </div>
                            {errors.password && (
                                <p className="field-error-text">{errors.password[0]}</p>
                            )}
                        </div>

                        {/* Remember Me Checkbox */}
                        <div className="remember-row">
                            <input
                                id="remember-me"
                                type="checkbox"
                                checked={remember}
                                onChange={(e) => setRemember(e.target.checked)}
                                className="checkbox-input"
                            />
                            <label htmlFor="remember-me" className="checkbox-label">
                                Keep me signed in on this workstation
                            </label>
                        </div>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={loading}
                            className="submit-btn"
                        >
                            {loading ? (
                                <>
                                    <div className="spinner"></div>
                                    <span>Signing in...</span>
                                </>
                            ) : (
                                <>
                                    <span>Sign in to Admin Dashboard</span>
                                    <i className="fa-solid fa-arrow-right text-xs"></i>
                                </>
                            )}
                        </button>
                    </form>

                    {/* Footer Switcher */}
                    <div className="card-footer">
                        <span>Need candidate careers?</span>
                        <Link to="/jobs" className="browse-link">
                            <span>Browse Public Openings</span>
                            <i className="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}