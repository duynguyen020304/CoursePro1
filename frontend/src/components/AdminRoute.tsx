import { type ReactNode } from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

interface AdminRouteProps {
  children: ReactNode;
}

/**
 * AdminRoute - Route guard for admin-only routes
 *
 * Performs auth AND role checks BEFORE rendering AdminLayout,
 * preventing transient admin layout/sidebar exposure to non-admin users.
 *
 * @param children - The admin layout/component to render if authorized
 */
export default function AdminRoute({ children }: AdminRouteProps) {
  const { isAuthenticated, loading, hasPermission } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/signin" replace />;
  }

  if (!hasPermission('admin.access')) {
    return <Navigate to="/" replace />;
  }

  return <>{children}</>;
}
