import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';

export default function Header() {
  const { user, isAuthenticated, hasAnyPermission, logout } = useAuth();
  const { itemCount } = useCart();
  const canAccessMyCourses = hasAnyPermission(['courses.learn', 'courses.consume.own']);

  return (
    <header className="bg-white shadow-sm sticky top-0 z-50">
      <nav className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Logo */}
          <Link to="/" className="text-2xl font-bold text-indigo-600">
            CoursePro
          </Link>

          {/* Navigation */}
          <div className="hidden md:flex items-center space-x-8">
            <Link to="/courses" className="text-gray-700 hover:text-indigo-600">
              Courses
            </Link>
            <Link to="/categories" className="text-gray-700 hover:text-indigo-600">
              Categories
            </Link>
            {isAuthenticated && canAccessMyCourses && (
              <>
                <Link to="/my-courses" className="text-gray-700 hover:text-indigo-600">
                  My Courses
                </Link>
                <Link to="/cart" className="relative text-gray-700 hover:text-indigo-600">
                  Cart
                  {itemCount > 0 && (
                    <span className="absolute -top-2 -right-4 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                      {itemCount}
                    </span>
                  )}
                </Link>
              </>
            )}
          </div>

          {/* Auth buttons */}
          <div className="flex items-center space-x-4">
            {isAuthenticated ? (
              <>
                <Link
                  to="/profile"
                  className="flex items-center space-x-2 text-gray-700 hover:text-indigo-600"
                >
                  {user?.profile_image ? (
                    <img
                      src={user.profile_image}
                      alt={user.first_name}
                      className="h-8 w-8 rounded-full object-cover"
                    />
                  ) : (
                    <div className="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold">
                      {user?.first_name?.[0]}
                    </div>
                  )}
                  <span className="hidden sm:inline">
                    {user?.first_name} {user?.last_name}
                  </span>
                </Link>
                <button
                  onClick={logout}
                  className="text-gray-700 hover:text-indigo-600"
                >
                  Logout
                </button>
              </>
            ) : (
              <>
                <Link
                  to="/signin"
                  className="text-gray-700 hover:text-indigo-600"
                >
                  Sign In
                </Link>
                <Link
                  to="/signup"
                  className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700"
                >
                  Get Started
                </Link>
              </>
            )}
          </div>
        </div>
      </nav>
    </header>
  );
}
