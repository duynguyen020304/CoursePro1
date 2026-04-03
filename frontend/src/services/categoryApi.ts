// Category API module with response validation
import api from './api';
import { z } from 'zod';
import { safeValidateResponse } from '../utils/apiValidator';
import {
  categoryListResponseSchema,
  categoryDetailResponseSchema,
  type CategoryListResponse,
  type CategoryDetailResponse,
} from '../schemas';

// Generic object schema for basic validation when specific schema isn't available
const genericObjectSchema = z.object({
  data: z.unknown(),
});

/**
 * Category API methods with Zod response validation
 */
export const categoryApi = {
  /**
   * List all categories with optional pagination
   */
  list: async (params?: { page?: number; per_page?: number; parent_id?: string }): Promise<CategoryListResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get('/categories', { params });
      const result = safeValidateResponse(categoryListResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[categoryApi.list] API call failed:', error);
      throw error;
    }
  },

  /**
   * Get a single category by ID
   */
  get: async (id: string): Promise<CategoryDetailResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get(`/categories/${id}`);
      const result = safeValidateResponse(categoryDetailResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[categoryApi.get] API call failed:', error);
      throw error;
    }
  },
};

export default categoryApi;
