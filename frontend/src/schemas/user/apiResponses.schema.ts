import { z } from 'zod';
import { uuidSchema } from '../common';

/**
 * UserProfile API response schema
 * Represents the user data returned from /user and /user/profile endpoints
 */
export const userProfileSchema = z.object({
  id: uuidSchema,
  email: z.string().email(),
  first_name: z.string(),
  last_name: z.string(),
  role: z.string(),
  avatar_url: z.string().url().nullable().optional(),
  phone: z.string().nullable().optional(),
  bio: z.string().nullable().optional(),
  created_at: z.string().datetime(),
});

/**
 * Type inference from schema
 */
export type UserProfile = z.infer<typeof userProfileSchema>;

/**
 * UpdateProfileResponse schema
 * Response returned after successfully updating a user profile
 */
export const updateProfileResponseSchema = z.object({
  user: userProfileSchema,
  message: z.string(),
});

/**
 * Type inference from schema
 */
export type UpdateProfileResponse = z.infer<typeof updateProfileResponseSchema>;

/**
 * CurrentUserResponse schema
 * Response returned from /user endpoint with current authenticated user
 */
export const currentUserResponseSchema = z.object({
  user: userProfileSchema,
});

/**
 * Type inference from schema
 */
export type CurrentUserResponse = z.infer<typeof currentUserResponseSchema>;
