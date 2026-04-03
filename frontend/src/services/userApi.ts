/**
 * User API Service with Zod Response Validation
 *
 * This module provides typed API methods for user-related endpoints.
 * Responses are validated against Zod schemas to ensure type safety.
 */

import { z } from 'zod';
import {
  userProfileSchema,
  currentUserResponseSchema,
  updateProfileResponseSchema,
  type UserProfile,
  type CurrentUserResponse,
  type UpdateProfileResponse,
} from '../schemas/user/apiResponses.schema';

// Import the base API client
import api from './api';

/**
 * Validates API response data against a Zod schema.
 * Returns the validated data on success, or raw data with warning on failure.
 * This preserves existing behavior while adding runtime validation.
 */
function validateResponse<T extends z.ZodType>(
  schema: T,
  data: unknown,
  responseKey: string
): z.infer<T> {
  const result = schema.safeParse(data);
  if (!result.success) {
    console.warn(`[userApi] Response validation failed for ${responseKey}:`, result.error.issues);
    // Return raw data cast to expected type - preserves existing behavior
    return data as z.infer<T>;
  }
  return result.data;
}

export const userApi = {
  /**
   * Get current authenticated user
   * GET /user
   */
  current: () => {
    return api.get<CurrentUserResponse>('/user').then((response) => {
      return validateResponse(
        currentUserResponseSchema,
        response.data,
        'currentUserResponse'
      );
    });
  },

  /**
   * Get user profile
   * GET /user/profile
   */
  profile: () => {
    return api.get<UserProfile>('/user/profile').then((response) => {
      return validateResponse(
        userProfileSchema,
        response.data,
        'userProfile'
      );
    });
  },

  /**
   * Get user profile (alias)
   * GET /user/profile
   */
  getProfile: () => {
    return api.get<UserProfile>('/user/profile').then((response) => {
      return validateResponse(
        userProfileSchema,
        response.data,
        'userProfile'
      );
    });
  },

  /**
   * Update user profile
   * PUT /user/profile
   */
  updateProfile: (data: Record<string, unknown>) => {
    return api.put<UpdateProfileResponse>('/user/profile', data).then((response) => {
      return validateResponse(
        updateProfileResponseSchema,
        response.data,
        'updateProfileResponse'
      );
    });
  },
};

export default userApi;