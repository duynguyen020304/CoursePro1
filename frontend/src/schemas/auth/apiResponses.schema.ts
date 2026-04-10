// Auth API Response Schemas
// Contains Zod schemas for validating authentication API responses

import { z } from 'zod';
import { uuidSchema, emailSchema } from '../common';

/**
 * User schema for API responses
 * Represents the user object returned from auth endpoints
 * Backend returns: { user_id, first_name, last_name, email, role_id, profile_image }
 */
export const userSchema = z.object({
  user_id: z.string(),
  email: z.string().email(),
   email_verified_at: z.string().datetime().nullable().optional(),
   first_name: z.string(),
   last_name: z.string(),
   is_verified: z.boolean().optional().default(false),
   role_id: z.string(),
   profile_image: z.string().nullable().optional(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().datetime().nullable().optional(),
  updated_at: z.string().datetime().nullable().optional(),
});

/**
 * Type inference from schema
 */
export type User = z.infer<typeof userSchema>;

/**
 * Auth response wrapper schema
 * The backend returns all auth responses wrapped in { success, message, data }
 */
const authResponseWrapperSchema = <T extends z.ZodTypeAny>(dataSchema: T) =>
  z.object({
    success: z.literal(true),
    message: z.string().optional(),
    data: dataSchema,
  });

/**
 * Login response schema
 * Backend returns: { success: true, message: '...', data: { user: {...} } }
 */
export const loginResponseSchema = authResponseWrapperSchema(
  z.object({
    user: userSchema,
  })
);

/**
 * Type inference from schema
 */
export type LoginResponse = z.infer<typeof loginResponseSchema>;

/**
 * Signup response schema
 * Backend returns: { success: true, message: '...', data: { user: {...} } }
 */
export const signupResponseSchema = authResponseWrapperSchema(
  z.object({
    user: userSchema,
  })
);

/**
 * Type inference from schema
 */
export type SignupResponse = z.infer<typeof signupResponseSchema>;

/**
 * Forgot password response schema
 * Backend returns: { success: true, message: '...' }
 */
export const forgotPasswordResponseSchema = authResponseWrapperSchema(
  z.object({})
);

/**
 * Type inference from schema
 */
export type ForgotPasswordResponse = z.infer<typeof forgotPasswordResponseSchema>;

/**
 * Verify code response schema
 * Backend returns: { success: true, message: '...' }
 */
export const verifyCodeResponseSchema = authResponseWrapperSchema(
  z.object({})
);

/**
 * Type inference from schema
 */
export type VerifyCodeResponse = z.infer<typeof verifyCodeResponseSchema>;

/**
 * Reset password response schema
 * Backend returns: { success: true, message: '...' }
 */
export const resetPasswordResponseSchema = authResponseWrapperSchema(
  z.object({})
);

/**
 * Type inference from schema
 */
export type ResetPasswordResponse = z.infer<typeof resetPasswordResponseSchema>;

/**
 * Change password response schema
 * Backend returns: { success: true, message: '...' }
 */
export const changePasswordResponseSchema = authResponseWrapperSchema(
  z.object({})
);

/**
 * Type inference from schema
 */
export type ChangePasswordResponse = z.infer<typeof changePasswordResponseSchema>;

/**
 * Logout response schema
 * Backend returns: { success: true, message: '...' }
 */
export const logoutResponseSchema = authResponseWrapperSchema(
  z.object({})
);

/**
 * Type inference from schema
 */
export type LogoutResponse = z.infer<typeof logoutResponseSchema>;
