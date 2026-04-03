// Instructor Public API module with response validation
import api from './api';
import { z } from 'zod';
import { safeValidateResponse } from '../utils/apiValidator';
import {
  instructorListResponseSchema,
  instructorDetailResponseSchema,
  type InstructorListResponse,
  type InstructorDetailResponse,
} from '../schemas';

// Generic object schema for basic validation
const genericObjectSchema = z.object({
  data: z.unknown(),
});

/**
 * Instructor Public API methods with Zod response validation
 */
export const instructorPublicApi = {
  /**
   * List all public instructors with optional pagination
   */
  list: async (params?: { page?: number; per_page?: number; search?: string }): Promise<InstructorListResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get('/instructors', { params });
      const result = safeValidateResponse(instructorListResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[instructorPublicApi.list] API call failed:', error);
      throw error;
    }
  },

  /**
   * Get a single instructor profile by ID
   */
  get: async (id: string): Promise<InstructorDetailResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get(`/instructors/${id}`);
      const result = safeValidateResponse(instructorDetailResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[instructorPublicApi.get] API call failed:', error);
      throw error;
    }
  },
};

export default instructorPublicApi;
