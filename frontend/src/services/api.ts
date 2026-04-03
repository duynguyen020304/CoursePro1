import axios, { type AxiosInstance, type AxiosRequestConfig, type InternalAxiosRequestConfig, type AxiosResponse, type AxiosError } from 'axios';

// Environment configuration
const RAW_API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
const API_BASE_URL = RAW_API_BASE_URL;
const API_ORIGIN = RAW_API_BASE_URL.startsWith('http://') || RAW_API_BASE_URL.startsWith('https://')
  ? new URL(RAW_API_BASE_URL).origin
  : (import.meta.env.VITE_BACKEND_ORIGIN || 'http://localhost:8000');
const CSRF_COOKIE_URL = `${API_ORIGIN}/sanctum/csrf-cookie`;

// Public paths that don't require authentication redirect
const PUBLIC_REDIRECT_PREFIXES = [
  '/',
  '/signin',
  '/signup',
  '/forgot-password',
  '/verify-code',
  '/reset-password',
  '/courses',
  '/categories',
  '/auth/callback',
  '/cart',
  '/checkout',
  '/instructors',
  '/about',
  '/faq',
  '/contact',
  '/privacy',
  '/terms',
];

// Paths that skip auth recovery (no refresh attempt)
const AUTH_RECOVERY_SKIP_PATHS = ['/login', '/signup', '/auth/google', '/auth/refresh', '/sanctum/csrf-cookie'];

// Extended config type for internal properties
interface InternalConfig extends InternalAxiosRequestConfig {
  __skipAuthRecovery?: boolean;
  __csrfRetried?: boolean;
  __authRetried?: boolean;
}

// Create axios client with configuration
function createClient(baseURL: string): AxiosInstance {
  return axios.create({
    baseURL,
    withCredentials: true,
    withXSRFToken: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  });
}

// Main API client and CSRF client
const api: AxiosInstance = createClient(API_BASE_URL);
const csrfClient: AxiosInstance = createClient(API_ORIGIN);

// Promise holders for refresh and CSRF operations
let refreshPromise: Promise<unknown> | null = null;
let csrfPromise: Promise<unknown> | null = null;

/**
 * Normalize URL to just the pathname
 */
function normalizeUrl(url = ''): string {
  if (!url) {
    return '';
  }

  if (url.startsWith('http://') || url.startsWith('https://')) {
    return new URL(url).pathname;
  }

  return url.startsWith('/') ? url : `/${url}`;
}

/**
 * Check if auth recovery should be skipped for this request
 */
function shouldSkipAuthRecovery(config: InternalConfig = {} as InternalConfig): boolean {
  if (config.__skipAuthRecovery) {
    return true;
  }

  const normalizedUrl = normalizeUrl(config.url);
  return AUTH_RECOVERY_SKIP_PATHS.some((path) => normalizedUrl.startsWith(path));
}

/**
 * Redirect to sign-in page if not on public path
 */
