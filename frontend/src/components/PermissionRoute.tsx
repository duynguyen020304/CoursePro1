import { type ReactNode } from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

interface PermissionRouteProps {
  children: ReactNode;
  anyOf?: string[];
  allOf?: string[];
  redirectTo?: string;
}

export default function PermissionRoute({
  children,
  anyOf = [],
  allOf = [],
  redirectTo = '/',
}: PermissionRouteProps) {
  const { loading, hasAnyPermission, hasAllPermissions } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  const passesAny = anyOf.length === 0 || hasAnyPermission(anyOf);
  const passesAll = allOf.length === 0 || hasAllPermissions(allOf);

  if (!passesAny || !passesAll) {
    return <Navigate to={redirectTo} replace />;
  }

  return <>{children}</>;
}
