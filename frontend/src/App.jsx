import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { CartProvider } from './contexts/CartContext';

// Layouts
import PublicLayout from './layouts/PublicLayout';
import UserLayout from './layouts/UserLayout';
import AdminLayout from './layouts/AdminLayout';

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
import Revenue from './pages/admin/Revenue';
import UploadVideo from './pages/admin/UploadVideo';

// Styles
import './index.css';

const queryClient = new QueryClient();

// Protected Route Component
function ProtectedRoute({ children }) {
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
          <Route path="/cart" element={<Cart />} />
          <Route path="/checkout" element={<Checkout />} />
          <Route path="/categories" element={<Courses />} />
          <Route path="/categories/:id" element={<CategoryPage />} />
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
          <Route path="/my-courses" element={<MyCourses />} />
          <Route path="/profile" element={<Profile />} />
          <Route path="/edit-profile" element={<EditProfile />} />
          <Route path="/purchase-history" element={<PurchaseHistory />} />
          <Route path="/certificates" element={<Certificates />} />
          <Route path="/watch/:courseId/:lessonId?" element={<WatchVideo />} />
        </Route>

        {/* Admin Routes */}
        <Route
          element={
            <ProtectedRoute>
              <AdminLayout />
            </ProtectedRoute>
          }
        >
          <Route path="/admin/dashboard" element={<AdminDashboard />} />
          <Route path="/admin/courses" element={<CourseManagement />} />
          <Route path="/admin/users" element={<UserManagement />} />
          <Route path="/admin/instructors" element={<div>Instructors Management</div>} />
          <Route path="/admin/orders" element={<div>Orders Management</div>} />
          <Route path="/admin/reviews" element={<div>Reviews Management</div>} />
          <Route path="/admin/revenue" element={<Revenue />} />
          <Route path="/admin/upload-video" element={<UploadVideo />} />
        </Route>

        {/* 404 */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

function App() {
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
