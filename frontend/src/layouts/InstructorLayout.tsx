import { Outlet, Navigate, Link, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import type { ReactNode } from 'react';

export default function InstructorLayout() {
  const { user, hasRole, loading, logout } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  // Redirect non-instructor users
  if (!hasRole('instructor') && !hasRole('admin')) {
    return <Navigate to="/" replace />;
  }

  const handleLogout = async () => {
    await logout();
    window.location.assign('/signin');
  };

  const isActive = (path: string) => location.pathname === path;

  const navItems = [
    { path: '/instructor/dashboard', label: 'Dashboard', icon: '📊' },
    { path: '/instructor/courses', label: 'My Courses', icon: '📚' },
    { path: '/instructor/courses/create', label: 'Create Course', icon: '➕' },
    { path: '/instructor/profile', label: 'Profile', icon: '👤' },
  ];

  return (
    <div className="min-h-screen flex bg-gray-100">
      {/* Instructor Sidebar */}
      <aside className="w-64 bg-indigo-900 text-white fixed h-full overflow-y-auto">
        <div className="p-4 border-b border-indigo-800">
          <Link to="/" className="text-xl font-bold hover:text-indigo-300">
            CoursePro
          </Link>
          <p className="text-xs text-indigo-300 mt-1">Instructor Portal</p>
        </div>

        <nav className="mt-4">
          {navItems.map((item) => (
            <Link
              key={item.path}
              to={item.path}
              className={`flex items-center px-4 py-3 hover:bg-indigo-800 transition-colors ${
                isActive(item.path) ? 'bg-indigo-800 border-r-4 border-white' : ''
              }`}
            >
              <span className="mr-3">{item.icon}</span>
              {item.label}
            </Link>
          ))}
        </nav>

        <div className="absolute bottom-0 w-full p-4 border-t border-indigo-800 bg-indigo-900">
          <div className="flex items-center mb-3">
            <div className="w-10 h-10 bg-indigo-700 rounded-full flex items-center justify-center">
              {user?.profile_image ? (
                <img
                  src={user.profile_image}
                  alt="Profile"
                  className="w-10 h-10 rounded-full object-cover"
                />
              ) : (
                <span className="text-sm font-medium">
                  {user?.first_name?.[0]}{user?.last_name?.[0]}
                </span>
              )}
            </div>
            <div className="ml-3">
              <p className="text-sm font-medium">
                {user?.first_name} {user?.last_name}
              </p>
              <p className="text-xs text-indigo-300">Instructor</p>
            </div>
          </div>
          <button
            onClick={handleLogout}
            className="w-full text-left text-red-400 hover:text-red-300 text-sm"
          >
            Sign Out
          </button>
        </div>
      </aside>

      {/* Main content */}
      <div className="ml-64 flex-1 flex flex-col">
        <header className="bg-white shadow-sm">
          <div className="px-6 py-4">
            <h2 className="text-lg font-semibold text-gray-800">
              {navItems.find(item => isActive(item.path))?.label || 'Instructor Dashboard'}
            </h2>
          </div>
        </header>
        <main className="flex-1 p-6 overflow-y-auto">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
