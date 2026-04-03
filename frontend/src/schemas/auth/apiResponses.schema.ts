// Auth API Response Schemas
// Contains Zod schemas for validating authentication API responses

import { z } from 'zod';
import { uuidSchema, emailSchema } from '../common';

/**
 * User schema for API responses
 * Represents the user object returned from auth endpoints
 */
export const userSchema = z.object({
  id: uuidSchema,
  email: emailSchema,
  first_name: z.string().min(1, 'First name is required'),
  last_name: z.string().min(1, 'Last name is required'),
  role: z.enum(['admin', 'student', 'instructor']),
});

/**
 * Type inference from schema
 */
export type User = z.infer<typeof userSchema>;

/**
 * Login response schema
 * Expected shape: { user: User, access_token: string }
 */
export const loginResponseSchema = z.object({
  user: userSchema,
  access_token: z.string().min(1, 'Access token is required'),
});

/**
 * Type inference from schema
 */
export type LoginResponse = z.infer<typeof loginResponseSchema>;

/**
 * Signup response schema
 * Expected shape: { user: User, message: string }
 */
export const signupResponseSchema = z.object({
  user: userSchema,
  message: z.string(),
});

/**
 * Type inference from schema
 */
export type SignupResponse = z.infer<typeof signupResponseSchema>;

/**
 * Forgot password response schema
 * Expected shape: { message: string }
 */
export const forgotPasswordResponseSchema = z.object({
  message: z.string(),
});

/**
 * Type inference from schema
 */
export type ForgotPasswordResponse = z.infer<typeof forgotPasswordResponseSchema>;

/**
 * Verify code response schema
 * Expected shape: { message: string }
 */
export const verifyCodeResponseSchema = z.object({
  message: z.string(),
});

/**
 * Type inference from schema
 */
export type VerifyCodeResponse = z.infer<typeof verifyCodeResponseSchema>;

/**
 * Reset password response schema
 * Expected shape: { message: string }
 */
export const resetPasswordResponseSchema = z.object({
  message: z.string(),
});

/**
 * Type inference from schema
 */
export type ResetPasswordResponse = z.infer<typeof resetPasswordResponseSchema>;

/**
 * Change password response schema
 * Expected shape: { message: string }
 */
export const changePasswordResponseSchema = z.object({
  message: z.string(),
});

/**
 * Type inference from schema
 */
export type ChangePasswordResponse = z.infer<typeof changePasswordResponseSchema>;

/**
 * Logout response schema
 * Expected shape: { message: string }
 */
export const logoutResponseSchema = z.object({
  message: z.string(),
});

/**
 * Type inference from schema
 */
export type LogoutResponse = z.infer<typeof logoutResponseSchema>;
