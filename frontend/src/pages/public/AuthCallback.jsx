import { useEffect, useRef, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { authApi } from '../../services/api';
import { useAuth } from '../../contexts/AuthContext';

export default function AuthCallback() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { refreshAuth } = useAuth();
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);
  const hasStartedRef = useRef(false);

  useEffect(() => {
    if (hasStartedRef.current) {
      return;
    }

    hasStartedRef.current = true;

    const handleCallback = async () => {
      const code = searchParams.get('code');
      const state = searchParams.get('state');
      const errorParam = searchParams.get('error');
      const handledCodeKey = code ? `oauth_code_handled:${code}` : null;

      // Handle OAuth error from Google
      if (errorParam) {
        setError('Authentication was cancelled or failed.');
        setLoading(false);
        setTimeout(() => navigate('/signin'), 2000);
        return;
      }

      // Missing code parameter
      if (!code) {
        setError('Invalid authentication response. Redirecting to login...');
        setLoading(false);
        setTimeout(() => navigate('/signin'), 2000);
        return;
      }

      if (handledCodeKey && sessionStorage.getItem(handledCodeKey) === '1') {
        setLoading(false);
        navigate('/', { replace: true });
        return;
      }

      // Validate state parameter (CSRF protection)
      const storedState = sessionStorage.getItem('oauth_state');
      if (state && storedState && state !== storedState) {
        setError('Invalid authentication state. Possible CSRF attack.');
        setLoading(false);
        setTimeout(() => navigate('/signin'), 2000);
        return;
      }

      // Clear the stored state
      sessionStorage.removeItem('oauth_state');

      try {
        const redirectUri = `${window.location.origin}/auth/callback`;
        const response = await authApi.googleLogin(code, redirectUri);

        if (response.data.success) {
          if (handledCodeKey) {
            sessionStorage.setItem(handledCodeKey, '1');
          }

          const { is_new_user } = response.data.data;
          await refreshAuth();

          navigate(is_new_user ? '/profile' : '/', { replace: true });
        } else {
          setError(response.data.message || 'Authentication failed');
          setTimeout(() => navigate('/signin'), 2000);
        }
      } catch (err) {
        const alreadyHandled = handledCodeKey && sessionStorage.getItem(handledCodeKey) === '1';

        if (alreadyHandled) {
          setLoading(false);
          navigate('/', { replace: true });
          return;
        }

        console.error('Google OAuth error:', err);
        setError(err.response?.data?.message || 'Authentication failed. Please try again.');
        setTimeout(() => navigate('/signin'), 2000);
      } finally {
        setLoading(false);
      }
    };

    handleCallback();
  }, [searchParams, navigate, refreshAuth]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="max-w-md w-full text-center p-8">
        {loading ? (
          <>
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
            <h2 className="text-xl font-semibold text-gray-900">Completing sign in...</h2>
            <p className="text-gray-600 mt-2">Please wait while we verify your account.</p>
          </>
        ) : error ? (
          <>
            <div className="text-red-500 mb-4">
              <svg className="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h2 className="text-xl font-semibold text-gray-900">Authentication Error</h2>
            <p className="text-gray-600 mt-2">{error}</p>
          </>
        ) : null}
      </div>
    </div>
  );
}
