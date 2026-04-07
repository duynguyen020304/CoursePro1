import { type ReactNode } from 'react';
import { Outlet, Navigate, Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

interface AdminLayoutProps {
  children?: ReactNode;
}

export default function AdminLayout({ children }: AdminLayoutProps) {
  const { user, loading, logout, hasAnyPermission } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  const handleLogout = async () => {
    await logout();
    window.location.assign('/signin');
  };

  const navItems = [
    { to: '/admin/dashboard', label: 'Dashboard', permissions: ['dashboard.admin.view', 'dashboard.view'] },
    { to: '/admin/courses', label: 'Courses', permissions: ['courses.view.any', 'courses.view', 'courses.manage'] },
    { to: '/admin/users', label: 'Users', permissions: ['users.view', 'users.manage'] },
    { to: '/admin/roles', label: 'Roles & Permissions', permissions: ['roles.view', 'roles.manage'] },
    { to: '/admin/revenue', label: 'Revenue', permissions: ['revenue.view', 'analytics.view'] },
  ].filter((item) => hasAnyPermission(item.permissions));

  return (
    <div className="min-h-screen flex bg-gray-100">
      {/* Admin Sidebar */}
      <aside className="w-64 bg-gray-900 text-white fixed h-full overflow-y-auto">
        <div className="p-4">
          <h1 className="text-xl font-bold">Admin Panel</h1>
        </div>
        <nav className="mt-4">
          {navItems.map((item) => (
            <Link key={item.to} to={item.to} className="block px-4 py-2 hover:bg-gray-800">
              {item.label}
            </Link>
          ))}
        </nav>
        <div className="absolute bottom-0 w-full p-4 border-t border-gray-800">
          <div className="text-sm text-gray-400 mb-2">
            {user?.first_name} {user?.last_name}
          </div>
          <button
            onClick={handleLogout}
            className="w-full text-left text-red-400 hover:text-red-300"
          >
            Logout
          </button>
        </div>
      </aside>

      {/* Main content */}
      <div className="ml-64 flex-1 flex flex-col">
        <header className="bg-white shadow">
          <div className="px-4 py-4">
            <h2 className="text-lg font-semibold text-gray-800">Administration</h2>
          </div>
        </header>
        <main className="flex-1 p-6 overflow-y-auto">
          {children ?? <Outlet />}
        </main>
      </div>
    </div>
  );
}
