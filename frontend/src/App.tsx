import { useEffect, useState, type ReactNode } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { CartProvider } from './contexts/CartContext';
import { initializeCsrf } from './services/api';

// Layouts
import PublicLayout from './layouts/PublicLayout';
import UserLayout from './layouts/UserLayout';
import AdminLayout from './layouts/AdminLayout';
import InstructorLayout from './layouts/InstructorLayout';
import AdminRoute from './components/AdminRoute';
import PermissionRoute from './components/PermissionRoute';

// Public Pages
import Home from './pages/public/Home';
import Courses from './pages/public/Courses';
import CourseDetail from './pages/public/CourseDetail';
import CategoryPage from './pages/public/CategoryPage';
import SignIn from './pages/public/SignIn';
import SignUp from './pages/public/SignUp';
import ForgotPassword from './pages/public/ForgotPassword';
import VerifyCode from './pages/public/VerifyCode';
import ResetPassword from './pages/public/ResetPassword';
import Cart from './pages/public/Cart';
import Checkout from './pages/public/Checkout';
import AuthCallback from './pages/public/AuthCallback';

// User Pages
import MyCourses from './pages/user/MyCourses';
import Profile from './pages/user/Profile';
import EditProfile from './pages/user/EditProfile';
import PurchaseHistory from './pages/user/PurchaseHistory';
import Certificates from './pages/user/Certificates';
import WatchVideo from './pages/user/WatchVideo';

// Admin Pages
import AdminDashboard from './pages/admin/Dashboard';
import CourseManagement from './pages/admin/CourseManagement';
import UserManagement from './pages/admin/UserManagement';
import RoleManagement from './pages/admin/RoleManagement';
import Revenue from './pages/admin/Revenue';
import UploadVideo from './pages/admin/UploadVideo';

// Instructor Pages
import InstructorDashboard from './pages/instructor/Dashboard';
import InstructorCourses from './pages/instructor/MyCourses';
import CreateCourse from './pages/instructor/CreateCourse';
import EditCourse from './pages/instructor/EditCourse';
import InstructorProfile from './pages/instructor/Profile';

// Styles
import './index.css';

const queryClient = new QueryClient();

// Protected Route Component
interface ProtectedRouteProps {
  children: ReactNode;
}

function ProtectedRoute({ children }: ProtectedRouteProps) {
  const { isAuthenticated, loading } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return isAuthenticated ? children : <Navigate to="/signin" replace />;
}

