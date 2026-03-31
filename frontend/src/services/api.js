import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor - add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor - handle errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Only redirect on 401 for authenticated endpoints (not cart/homepage)
    if (error.response?.status === 401) {
      const currentPath = window.location.pathname;
      // Don't redirect if already on signin/signup or public pages
      const publicPaths = ['/signin', '/signup', '/forgot-password', '/verify-code', '/reset-password'];
      const isPublicPath = publicPaths.some(path => currentPath.startsWith(path));

      if (!isPublicPath && currentPath === '/') {
        // Clear token but don't redirect from public pages
        localStorage.removeItem('token');
        localStorage.removeItem('user');
      } else if (!isPublicPath) {
        // Redirect to signin for protected routes
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/signin';
      }
    }
    return Promise.reject(error);
  }
);

// Auth APIs
export const authApi = {
  login: (credentials) => api.post('/login', credentials),
  signup: (data) => api.post('/signup', data),
  forgotPassword: (email) => api.post('/forgot-password', { email }),
  verifyCode: (email, code) => api.post('/verify-code', { email, code }),
  resetPassword: (email, code, password, password_confirmation) =>
    api.post('/reset-password', { email, code, password, password_confirmation }),
  changePassword: (current_password, new_password, new_password_confirmation) =>
    api.put('/user/change-password', { current_password, new_password, new_password_confirmation }),
  logout: () => api.post('/logout'),
};

// User APIs
export const userApi = {
  getProfile: () => api.get('/user/profile'),
  updateProfile: (data) => api.put('/user/profile', data),
};

// Student APIs
export const studentApi = {
  getProfile: () => api.get('/student/profile'),
  hasPurchased: (course_id) => api.post('/student/has-purchased', { course_id }),
};

// Instructor APIs
export const instructorApi = {
  getProfile: () => api.get('/instructor/profile'),
  create: (biography) => api.post('/instructor', { biography }),
  update: (biography) => api.put('/instructor', { biography }),
};

// Course APIs
export const courseApi = {
  list: (params) => api.get('/courses', { params }),
  get: (id) => api.get(`/courses/${id}`),
  search: (q) => api.get('/courses/search', { params: { q } }),

  // Course nested resources
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

// Lesson APIs
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

// Chapter APIs
export const chapterApi = {
  list: (courseId) => api.get(`/courses/${courseId}/chapters`),
  create: (courseId, data) => api.post(`/courses/${courseId}/chapters`, data),
  update: (courseId, chapterId, data) => api.put(`/courses/${courseId}/chapters/${chapterId}`, data),
  delete: (courseId, chapterId) => api.delete(`/courses/${courseId}/chapters/${chapterId}`),
};

// Category APIs
export const categoryApi = {
  list: (params) => api.get('/categories', { params }),
  get: (id) => api.get(`/categories/${id}`),
};

// Instructor public APIs
export const instructorPublicApi = {
  list: (params) => api.get('/instructors', { params }),
  get: (id) => api.get(`/instructors/${id}`),
};

// Cart APIs
export const cartApi = {
  get: () => api.get('/cart'),
  addItem: (course_id, quantity = 1) => api.post('/cart/items', { course_id, quantity }),
  removeItem: (cartItemId) => api.delete(`/cart/items/${cartItemId}`),
  clear: () => api.delete('/cart'),
};

// Order APIs
export const orderApi = {
  list: () => api.get('/orders'),
  create: () => api.post('/orders'),
  get: (orderId) => api.get(`/orders/${orderId}`),
  completePayment: (orderId, payment_method) => api.post(`/orders/${orderId}/payment`, { payment_method }),
};

// Review APIs
export const reviewApi = {
  list: (params) => api.get('/reviews', { params }),
  create: (course_id, rating, review_text) => api.post('/reviews', { course_id, rating, review_text }),
  update: (reviewId, data) => api.put(`/reviews/${reviewId}`, data),
  delete: (reviewId) => api.delete(`/reviews/${reviewId}`),
};

export default api;
