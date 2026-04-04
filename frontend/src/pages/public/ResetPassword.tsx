import { useState } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { authApi } from '../../services/authApi';
import { resetPasswordSchema, type ResetPasswordFormData } from '../../schemas/auth/resetPassword.schema';

export default function ResetPassword() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  const email = searchParams.get('email') || '';
  const token = searchParams.get('token') || '';

  // Detect token format: JWT contains dots and is typically longer than 50 chars
  const isJwt = token.includes('.') && token.length > 50;

  // For 6-digit code flow, we need to verify code first
  const [codeVerified, setCodeVerified] = useState(isJwt); // JWT flow skips verification
  const [loadingCode, setLoadingCode] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<ResetPasswordFormData>({
    resolver: zodResolver(resetPasswordSchema),
    defaultValues: {
      token,
      email,
      password: '',
      password_confirmation: '',
    },
  });

  const onSubmit = async (data: ResetPasswordFormData) => {
    setError('');
    setLoading(true);

    try {
      if (isJwt) {
        // JWT flow: direct reset
        await authApi.resetPassword(email, token, data.password, data.password_confirmation);
        setSuccess('Password reset successfully! Redirecting to sign in...');
        setTimeout(() => {
          navigate('/signin');
        }, 2000);
      } else {
        // Code flow: first verify code, then reset
        if (!codeVerified) {
          setLoadingCode(true);
          await authApi.verifyCode(email, token);
          setCodeVerified(true);
          setLoadingCode(false);
          setLoading(false);
          return;
        }
        await authApi.resetPassword(email, token, data.password, data.password_confirmation);
        setSuccess('Password reset successfully! Redirecting to sign in...');
        setTimeout(() => {
          navigate('/signin');
        }, 2000);
      }
    } catch (err: unknown) {
      const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Failed to reset password';
      setError(errorMessage);
    } finally {
      setLoading(false);
      setLoadingCode(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8">
        <div>
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Reset Your Password
          </h2>
          <p className="mt-2 text-center text-sm text-gray-600">
            Enter your new password below
          </p>
        </div>

        {success && (
          <div className="bg-green-50 text-green-500 p-4 rounded-lg text-sm">
            {success}
          </div>
        )}

        {error && (
          <div className="bg-red-50 text-red-500 p-4 rounded-lg text-sm">
            {error}
          </div>
        )}

        <form className="mt-8 space-y-6" onSubmit={handleSubmit(onSubmit)}>
          {!isJwt && !codeVerified && (
            <div className="text-center text-sm text-gray-600 mb-4">
              <p>Enter the 6-digit code from your email to verify your request.</p>
            </div>
          )}

          {(!isJwt && !codeVerified) ? null : (
            <>
              <div>
                <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-1">
                  New Password
                </label>
                <input
                  id="password"
                  type="password"
                  className="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="••••••••"
                  {...register('password')}
                />
                {errors.password && (
                  <p className="mt-1 text-sm text-red-500">{errors.password.message}</p>
                )}
              </div>

              <div>
                <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700 mb-1">
                  Confirm New Password
                </label>
                <input
                  id="password_confirmation"
                  type="password"
                  className="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="••••••••"
                  {...register('password_confirmation')}
                />
                {errors.password_confirmation && (
                  <p className="mt-1 text-sm text-red-500">{errors.password_confirmation.message}</p>
                )}
              </div>
            </>
          )}

          <button
            type="submit"
            disabled={loading || loadingCode}
            className="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
          >
            {loading || loadingCode ? 'Processing...' : (isJwt || codeVerified ? 'Reset Password' : 'Verify Code')}
          </button>
        </form>
      </div>
    </div>
  );
}