function AppRoutes() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Public Routes */}
        <Route element={<PublicLayout />}>
          <Route path="/" element={<Home />} />
          <Route path="/courses" element={<Courses />} />
          <Route path="/courses/:id" element={<CourseDetail />} />
          <Route path="/courses/:id/watch" element={<WatchVideo />} />
          <Route path="/signin" element={<SignIn />} />
          <Route path="/signup" element={<SignUp />} />
          <Route path="/forgot-password" element={<ForgotPassword />} />
          <Route path="/verify-code" element={<VerifyCode />} />
          <Route path="/reset-password" element={<ResetPassword />} />
          <Route path="/auth/callback" element={<AuthCallback />} />
          <Route path="/cart" element={<Cart />} />
          <Route path="/checkout" element={<Checkout />} />
          <Route path="/categories" element={<Courses />} />
          <Route path="/categories/:slug" element={<CategoryPage />} />
          <Route path="/instructors" element={<Courses />} />
          <Route path="/about" element={<Home />} />
          <Route path="/faq" element={<Home />} />
          <Route path="/contact" element={<Home />} />
          <Route path="/privacy" element={<Home />} />
          <Route path="/terms" element={<Home />} />
        </Route>

        {/* Protected User Routes */}
        <Route
          element={
            <ProtectedRoute>
              <UserLayout />
            </ProtectedRoute>
          }
        >
          <Route
            path="/my-courses"
            element={
              <PermissionRoute anyOf={['courses.learn', 'courses.consume.own']}>
                <MyCourses />
              </PermissionRoute>
            }
          />
          <Route path="/profile" element={<PermissionRoute anyOf={['profile.view.own', 'profile.view']}><Profile /></PermissionRoute>} />
          <Route path="/edit-profile" element={<PermissionRoute anyOf={['profile.edit.own', 'profile.edit']}><EditProfile /></PermissionRoute>} />
          <Route path="/purchase-history" element={<PermissionRoute anyOf={['purchase-history.view', 'orders.view.own', 'orders.view']}><PurchaseHistory /></PermissionRoute>} />
          <Route path="/certificates" element={<PermissionRoute anyOf={['certificates.view.own', 'certificates.view']}><Certificates /></PermissionRoute>} />
          <Route path="/watch/:courseId/:lessonId?" element={<PermissionRoute anyOf={['courses.consume.own', 'lessons.watch']}><WatchVideo /></PermissionRoute>} />
        </Route>

        {/* Admin Routes */}
        <Route
          element={
            <AdminRoute>
              <AdminLayout />
            </AdminRoute>
          }
        >
          <Route path="/admin/dashboard" element={<PermissionRoute anyOf={['dashboard.admin.view', 'dashboard.view']}><AdminDashboard /></PermissionRoute>} />
          <Route path="/admin/courses" element={<PermissionRoute anyOf={['courses.view.any', 'courses.view', 'courses.manage']}><CourseManagement /></PermissionRoute>} />
          <Route path="/admin/users" element={<PermissionRoute anyOf={['users.view', 'users.manage']}><UserManagement /></PermissionRoute>} />
          <Route path="/admin/roles" element={<PermissionRoute anyOf={['roles.view', 'roles.manage']}><RoleManagement /></PermissionRoute>} />
          <Route path="/admin/revenue" element={<PermissionRoute anyOf={['revenue.view', 'analytics.view']}><Revenue /></PermissionRoute>} />
          <Route path="/admin/upload-video" element={<PermissionRoute anyOf={['videos.manage.any', 'videos.manage', 'courses.manage', 'courses.edit']}><UploadVideo /></PermissionRoute>} />
        </Route>

        {/* Instructor Routes */}
        <Route
          element={
            <ProtectedRoute>
              <InstructorLayout />
            </ProtectedRoute>
          }
        >
          <Route path="/instructor/dashboard" element={<PermissionRoute anyOf={['dashboard.instructor.view', 'dashboard.view', 'instructor.dashboard.view']}><InstructorDashboard /></PermissionRoute>} />
          <Route path="/instructor/courses" element={<PermissionRoute anyOf={['instructor.courses.view', 'courses.view.own', 'courses.manage.own', 'courses.manage']}><InstructorCourses /></PermissionRoute>} />
          <Route path="/instructor/courses/create" element={<PermissionRoute anyOf={['instructor.courses.create', 'courses.create']}><CreateCourse /></PermissionRoute>} />
          <Route path="/instructor/courses/:courseId/edit" element={<PermissionRoute anyOf={['instructor.courses.edit', 'courses.manage.own', 'courses.edit.own', 'courses.manage.any', 'courses.edit.any']}><EditCourse /></PermissionRoute>} />
          <Route path="/instructor/profile" element={<PermissionRoute anyOf={['instructor.profile.view', 'instructor.profile.edit', 'profile.view.own', 'profile.edit.own']}><InstructorProfile /></PermissionRoute>} />
        </Route>

        {/* 404 */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

function App() {
  const [csrfReady, setCsrfReady] = useState(false);

  useEffect(() => {
    initializeCsrf().finally(() => {
      setCsrfReady(true);
    });
  }, []);

  if (!csrfReady) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <CartProvider>
          <AppRoutes />
        </CartProvider>
      </AuthProvider>
    </QueryClientProvider>
  );
}

export default App;
