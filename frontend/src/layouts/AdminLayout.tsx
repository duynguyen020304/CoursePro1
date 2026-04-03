import { type ReactNode } from 'react';
import { Outlet, Navigate, Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

interface AdminLayoutProps {
  children?: ReactNode;
}

export default function AdminLayout({ children }: AdminLayoutProps) {
  const { user, hasRole, loading, logout } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  // Redirect non-admin users
  if (!hasRole('admin')) {
    return <Navigate to="/" replace />;
  }

  const handleLogout = async () => {
    await logout();
    window.location.assign('/signin');
  };

  return (
    <div className="min-h-screen flex bg-gray-100">
      {/* Admin Sidebar */}
      <aside className="w-64 bg-gray-900 text-white fixed h-full overflow-y-auto">
        <div className="p-4">
          <h1 className="text-xl font-bold">Admin Panel</h1>
        </div>
        <nav className="mt-4">
          <Link to="/admin/dashboard" className="block px-4 py-2 hover:bg-gray-800">
            Dashboard
          </Link>
          <Link to="/admin/courses" className="block px-4 py-2 hover:bg-gray-800">
            Courses
          </Link>
          <Link to="/admin/users" className="block px-4 py-2 hover:bg-gray-800">
            Users
          </Link>
          <Link to="/admin/roles" className="block px-4 py-2 hover:bg-gray-800">
            Roles & Permissions
          </Link>
          <Link to="/admin/instructors" className="block px-4 py-2 hover:bg-gray-800">
            Instructors
          </Link>
          <Link to="/admin/orders" className="block px-4 py-2 hover:bg-gray-800">
            Orders
          </Link>
          <Link to="/admin/reviews" className="block px-4 py-2 hover:bg-gray-800">
            Reviews
          </Link>
          <Link to="/admin/revenue" className="block px-4 py-2 hover:bg-gray-800">
            Revenue
          </Link>
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
