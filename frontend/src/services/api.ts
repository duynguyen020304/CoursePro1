import axios, { type AxiosInstance, type AxiosRequestConfig, type InternalAxiosRequestConfig, type AxiosResponse, type AxiosError } from 'axios';
import { z } from 'zod';

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
  '/verify-email',
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
  return AUTH_RECOVERY_SKIP_PATHS.some((path) => normalizedUrl === path || normalizedUrl.startsWith(`${path}/`));
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
export function refreshAuthCookies(): Promise<unknown> {
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

// ─── Zod Response Validation ───────────────────────────────────────────────

/**
 * Validates an API response against a Zod schema.
 * On validation failure: logs error, returns raw data (non-breaking).
 * On success: returns typed parsed data.
 */
function validated<T extends z.ZodTypeAny>(
  call: Promise<unknown>,
  schema: T,
  key: string
): Promise<{ data: z.infer<T> }> {
  return call.then(async (resp: unknown) => {
    const data = (resp as { data?: unknown }).data ?? resp;
    const result = schema.safeParse(data);
    if (!result.success) {
      console.error(`[API] ${key}:`, result.error.flatten());
      return { data: data as z.infer<T> };
    }
    return { data: result.data };
  });
}

/**
 * Auth-specific validation helper that unwraps the nested { user } layer.
 * Auth endpoints (login/signup) return { success, message, data: { user: {...} } }.
 * This helper extracts the user directly so consumers access response.data.user (NOT response.data.data.user).
 * For endpoints that return flat user data (profile/current), use standard validated().
 */
function unwrapAuthUser(
  call: Promise<unknown>,
  schema: z.ZodTypeAny,
  key: string
): Promise<{ data: z.infer<typeof userSchema> }> {
  return validated(call, schema, key).then((result) => {
    // Auth responses have { success, message, data: { user } } - extract user directly
    const user = (result.data as { data?: { user?: unknown } })?.data?.user;
    return { data: user as z.infer<typeof userSchema> };
  });
}

// Re-export userSchema type for use in authApi
export type { User } from '../schemas/auth/apiResponses.schema';

/**
 * Generic API response wrapper — validates { success, data } shape.
 * Used for endpoints without domain-specific schemas.
 */
function apiData<T>(key: string) {
  return (call: Promise<unknown>) =>
    validated(call, z.object({ success: z.boolean(), message: z.string().optional(), data: z.any() }), key) as Promise<{ data: T }>;
}

// ─── Auth Response Schemas ─────────────────────────────────────────────────

import {
  loginResponseSchema,
  signupResponseSchema,
  forgotPasswordResponseSchema,
  verifyCodeResponseSchema,
  resetPasswordResponseSchema,
  changePasswordResponseSchema,
  logoutResponseSchema,
  userSchema,
} from '../schemas/auth/apiResponses.schema';

import {
  userProfileSchema,
  updateProfileResponseSchema,
  currentUserResponseSchema,
} from '../schemas/user/apiResponses.schema';

import {
  cartResponseSchema,
  cartItemResponseSchema,
  clearCartResponseSchema,
} from '../schemas/cart/apiResponses.schema';

import {
  studentProfileSchema,
  hasPurchasedSchema,
} from '../schemas/student/apiResponses.schema';

import {
  instructorProfileResponseSchema,
  instructorStatsResponseSchema,
  instructorCourseListResponseSchema,
  instructorCreateResponseSchema,
} from '../schemas/instructor/apiResponses.schema';

import {
  reviewListResponseSchema,
  reviewCreateResponseSchema,
  reviewUpdateResponseSchema,
  reviewDeleteResponseSchema,
} from '../schemas/review/apiResponses.schema';

import {
  roleListApiResponseSchema,
  roleApiResponseSchema,
  roleCreateApiResponseSchema,
  permissionListApiResponseSchema,
  rolePermissionActionApiResponseSchema,
} from '../schemas/admin/role.schema';

import {
  adminUserListResponseSchema,
  adminUserResponseSchema,
  createAdminUserResponseSchema,
  assignRoleResponseSchema,
} from '../schemas/admin/user.schema';

import {
  orderListResponseSchema,
  orderDetailResponseSchema,
  createOrderResponseSchema,
} from '../schemas/order/apiResponses.schema';

import {
  courseSchema,
  courseListResponseSchema,
  courseDetailResponseSchema,
} from '../schemas/course/apiResponses.schema';

// ─── API Methods ────────────────────────────────────────────────────────────

// Auth API methods
export const authApi = {
  login: (credentials: { email: string; password: string }) =>
    unwrapAuthUser(api.post('/login', credentials), loginResponseSchema, 'authApi.login'),
  signup: (data: { first_name: string; last_name: string; email: string; password: string; password_confirmation: string }) =>
    unwrapAuthUser(api.post('/signup', data), signupResponseSchema, 'authApi.signup'),
  forgotPassword: (email: string) =>
    validated(api.post('/forgot-password', { email }), forgotPasswordResponseSchema, 'authApi.forgotPassword'),
  forgotPasswordJwt: (email: string) =>
    validated(api.post('/forgot-password-jwt', { email }), forgotPasswordResponseSchema, 'authApi.forgotPasswordJwt'),
  verifyCode: (email: string, code: string) =>
    validated(api.post('/verify-code', { email, code }), verifyCodeResponseSchema, 'authApi.verifyCode'),
  verifyEmail: (email: string, code: string) =>
    validated(api.post('/email/verify', { email, code }), verifyCodeResponseSchema, 'authApi.verifyEmail'),
  resendVerification: (email: string) =>
    validated(api.post('/email/resend', { email }), forgotPasswordResponseSchema, 'authApi.resendVerification'),
  resetPassword: (email: string, code: string, password: string, password_confirmation: string) =>
    validated(api.post('/reset-password', { email, code, password, password_confirmation }), resetPasswordResponseSchema, 'authApi.resetPassword'),
  changePassword: (current_password: string, new_password: string, new_password_confirmation: string) =>
    validated(api.put('/user/change-password', { current_password, new_password, new_password_confirmation }), changePasswordResponseSchema, 'authApi.changePassword'),
  logout: () =>
    validated(api.post('/auth/logout'), logoutResponseSchema, 'authApi.logout'),
  googleLogin: (code: string, redirectUri: string) =>
    api.post('/auth/google', { code, redirectUri }),
  refresh: () => refreshAuthCookies(),
};

// User API methods
export const userApi = {
  current: () =>
    validated(api.get('/user'), currentUserResponseSchema, 'userApi.current'),
  profile: () =>
    validated(api.get('/user/profile'), currentUserResponseSchema, 'userApi.profile'),
  updateProfile: (data: Record<string, unknown>) =>
    validated(api.put('/user/profile', data), updateProfileResponseSchema, 'userApi.updateProfile'),
};

// Student API methods
export const studentApi = {
  getProfile: () =>
    validated(api.get('/student/profile'), studentProfileSchema, 'studentApi.getProfile'),
  hasPurchased: (course_id: string | number) =>
    validated(api.post('/student/has-purchased', { course_id }), hasPurchasedSchema, 'studentApi.hasPurchased'),
};

// Instructor API methods
export const instructorApi = {
  getProfile: () =>
    validated(api.get('/instructor/profile'), instructorProfileResponseSchema, 'instructorApi.getProfile'),
  create: (biography: string) =>
    validated(api.post('/instructor', { biography }), instructorCreateResponseSchema, 'instructorApi.create'),
  update: (biography: string) =>
    validated(api.put('/instructor', { biography }), instructorProfileResponseSchema, 'instructorApi.update'),
  updateProfile: (biography: string) =>
    validated(api.put('/instructor', { biography }), instructorProfileResponseSchema, 'instructorApi.updateProfile'),
  getStats: () =>
    validated(api.get('/instructor/stats'), instructorStatsResponseSchema, 'instructorApi.getStats'),
  getCourses: () =>
    validated(api.get('/instructor/courses'), instructorCourseListResponseSchema, 'instructorApi.getCourses'),
  getCourse: (courseId: string | number) =>
    validated(
      api.get(`/instructor/courses/${courseId}`),
      z.object({ success: z.boolean(), message: z.string().optional(), data: courseSchema }),
      'instructorApi.getCourse'
    ),
  createCourse: (data: Record<string, unknown>) =>
    validated(
      api.post('/instructor/courses', data),
      z.object({ success: z.boolean(), message: z.string().optional(), data: courseSchema }),
      'instructorApi.createCourse'
    ),
  updateCourse: (courseId: string | number, data: Record<string, unknown>) =>
    validated(
      api.put(`/instructor/courses/${courseId}`, data),
      z.object({ success: z.boolean(), message: z.string().optional(), data: courseSchema }),
      'instructorApi.updateCourse'
    ),
  deleteCourse: (courseId: string | number) =>
    apiData<{ success: boolean; message?: string }>('instructorApi.deleteCourse')(api.delete(`/instructor/courses/${courseId}`)),
  addImage: (courseId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('instructorApi.addImage')(api.post(`/instructor/courses/${courseId}/images`, data)),
  deleteImage: (courseId: string | number, imageId: string | number) =>
    apiData<{ success: boolean; message?: string }>('instructorApi.deleteImage')(api.delete(`/instructor/courses/${courseId}/images/${imageId}`)),
};

// Course API methods
export const courseApi = {
  list: (params?: Record<string, unknown>) =>
    validated(api.get('/courses', { params }), courseListResponseSchema, 'courseApi.list'),
  get: (id: string | number) =>
    validated(api.get(`/courses/${id}`), courseDetailResponseSchema, 'courseApi.get'),
  search: (q: string) =>
    validated(api.get('/courses/search', { params: { q } }), courseListResponseSchema, 'courseApi.search'),

  getInstructors: (courseId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('courseApi.getInstructors')(api.get(`/courses/${courseId}/instructors`)),
  addInstructor: (courseId: string | number, instructor_id: string | number) =>
    apiData<{ success: boolean; message?: string }>('courseApi.addInstructor')(api.post(`/courses/${courseId}/instructors`, { instructor_id })),
  removeInstructor: (courseId: string | number, instructorId: string | number) =>
    apiData<{ success: boolean; message?: string }>('courseApi.removeInstructor')(api.delete(`/courses/${courseId}/instructors/${instructorId}`)),

  getCategories: (courseId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('courseApi.getCategories')(api.get(`/courses/${courseId}/categories`)),
  addCategory: (courseId: string | number, category_id: string | number) =>
    apiData<{ success: boolean; message?: string }>('courseApi.addCategory')(api.post(`/courses/${courseId}/categories`, { category_id })),
  removeCategory: (courseId: string | number, categoryId: string | number) =>
    apiData<{ success: boolean; message?: string }>('courseApi.removeCategory')(api.delete(`/courses/${courseId}/categories/${categoryId}`)),

  getImages: (courseId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('courseApi.getImages')(api.get(`/courses/${courseId}/images`)),
  addImage: (courseId: string | number, image_url: string, is_primary: boolean, sort_order?: number) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('courseApi.addImage')(api.post(`/courses/${courseId}/images`, { image_url, is_primary, sort_order })),
  updateImage: (courseId: string | number, imageId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('courseApi.updateImage')(api.put(`/courses/${courseId}/images/${imageId}`, data)),
  deleteImage: (courseId: string | number, imageId: string | number) =>
    apiData<{ success: boolean; message?: string }>('courseApi.deleteImage')(api.delete(`/courses/${courseId}/images/${imageId}`)),

  getObjectives: (courseId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('courseApi.getObjectives')(api.get(`/courses/${courseId}/objectives`)),
  addObjective: (courseId: string | number, objective: string, sort_order?: number) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('courseApi.addObjective')(api.post(`/courses/${courseId}/objectives`, { objective, sort_order })),
  updateObjective: (courseId: string | number, objectiveId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('courseApi.updateObjective')(api.put(`/courses/${courseId}/objectives/${objectiveId}`, data)),
  deleteObjective: (courseId: string | number, objectiveId: string | number) =>
    apiData<{ success: boolean; message?: string }>('courseApi.deleteObjective')(api.delete(`/courses/${courseId}/objectives/${objectiveId}`)),

  getRequirements: (courseId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('courseApi.getRequirements')(api.get(`/courses/${courseId}/requirements`)),
  addRequirement: (courseId: string | number, requirement: string, sort_order?: number) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('courseApi.addRequirement')(api.post(`/courses/${courseId}/requirements`, { requirement, sort_order })),
  updateRequirement: (courseId: string | number, requirementId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('courseApi.updateRequirement')(api.put(`/courses/${courseId}/requirements/${requirementId}`, data)),
  deleteRequirement: (courseId: string | number, requirementId: string | number) =>
    apiData<{ success: boolean; message?: string }>('courseApi.deleteRequirement')(api.delete(`/courses/${courseId}/requirements/${requirementId}`)),

  getChapters: (courseId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('courseApi.getChapters')(api.get(`/courses/${courseId}/chapters`)),
  addChapter: (courseId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('courseApi.addChapter')(api.post(`/courses/${courseId}/chapters`, data)),
  updateChapter: (courseId: string | number, chapterId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('courseApi.updateChapter')(api.put(`/courses/${courseId}/chapters/${chapterId}`, data)),
  deleteChapter: (courseId: string | number, chapterId: string | number) =>
    apiData<{ success: boolean; message?: string }>('courseApi.deleteChapter')(api.delete(`/courses/${courseId}/chapters/${chapterId}`)),

  getLessons: (courseId: string | number, chapterId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('courseApi.getLessons')(api.get(`/courses/${courseId}/chapters/${chapterId}/lessons`)),
  addLesson: (courseId: string | number, chapterId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('courseApi.addLesson')(api.post(`/courses/${courseId}/chapters/${chapterId}/lessons`, data)),
};

// Lesson API methods
export const lessonApi = {
  get: (lessonId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('lessonApi.get')(api.get(`/lessons/${lessonId}`)),
  update: (lessonId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('lessonApi.update')(api.put(`/lessons/${lessonId}`, data)),
  delete: (lessonId: string | number) =>
    apiData<{ success: boolean; message?: string }>('lessonApi.delete')(api.delete(`/lessons/${lessonId}`)),

  getVideos: (lessonId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('lessonApi.getVideos')(api.get(`/lessons/${lessonId}/videos`)),
  addVideo: (lessonId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('lessonApi.addVideo')(api.post(`/lessons/${lessonId}/videos`, data)),
  initiateVideoUpload: (lessonId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('lessonApi.initiateVideoUpload')(api.post(`/lessons/${lessonId}/videos/uploads/initiate`, data)),
  completeVideoUpload: (lessonId: string | number, videoId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('lessonApi.completeVideoUpload')(api.post(`/lessons/${lessonId}/videos/${videoId}/uploads/complete`, data)),
  abortVideoUpload: (lessonId: string | number, videoId: string | number, data?: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string }>('lessonApi.abortVideoUpload')(api.post(`/lessons/${lessonId}/videos/${videoId}/uploads/abort`, data ?? {})),
  updateVideo: (lessonId: string | number, videoId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('lessonApi.updateVideo')(api.put(`/lessons/${lessonId}/videos/${videoId}`, data)),
  deleteVideo: (lessonId: string | number, videoId: string | number) =>
    apiData<{ success: boolean; message?: string }>('lessonApi.deleteVideo')(api.delete(`/lessons/${lessonId}/videos/${videoId}`)),

  getResources: (lessonId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('lessonApi.getResources')(api.get(`/lessons/${lessonId}/resources`)),
  addResource: (lessonId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('lessonApi.addResource')(api.post(`/lessons/${lessonId}/resources`, data)),
  updateResource: (lessonId: string | number, resourceId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('lessonApi.updateResource')(api.put(`/lessons/${lessonId}/resources/${resourceId}`, data)),
  deleteResource: (lessonId: string | number, resourceId: string | number) =>
    apiData<{ success: boolean; message?: string }>('lessonApi.deleteResource')(api.delete(`/lessons/${lessonId}/resources/${resourceId}`)),
};

// Chapter API methods
export const chapterApi = {
  list: (courseId: string | number) =>
    apiData<{ success: boolean; data: unknown }>('chapterApi.list')(api.get(`/courses/${courseId}/chapters`)),
  create: (courseId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('chapterApi.create')(api.post(`/courses/${courseId}/chapters`, data)),
  update: (courseId: string | number, chapterId: string | number, data: Record<string, unknown>) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('chapterApi.update')(api.put(`/courses/${courseId}/chapters/${chapterId}`, data)),
  delete: (courseId: string | number, chapterId: string | number) =>
    apiData<{ success: boolean; message?: string }>('chapterApi.delete')(api.delete(`/courses/${courseId}/chapters/${chapterId}`)),
};

// Category API methods
export const categoryApi = {
  list: (params?: Record<string, unknown>) =>
    apiData<{ success: boolean; data: unknown }>('categoryApi.list')(api.get('/categories', { params })),
  get: (id: string | number) =>
    apiData<{ success: boolean; data: unknown }>('categoryApi.get')(api.get(`/categories/${id}`)),
};

// Public Instructor API methods
export const instructorPublicApi = {
  list: (params?: Record<string, unknown>) =>
    apiData<{ success: boolean; data: unknown }>('instructorPublicApi.list')(api.get('/instructors', { params })),
  get: (id: string | number) =>
    apiData<{ success: boolean; data: unknown }>('instructorPublicApi.get')(api.get(`/instructors/${id}`)),
};

// Cart API methods
export const cartApi = {
  get: () =>
    validated(api.get('/cart'), cartResponseSchema, 'cartApi.get'),
  addItem: (course_id: string | number, quantity = 1) =>
    validated(api.post('/cart/items', { course_id, quantity }), cartItemResponseSchema, 'cartApi.addItem'),
  removeItem: (cartItemId: string | number) =>
    apiData<{ success: boolean; message?: string }>('cartApi.removeItem')(api.delete(`/cart/items/${cartItemId}`)),
  clear: () =>
    validated(api.delete('/cart'), clearCartResponseSchema, 'cartApi.clear'),
};

// Order API methods
export const orderApi = {
  list: (params?: Record<string, unknown>) =>
    validated(api.get('/orders', { params }), orderListResponseSchema, 'orderApi.list'),
  create: () =>
    validated(api.post('/orders'), createOrderResponseSchema, 'orderApi.create'),
  get: (orderId: string | number) =>
    validated(api.get(`/orders/${orderId}`), orderDetailResponseSchema, 'orderApi.get'),
  completePayment: (orderId: string | number, payment_method: string) =>
    apiData<{ success: boolean; message?: string; data: unknown }>('orderApi.completePayment')(api.post(`/orders/${orderId}/payment`, { order_id: orderId, payment_method })),
};

// Review API methods
export const reviewApi = {
  list: (params?: Record<string, unknown>) =>
    validated(api.get('/reviews', { params }), reviewListResponseSchema, 'reviewApi.list'),
  create: (course_id: string | number, rating: number, review_text: string) =>
    validated(api.post('/reviews', { course_id, rating, review_text }), reviewCreateResponseSchema, 'reviewApi.create'),
  update: (reviewId: string | number, data: Record<string, unknown>) =>
    validated(api.put(`/reviews/${reviewId}`, data), reviewUpdateResponseSchema, 'reviewApi.update'),
  delete: (reviewId: string | number) =>
    validated(api.delete(`/reviews/${reviewId}`), reviewDeleteResponseSchema, 'reviewApi.delete'),
};

// Role API methods
export const roleApi = {
  list: () =>
    validated(api.get('/admin/roles'), roleListApiResponseSchema, 'roleApi.list'),
  get: (id: string | number) =>
    validated(api.get(`/admin/roles/${id}`), roleApiResponseSchema, 'roleApi.get'),
  create: (data: Record<string, unknown>) =>
    validated(api.post('/admin/roles', data), roleCreateApiResponseSchema, 'roleApi.create'),
  update: (id: string | number, data: Record<string, unknown>) =>
    validated(api.put(`/admin/roles/${id}`, data), roleApiResponseSchema, 'roleApi.update'),
  delete: (id: string | number) =>
    apiData<{ success: boolean; message?: string }>('roleApi.delete')(api.delete(`/admin/roles/${id}`)),
  getPermissions: (id: string | number) =>
    validated(api.get(`/admin/roles/${id}/permissions`), rolePermissionActionApiResponseSchema, 'roleApi.getPermissions'),
  assignPermissions: (id: string | number, permissions: string[]) =>
    validated(api.post(`/admin/roles/${id}/permissions`, { permissions }), rolePermissionActionApiResponseSchema, 'roleApi.assignPermissions'),
  syncPermissions: (id: string | number, permissions: string[]) =>
    validated(api.put(`/admin/roles/${id}/permissions`, { permissions }), rolePermissionActionApiResponseSchema, 'roleApi.syncPermissions'),
  removePermission: (id: string | number, permissionId: string | number) =>
    apiData<{ success: boolean; message?: string }>('roleApi.removePermission')(api.delete(`/admin/roles/${id}/permissions/${permissionId}`)),
};

// Permission API methods
export const permissionApi = {
  list: () =>
    validated(api.get('/admin/permissions'), permissionListApiResponseSchema, 'permissionApi.list'),
};

// Admin User API methods
export const adminUserApi = {
  list: (params?: Record<string, unknown>) =>
    validated(api.get('/admin/users', { params }), adminUserListResponseSchema, 'adminUserApi.list'),
  get: (id: string | number) =>
    validated(api.get(`/admin/users/${id}`), adminUserResponseSchema, 'adminUserApi.get'),
  create: (data: Record<string, unknown>) =>
    validated(api.post('/admin/users', data), createAdminUserResponseSchema, 'adminUserApi.create'),
  update: (id: string | number, data: Record<string, unknown>) =>
    validated(api.put(`/admin/users/${id}`, data), adminUserResponseSchema, 'adminUserApi.update'),
  delete: (id: string | number) =>
    apiData<{ success: boolean; message?: string }>('adminUserApi.delete')(api.delete(`/admin/users/${id}`)),
  assignRole: (id: string | number, role_id: string | number) =>
    validated(api.put(`/admin/users/${id}/role`, { role_id }), assignRoleResponseSchema, 'adminUserApi.assignRole'),
};

// Default export is the Axios instance
export default api;
