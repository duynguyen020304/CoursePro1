// Auth API with Response Validation
// Wraps auth API responses with Zod schema validation

import { z } from 'zod';
import api, { initializeCsrf, refreshAuthCookies } from './api';
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

async function validatedApiCall<T extends z.ZodTypeAny>(
  apiCall: Promise<unknown>,
  schema: T,
  responseKey: string
): Promise<z.infer<T>> {
  const response = await apiCall;
  const responseData = (response as { data?: unknown }).data;
  const validation = safeValidate(schema, responseData, responseKey);
  return (validation.success ? validation.data : responseData) as z.infer<T>;
}

export { initializeCsrf };

export const authApi = {
  login: async (credentials: { email: string; password: string }): Promise<LoginResponse> => {
    return validatedApiCall(api.post('/login', credentials), loginResponseSchema, 'login') as Promise<LoginResponse>;
  },

  signup: async (data: {
    first_name: string;
    last_name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }): Promise<SignupResponse> => {
    return validatedApiCall(api.post('/signup', data), signupResponseSchema, 'signup') as Promise<SignupResponse>;
  },

  forgotPassword: async (email: string): Promise<ForgotPasswordResponse> => {
    return validatedApiCall(
      api.post('/forgot-password', { email }),
      forgotPasswordResponseSchema,
      'forgotPassword'
    ) as Promise<ForgotPasswordResponse>;
  },

  forgotPasswordJwt: async (email: string): Promise<ForgotPasswordResponse> => {
    return validatedApiCall(
      api.post('/forgot-password-jwt', { email }),
      forgotPasswordResponseSchema,
      'forgotPasswordJwt'
    ) as Promise<ForgotPasswordResponse>;
  },

  verifyCode: async (email: string, code: string): Promise<VerifyCodeResponse> => {
    return validatedApiCall(
      api.post('/verify-code', { email, code }),
      verifyCodeResponseSchema,
      'verifyCode'
    ) as Promise<VerifyCodeResponse>;
  },

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

  logout: async (): Promise<LogoutResponse> => {
    return validatedApiCall(api.post('/auth/logout'), logoutResponseSchema, 'logout') as Promise<LogoutResponse>;
  },

  googleLogin: async (code: string, redirectUri: string): Promise<unknown> => {
    return api.post('/auth/google', { code, redirectUri });
  },

  refresh: (): Promise<unknown> => {
    return refreshAuthCookies();
  },

  initializeCsrf,
};

export default authApi;
