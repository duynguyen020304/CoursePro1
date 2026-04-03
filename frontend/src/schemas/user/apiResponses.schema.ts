import { z } from 'zod';
import { uuidSchema } from '../common';

/**
 * UserProfile API response schema
 * Represents the user data returned from /user/profile endpoint
 * Backend returns: { success, message, data: { user_id, first_name, last_name, email, role_id, profile_image, role, student, instructor } }
 */
export const userProfileSchema = z.object({
  user_id: z.string(),
  email: z.string().email(),
  first_name: z.string(),
  last_name: z.string(),
  role_id: z.string(),
  profile_image: z.string().nullable().optional(),
  role: z.object({
    role_id: z.string(),
    name: z.string(),
    permissions: z.array(z.object({ name: z.string() })).optional(),
  }).optional(),
  student: z.any().optional(),
  instructor: z.any().optional(),
});

/**
 * Type inference from schema
 */
export type UserProfile = z.infer<typeof userProfileSchema>;

/**
 * User API response wrapper schema
 */
const userApiResponseWrapperSchema = <T extends z.ZodTypeAny>(dataSchema: T) =>
  z.object({
    success: z.boolean(),
    message: z.string().optional(),
    data: dataSchema,
  });

/**
 * UpdateProfileResponse schema
 * Response returned after successfully updating a user profile
 * Backend returns: { success, message, data: { user_id, first_name, ... } }
 */
export const updateProfileResponseSchema = userApiResponseWrapperSchema(
  z.object({
    user_id: z.string(),
    first_name: z.string(),
    last_name: z.string(),
    email: z.string(),
    role_id: z.string(),
    profile_image: z.string().nullable().optional(),
  })
);

/**
 * Type inference from schema
 */
export type UpdateProfileResponse = z.infer<typeof updateProfileResponseSchema>;

/**
 * CurrentUserResponse schema
 * Response returned from /user endpoint with current authenticated user
 */
export const currentUserResponseSchema = userApiResponseWrapperSchema(
  z.object({
    user: userProfileSchema,
  })
);

/**
 * Type inference from schema
 */
export type CurrentUserResponse = z.infer<typeof currentUserResponseSchema>;
