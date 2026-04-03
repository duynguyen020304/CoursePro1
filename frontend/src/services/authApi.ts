// Auth API with Response Validation
// Wraps authApi responses with Zod schema validation

import { z } from 'zod';
import api from './api';
import {
  loginResponseSchema,
  signupResponseSchema,
  forgotPasswordResponseSchema,
  verifyCodeResponseSchema,
  resetPasswordResponseSchema,
  changePasswordResponseSchema,
  logoutResponseSchema,
  type LoginResponse,
  type SignupResponse,
  type ForgotPasswordResponse,
  type VerifyCodeResponse,
  type ResetPasswordResponse,
  type ChangePasswordResponse,
  type LogoutResponse,
} from '../schemas/auth/apiResponses.schema';

/**
 * Safely validates data against a Zod schema without throwing.
 * Logs validation errors but preserves existing error handling behavior.
 */
function safeValidate<T extends z.ZodTypeAny>(
  schema: T,
  data: unknown,
  responseKey?: string
): { success: true; data: z.infer<T> } | { success: false; data: unknown } {
  const result = schema.safeParse(data);

  if (!result.success) {
    console.error(
      `[API Validation Error]${responseKey ? ` (${responseKey})` : ''}:`,
      result.error.flatten()
    );
    return { success: false, data };
  }

  return { success: true, data: result.data };
}

/**
 * Wraps an API call and validates the response against a Zod schema.
 * Returns the original response data but logs validation errors.
 */
async function validatedApiCall<T extends z.ZodTypeAny>(
  apiCall: Promise<unknown>,
  schema: T,
  responseKey: string
): Promise<z.infer<T>> {
  const response = await apiCall;

  // Handle axios response structure
  const responseData = (response as { data?: unknown }).data;

  const validation = safeValidate(schema, responseData, responseKey);

  // Always return the data (even if validation failed), preserve original behavior
  return (validation.success ? validation.data : responseData) as z.infer<T>;
}

// CSRF and auth token refresh utilities (from api.js)
const CSRF_COOKIE_URL = `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/sanctum/csrf-cookie`;

function hasXsrfCookie(): boolean {
  if (typeof document === 'undefined') {
    return false;
  }
  return document.cookie.split('; ').some((cookie) => cookie.startsWith('XSRF-TOKEN='));
}

let refreshPromise: Promise<unknown> | null = null;

function refreshAuthCookies(): Promise<unknown> {
  if (!refreshPromise) {
    refreshPromise = api.post('/auth/refresh', null, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    }).finally(() => {
      refreshPromise = null;
    }) as Promise<unknown>;
  }
  return refreshPromise;
}

export async function initializeCsrf({ force = false } = {}): Promise<void> {
  if (!force && hasXsrfCookie()) {
    return;
  }
  await api.get('/sanctum/csrf-cookie', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  });
}

/**
 * Auth API with Zod response validation
 * All methods validate API responses using schemas from apiResponses.schema.ts
 * Validation errors are logged but do not throw - existing error handling is preserved
 */
export const authApi = {
  /**
   * Login user
   * Validates response against LoginResponse schema
   */
  login: async (credentials: { email: string; password: string }): Promise<LoginResponse> => {
    return validatedApiCall(api.post('/login', credentials), loginResponseSchema, 'login') as Promise<LoginResponse>;
  },

  /**
   * Register new user
   * Validates response against SignupResponse schema
   */
  signup: async (data: {
    first_name: string;
    last_name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }): Promise<SignupResponse> => {
    return validatedApiCall(api.post('/signup', data), signupResponseSchema, 'signup') as Promise<SignupResponse>;
  },

  /**
   * Request password reset email
   * Validates response against ForgotPasswordResponse schema
   */
  forgotPassword: async (email: string): Promise<ForgotPasswordResponse> => {
    return validatedApiCall(
      api.post('/forgot-password', { email }),
      forgotPasswordResponseSchema,
      'forgotPassword'
    ) as Promise<ForgotPasswordResponse>;
  },

  /**
   * Verify reset code
   * Validates response against VerifyCodeResponse schema
   */
  verifyCode: async (email: string, code: string): Promise<VerifyCodeResponse> => {
    return validatedApiCall(
      api.post('/verify-code', { email, code }),
      verifyCodeResponseSchema,
      'verifyCode'
    ) as Promise<VerifyCodeResponse>;
  },

  /**
   * Reset password with verified code
   * Validates response against ResetPasswordResponse schema
   */
  resetPassword: async (
    email: string,
    code: string,
    password: string,
    password_confirmation: string
  ): Promise<ResetPasswordResponse> => {
    return validatedApiCall(
      api.post('/reset-password', { email, code, password, password_confirmation }),
      resetPasswordResponseSchema,
      'resetPassword'
    ) as Promise<ResetPasswordResponse>;
  },

  /**
   * Change password for authenticated user
   * Validates response against ChangePasswordResponse schema
   */
  changePassword: async (
    current_password: string,
    new_password: string,
    new_password_confirmation: string
  ): Promise<ChangePasswordResponse> => {
    return validatedApiCall(
      api.put('/user/change-password', {
        current_password,
        new_password,
        new_password_confirmation,
      }),
      changePasswordResponseSchema,
      'changePassword'
    ) as Promise<ChangePasswordResponse>;
  },

  /**
   * Logout user
   * Validates response against LogoutResponse schema
   */
  logout: async (): Promise<LogoutResponse> => {
    return validatedApiCall(api.post('/auth/logout'), logoutResponseSchema, 'logout') as Promise<LogoutResponse>;
  },

  /**
   * Login with Google code
   * Note: Google response structure may vary, no schema validation applied
   */
  googleLogin: async (code: string, redirectUri: string): Promise<unknown> => {
    return api.post('/auth/google', { code, redirectUri });
  },

  /**
   * Refresh authentication cookies
   * Note: Refresh endpoint doesn't return standard response schema
   */
  refresh: (): Promise<unknown> => {
    return refreshAuthCookies();
  },

  /**
   * Re-export initializeCsrf for convenience
   */
  initializeCsrf,
};

export default authApi;