function redirectToSignIn(): void {
  if (typeof window === 'undefined') {
    return;
  }

  const { pathname } = window.location;
  const isPublicPath = pathname === '/'
    || PUBLIC_REDIRECT_PREFIXES
      .filter((prefix) => prefix !== '/')
      .some((prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`));

  if (!isPublicPath) {
    window.location.assign('/signin');
  }
}

/**
 * Check if XSRF token cookie exists
 */
function hasXsrfCookie(): boolean {
  if (typeof document === 'undefined') {
    return false;
  }

  return document.cookie.split('; ').some((cookie) => cookie.startsWith('XSRF-TOKEN='));
}

/**
 * Initialize CSRF token by fetching from server
 */
export function initializeCsrf({ force = false } = {}): Promise<unknown> {
  if (!force && hasXsrfCookie()) {
    return Promise.resolve();
  }

  if (!csrfPromise || force) {
    csrfPromise = csrfClient.get(CSRF_COOKIE_URL, {
      __skipAuthRecovery: true,
    } as InternalConfig).finally(() => {
      csrfPromise = null;
    });
  }

  return csrfPromise;
}

/**
 * Refresh authentication cookies
 */
function refreshAuthCookies(): Promise<unknown> {
  if (!refreshPromise) {
    refreshPromise = api.post('/auth/refresh', null, {
      __skipAuthRecovery: true,
    } as InternalConfig).finally(() => {
      refreshPromise = null;
    });
  }

  return refreshPromise;
}

// Response interceptor for error handling
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError): Promise<never> => {
    const response = error.response;
    const config = error.config as InternalConfig | undefined;

    if (!response || !config) {
      return Promise.reject(error);
    }

    // Handle CSRF token mismatch (419)
    if (response.status === 419 && !config.__csrfRetried && !shouldSkipAuthRecovery(config)) {
      config.__csrfRetried = true;

      try {
        await initializeCsrf({ force: true });
        return api(config as AxiosRequestConfig);
      } catch {
        return Promise.reject(error);
      }
    }

    // Handle unauthorized (401)
    if (response.status === 401 && !config.__authRetried && !shouldSkipAuthRecovery(config)) {
      config.__authRetried = true;

      try {
        await refreshAuthCookies();
        return api(config as AxiosRequestConfig);
      } catch (refreshError) {
        redirectToSignIn();
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

// Auth API methods
export const authApi = {
  login: (credentials: { email: string; password: string }) => api.post('/login', credentials),
  signup: (data: { first_name: string; last_name: string; email: string; password: string; password_confirmation: string }) => api.post('/signup', data),
  forgotPassword: (email: string) => api.post('/forgot-password', { email }),
  verifyCode: (email: string, code: string) => api.post('/verify-code', { email, code }),
  resetPassword: (email: string, code: string, password: string, password_confirmation: string) =>
    api.post('/reset-password', { email, code, password, password_confirmation }),
  changePassword: (current_password: string, new_password: string, new_password_confirmation: string) =>
    api.put('/user/change-password', { current_password, new_password, new_password_confirmation }),
  logout: () => api.post('/auth/logout'),
  googleLogin: (code: string, redirectUri: string) =>
    api.post('/auth/google', { code, redirectUri }),
  refresh: () => refreshAuthCookies(),
};

// User API methods
export const userApi = {
  current: () => api.get('/user'),
  profile: () => api.get('/user/profile'),
  getProfile: () => api.get('/user/profile'),
  updateProfile: (data: Record<string, unknown>) => api.put('/user/profile', data),
};

// Student API methods
export const studentApi = {
  getProfile: () => api.get('/student/profile'),
  hasPurchased: (course_id: string | number) => api.post('/student/has-purchased', { course_id }),
};

// Instructor API methods
export const instructorApi = {
  getProfile: () => api.get('/instructor/profile'),
  create: (biography: string) => api.post('/instructor', { biography }),
  update: (biography: string) => api.put('/instructor', { biography }),
  updateProfile: (biography: string) => api.put('/instructor', { biography }),
  getStats: () => api.get('/instructor/stats'),
  getCourses: () => api.get('/instructor/courses'),
  getCourse: (courseId: string | number) => api.get(`/instructor/courses/${courseId}`),
  createCourse: (data: Record<string, unknown>) => api.post('/instructor/courses', data),
  updateCourse: (courseId: string | number, data: Record<string, unknown>) => api.put(`/instructor/courses/${courseId}`, data),
  deleteCourse: (courseId: string | number) => api.delete(`/instructor/courses/${courseId}`),
  addImage: (courseId: string | number, data: Record<string, unknown>) => api.post(`/instructor/courses/${courseId}/images`, data),
  deleteImage: (courseId: string | number, imageId: string | number) => api.delete(`/instructor/courses/${courseId}/images/${imageId}`),
};

// Course API methods
export const courseApi = {
  list: (params?: Record<string, unknown>) => api.get('/courses', { params }),
  get: (id: string | number) => api.get(`/courses/${id}`),
  search: (q: string) => api.get('/courses/search', { params: { q } }),

  getInstructors: (courseId: string | number) => api.get(`/courses/${courseId}/instructors`),
  addInstructor: (courseId: string | number, instructor_id: string | number) => api.post(`/courses/${courseId}/instructors`, { instructor_id }),
  removeInstructor: (courseId: string | number, instructorId: string | number) => api.delete(`/courses/${courseId}/instructors/${instructorId}`),

  getCategories: (courseId: string | number) => api.get(`/courses/${courseId}/categories`),
  addCategory: (courseId: string | number, category_id: string | number) => api.post(`/courses/${courseId}/categories`, { category_id }),
  removeCategory: (courseId: string | number, categoryId: string | number) => api.delete(`/courses/${courseId}/categories/${categoryId}`),

  getImages: (courseId: string | number) => api.get(`/courses/${courseId}/images`),
  addImage: (courseId: string | number, image_url: string, is_primary: boolean, sort_order?: number) =>
    api.post(`/courses/${courseId}/images`, { image_url, is_primary, sort_order }),
  updateImage: (courseId: string | number, imageId: string | number, data: Record<string, unknown>) => api.put(`/courses/${courseId}/images/${imageId}`, data),
  deleteImage: (courseId: string | number, imageId: string | number) => api.delete(`/courses/${courseId}/images/${imageId}`),

  getObjectives: (courseId: string | number) => api.get(`/courses/${courseId}/objectives`),
  addObjective: (courseId: string | number, objective: string, sort_order?: number) =>
    api.post(`/courses/${courseId}/objectives`, { objective, sort_order }),
  updateObjective: (courseId: string | number, objectiveId: string | number, data: Record<string, unknown>) =>
    api.put(`/courses/${courseId}/objectives/${objectiveId}`, data),
  deleteObjective: (courseId: string | number, objectiveId: string | number) => api.delete(`/courses/${courseId}/objectives/${objectiveId}`),

  getRequirements: (courseId: string | number) => api.get(`/courses/${courseId}/requirements`),
  addRequirement: (courseId: string | number, requirement: string, sort_order?: number) =>
    api.post(`/courses/${courseId}/requirements`, { requirement, sort_order }),
  updateRequirement: (courseId: string | number, requirementId: string | number, data: Record<string, unknown>) =>
    api.put(`/courses/${courseId}/requirements/${requirementId}`, data),
  deleteRequirement: (courseId: string | number, requirementId: string | number) => api.delete(`/courses/${courseId}/requirements/${requirementId}`),

  getChapters: (courseId: string | number) => api.get(`/courses/${courseId}/chapters`),
  addChapter: (courseId: string | number, data: Record<string, unknown>) => api.post(`/courses/${courseId}/chapters`, data),
  updateChapter: (courseId: string | number, chapterId: string | number, data: Record<string, unknown>) => api.put(`/courses/${courseId}/chapters/${chapterId}`, data),
  deleteChapter: (courseId: string | number, chapterId: string | number) => api.delete(`/courses/${courseId}/chapters/${chapterId}`),

  getLessons: (courseId: string | number, chapterId: string | number) => api.get(`/courses/${courseId}/chapters/${chapterId}/lessons`),
  addLesson: (courseId: string | number, chapterId: string | number, data: Record<string, unknown>) => api.post(`/courses/${courseId}/chapters/${chapterId}/lessons`, data),
};

// Lesson API methods
export const lessonApi = {
  get: (lessonId: string | number) => api.get(`/lessons/${lessonId}`),
  update: (lessonId: string | number, data: Record<string, unknown>) => api.put(`/lessons/${lessonId}`, data),
  delete: (lessonId: string | number) => api.delete(`/lessons/${lessonId}`),

  getVideos: (lessonId: string | number) => api.get(`/lessons/${lessonId}/videos`),
  addVideo: (lessonId: string | number, data: Record<string, unknown>) => api.post(`/lessons/${lessonId}/videos`, data),
  updateVideo: (lessonId: string | number, videoId: string | number, data: Record<string, unknown>) => api.put(`/lessons/${lessonId}/videos/${videoId}`, data),
  deleteVideo: (lessonId: string | number, videoId: string | number) => api.delete(`/lessons/${lessonId}/videos/${videoId}`),

  getResources: (lessonId: string | number) => api.get(`/lessons/${lessonId}/resources`),
  addResource: (lessonId: string | number, data: Record<string, unknown>) => api.post(`/lessons/${lessonId}/resources`, data),
  updateResource: (lessonId: string | number, resourceId: string | number, data: Record<string, unknown>) => api.put(`/lessons/${lessonId}/resources/${resourceId}`, data),
  deleteResource: (lessonId: string | number, resourceId: string | number) => api.delete(`/lessons/${lessonId}/resources/${resourceId}`),
};

// Chapter API methods
export const chapterApi = {
  list: (courseId: string | number) => api.get(`/courses/${courseId}/chapters`),
  create: (courseId: string | number, data: Record<string, unknown>) => api.post(`/courses/${courseId}/chapters`, data),
  update: (courseId: string | number, chapterId: string | number, data: Record<string, unknown>) => api.put(`/courses/${courseId}/chapters/${chapterId}`, data),
  delete: (courseId: string | number, chapterId: string | number) => api.delete(`/courses/${courseId}/chapters/${chapterId}`),
};

// Category API methods
export const categoryApi = {
  list: (params?: Record<string, unknown>) => api.get('/categories', { params }),
  get: (id: string | number) => api.get(`/categories/${id}`),
};

// Public Instructor API methods
export const instructorPublicApi = {
  list: (params?: Record<string, unknown>) => api.get('/instructors', { params }),
  get: (id: string | number) => api.get(`/instructors/${id}`),
};

// Cart API methods
export const cartApi = {
  get: () => api.get('/cart'),
  addItem: (course_id: string | number, quantity = 1) => api.post('/cart/items', { course_id, quantity }),
  removeItem: (cartItemId: string | number) => api.delete(`/cart/items/${cartItemId}`),
  clear: () => api.delete('/cart'),
};

// Order API methods
export const orderApi = {
  list: (params?: Record<string, unknown>) => api.get('/orders', { params }),
  create: () => api.post('/orders'),
  get: (orderId: string | number) => api.get(`/orders/${orderId}`),
  completePayment: (orderId: string | number, payment_method: string) => api.post(`/orders/${orderId}/payment`, { payment_method }),
};

// Review API methods
export const reviewApi = {
  list: (params?: Record<string, unknown>) => api.get('/reviews', { params }),
  create: (course_id: string | number, rating: number, review_text: string) => api.post('/reviews', { course_id, rating, review_text }),
  update: (reviewId: string | number, data: Record<string, unknown>) => api.put(`/reviews/${reviewId}`, data),
  delete: (reviewId: string | number) => api.delete(`/reviews/${reviewId}`),
};

// Role API methods
export const roleApi = {
  list: () => api.get('/admin/roles'),
  get: (id: string | number) => api.get(`/admin/roles/${id}`),
  create: (data: Record<string, unknown>) => api.post('/admin/roles', data),
  update: (id: string | number, data: Record<string, unknown>) => api.put(`/admin/roles/${id}`, data),
  delete: (id: string | number) => api.delete(`/admin/roles/${id}`),
  getPermissions: (id: string | number) => api.get(`/admin/roles/${id}/permissions`),
  assignPermissions: (id: string | number, permissions: string[]) => api.post(`/admin/roles/${id}/permissions`, { permissions }),
  syncPermissions: (id: string | number, permissions: string[]) => api.put(`/admin/roles/${id}/permissions`, { permissions }),
  removePermission: (id: string | number, permissionId: string | number) => api.delete(`/admin/roles/${id}/permissions/${permissionId}`),
};

// Permission API methods
export const permissionApi = {
  list: () => api.get('/admin/permissions'),
};

// Admin User API methods
export const adminUserApi = {
  list: (params?: Record<string, unknown>) => api.get('/admin/users', { params }),
  get: (id: string | number) => api.get(`/admin/users/${id}`),
  create: (data: Record<string, unknown>) => api.post('/admin/users', data),
  update: (id: string | number, data: Record<string, unknown>) => api.put(`/admin/users/${id}`, data),
  delete: (id: string | number) => api.delete(`/admin/users/${id}`),
  assignRole: (id: string | number, role_id: string | number) => api.put(`/admin/users/${id}/role`, { role_id }),
};

// Default export is the Axios instance
export default api;
