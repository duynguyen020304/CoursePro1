import axios from 'axios';

const RAW_API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
const API_BASE_URL = RAW_API_BASE_URL;
const API_ORIGIN = RAW_API_BASE_URL.startsWith('http://') || RAW_API_BASE_URL.startsWith('https://')
  ? new URL(RAW_API_BASE_URL).origin
  : (import.meta.env.VITE_BACKEND_ORIGIN || 'http://localhost:8000');
const CSRF_COOKIE_URL = `${API_ORIGIN}/sanctum/csrf-cookie`;
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
const AUTH_RECOVERY_SKIP_PATHS = ['/login', '/signup', '/auth/google', '/auth/refresh', '/sanctum/csrf-cookie'];

function createClient(baseURL) {
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

const api = createClient(API_BASE_URL);
const csrfClient = createClient(API_ORIGIN);

let refreshPromise = null;
let csrfPromise = null;

function normalizeUrl(url = '') {
  if (!url) {
    return '';
  }

  if (url.startsWith('http://') || url.startsWith('https://')) {
    return new URL(url).pathname;
  }

  return url.startsWith('/') ? url : `/${url}`;
}

function shouldSkipAuthRecovery(config = {}) {
  if (config.__skipAuthRecovery) {
    return true;
  }

  const normalizedUrl = normalizeUrl(config.url);
  return AUTH_RECOVERY_SKIP_PATHS.some((path) => normalizedUrl.startsWith(path));
}

function redirectToSignIn() {
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

function hasXsrfCookie() {
  if (typeof document === 'undefined') {
    return false;
  }

  return document.cookie.split('; ').some((cookie) => cookie.startsWith('XSRF-TOKEN='));
}

export function initializeCsrf({ force = false } = {}) {
  if (!force && hasXsrfCookie()) {
    return Promise.resolve();
  }

  if (!csrfPromise || force) {
    csrfPromise = csrfClient.get(CSRF_COOKIE_URL, {
      __skipAuthRecovery: true,
    }).finally(() => {
      csrfPromise = null;
    });
  }

  return csrfPromise;
}

function refreshAuthCookies() {
  if (!refreshPromise) {
    refreshPromise = api.post('/auth/refresh', null, {
      __skipAuthRecovery: true,
    }).finally(() => {
      refreshPromise = null;
    });
  }

  return refreshPromise;
}

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const { response, config } = error;

    if (!response || !config) {
      return Promise.reject(error);
    }

    if (response.status === 419 && !config.__csrfRetried && !shouldSkipAuthRecovery(config)) {
      config.__csrfRetried = true;

      try {
        await initializeCsrf({ force: true });
        return api(config);
      } catch {
        return Promise.reject(error);
      }
    }

    if (response.status === 401 && !config.__authRetried && !shouldSkipAuthRecovery(config)) {
      config.__authRetried = true;

      try {
        await refreshAuthCookies();
        return api(config);
      } catch (refreshError) {
        redirectToSignIn();
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

export const authApi = {
  login: (credentials) => api.post('/login', credentials),
  signup: (data) => api.post('/signup', data),
  forgotPassword: (email) => api.post('/forgot-password', { email }),
  verifyCode: (email, code) => api.post('/verify-code', { email, code }),
  resetPassword: (email, code, password, password_confirmation) =>
    api.post('/reset-password', { email, code, password, password_confirmation }),
  changePassword: (current_password, new_password, new_password_confirmation) =>
    api.put('/user/change-password', { current_password, new_password, new_password_confirmation }),
  logout: () => api.post('/auth/logout'),
  googleLogin: (code, redirectUri) =>
    api.post('/auth/google', { code, redirectUri }),
  refresh: () => refreshAuthCookies(),
};

export const userApi = {
  current: () => api.get('/user'),
  profile: () => api.get('/user/profile'),
  getProfile: () => api.get('/user/profile'),
  updateProfile: (data) => api.put('/user/profile', data),
};

export const studentApi = {
  getProfile: () => api.get('/student/profile'),
  hasPurchased: (course_id) => api.post('/student/has-purchased', { course_id }),
};

export const instructorApi = {
  getProfile: () => api.get('/instructor/profile'),
  create: (biography) => api.post('/instructor', { biography }),
  update: (biography) => api.put('/instructor', { biography }),
  updateProfile: (biography) => api.put('/instructor', { biography }),
  getStats: () => api.get('/instructor/stats'),
  getCourses: () => api.get('/instructor/courses'),
  getCourse: (courseId) => api.get(`/instructor/courses/${courseId}`),
  createCourse: (data) => api.post('/instructor/courses', data),
  updateCourse: (courseId, data) => api.put(`/instructor/courses/${courseId}`, data),
  deleteCourse: (courseId) => api.delete(`/instructor/courses/${courseId}`),
  addImage: (courseId, data) => api.post(`/instructor/courses/${courseId}/images`, data),
  deleteImage: (courseId, imageId) => api.delete(`/instructor/courses/${courseId}/images/${imageId}`),
};

export const courseApi = {
  list: (params) => api.get('/courses', { params }),
  get: (id) => api.get(`/courses/${id}`),
  search: (q) => api.get('/courses/search', { params: { q } }),

  getInstructors: (courseId) => api.get(`/courses/${courseId}/instructors`),
  addInstructor: (courseId, instructor_id) => api.post(`/courses/${courseId}/instructors`, { instructor_id }),
  removeInstructor: (courseId, instructorId) => api.delete(`/courses/${courseId}/instructors/${instructorId}`),

  getCategories: (courseId) => api.get(`/courses/${courseId}/categories`),
  addCategory: (courseId, category_id) => api.post(`/courses/${courseId}/categories`, { category_id }),
  removeCategory: (courseId, categoryId) => api.delete(`/courses/${courseId}/categories/${categoryId}`),

  getImages: (courseId) => api.get(`/courses/${courseId}/images`),
  addImage: (courseId, image_url, is_primary, sort_order) =>
    api.post(`/courses/${courseId}/images`, { image_url, is_primary, sort_order }),
  updateImage: (courseId, imageId, data) => api.put(`/courses/${courseId}/images/${imageId}`, data),
  deleteImage: (courseId, imageId) => api.delete(`/courses/${courseId}/images/${imageId}`),

  getObjectives: (courseId) => api.get(`/courses/${courseId}/objectives`),
  addObjective: (courseId, objective, sort_order) =>
    api.post(`/courses/${courseId}/objectives`, { objective, sort_order }),
  updateObjective: (courseId, objectiveId, data) =>
    api.put(`/courses/${courseId}/objectives/${objectiveId}`, data),
  deleteObjective: (courseId, objectiveId) => api.delete(`/courses/${courseId}/objectives/${objectiveId}`),

  getRequirements: (courseId) => api.get(`/courses/${courseId}/requirements`),
  addRequirement: (courseId, requirement, sort_order) =>
    api.post(`/courses/${courseId}/requirements`, { requirement, sort_order }),
  updateRequirement: (courseId, requirementId, data) =>
    api.put(`/courses/${courseId}/requirements/${requirementId}`, data),
  deleteRequirement: (courseId, requirementId) => api.delete(`/courses/${courseId}/requirements/${requirementId}`),

  getChapters: (courseId) => api.get(`/courses/${courseId}/chapters`),
  addChapter: (courseId, data) => api.post(`/courses/${courseId}/chapters`, data),
  updateChapter: (courseId, chapterId, data) => api.put(`/courses/${courseId}/chapters/${chapterId}`, data),
  deleteChapter: (courseId, chapterId) => api.delete(`/courses/${courseId}/chapters/${chapterId}`),

  getLessons: (courseId, chapterId) => api.get(`/courses/${courseId}/chapters/${chapterId}/lessons`),
  addLesson: (courseId, chapterId, data) => api.post(`/courses/${courseId}/chapters/${chapterId}/lessons`, data),
};

export const lessonApi = {
  get: (lessonId) => api.get(`/lessons/${lessonId}`),
  update: (lessonId, data) => api.put(`/lessons/${lessonId}`, data),
  delete: (lessonId) => api.delete(`/lessons/${lessonId}`),

  getVideos: (lessonId) => api.get(`/lessons/${lessonId}/videos`),
  addVideo: (lessonId, data) => api.post(`/lessons/${lessonId}/videos`, data),
  updateVideo: (lessonId, videoId, data) => api.put(`/lessons/${lessonId}/videos/${videoId}`, data),
  deleteVideo: (lessonId, videoId) => api.delete(`/lessons/${lessonId}/videos/${videoId}`),

  getResources: (lessonId) => api.get(`/lessons/${lessonId}/resources`),
  addResource: (lessonId, data) => api.post(`/lessons/${lessonId}/resources`, data),
  updateResource: (lessonId, resourceId, data) => api.put(`/lessons/${lessonId}/resources/${resourceId}`, data),
  deleteResource: (lessonId, resourceId) => api.delete(`/lessons/${lessonId}/resources/${resourceId}`),
};

export const chapterApi = {
  list: (courseId) => api.get(`/courses/${courseId}/chapters`),
  create: (courseId, data) => api.post(`/courses/${courseId}/chapters`, data),
  update: (courseId, chapterId, data) => api.put(`/courses/${courseId}/chapters/${chapterId}`, data),
  delete: (courseId, chapterId) => api.delete(`/courses/${courseId}/chapters/${chapterId}`),
};

export const categoryApi = {
  list: (params) => api.get('/categories', { params }),
  get: (id) => api.get(`/categories/${id}`),
};

export const instructorPublicApi = {
  list: (params) => api.get('/instructors', { params }),
  get: (id) => api.get(`/instructors/${id}`),
};

export const cartApi = {
  get: () => api.get('/cart'),
  addItem: (course_id, quantity = 1) => api.post('/cart/items', { course_id, quantity }),
  removeItem: (cartItemId) => api.delete(`/cart/items/${cartItemId}`),
  clear: () => api.delete('/cart'),
};

export const orderApi = {
  list: (params) => api.get('/orders', { params }),
  create: () => api.post('/orders'),
  get: (orderId) => api.get(`/orders/${orderId}`),
  completePayment: (orderId, payment_method) => api.post(`/orders/${orderId}/payment`, { payment_method }),
};

export const reviewApi = {
  list: (params) => api.get('/reviews', { params }),
  create: (course_id, rating, review_text) => api.post('/reviews', { course_id, rating, review_text }),
  update: (reviewId, data) => api.put(`/reviews/${reviewId}`, data),
  delete: (reviewId) => api.delete(`/reviews/${reviewId}`),
};

export const roleApi = {
  list: () => api.get('/admin/roles'),
  get: (id) => api.get(`/admin/roles/${id}`),
  create: (data) => api.post('/admin/roles', data),
  update: (id, data) => api.put(`/admin/roles/${id}`, data),
  delete: (id) => api.delete(`/admin/roles/${id}`),
  getPermissions: (id) => api.get(`/admin/roles/${id}/permissions`),
  assignPermissions: (id, permissions) => api.post(`/admin/roles/${id}/permissions`, { permissions }),
  syncPermissions: (id, permissions) => api.put(`/admin/roles/${id}/permissions`, { permissions }),
  removePermission: (id, permissionId) => api.delete(`/admin/roles/${id}/permissions/${permissionId}`),
};

export const permissionApi = {
  list: () => api.get('/admin/permissions'),
};

export const adminUserApi = {
  list: (params) => api.get('/admin/users', { params }),
  get: (id) => api.get(`/admin/users/${id}`),
  create: (data) => api.post('/admin/users', data),
  update: (id, data) => api.put(`/admin/users/${id}`, data),
  delete: (id) => api.delete(`/admin/users/${id}`),
  assignRole: (id, role_id) => api.put(`/admin/users/${id}/role`, { role_id }),
};

export default api;
