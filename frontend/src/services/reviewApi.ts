// Review API module with response validation
import api from './api';
import { z } from 'zod';
import { safeValidateResponse } from '../utils/apiValidator';
import {
  reviewListResponseSchema,
  reviewResponseSchema,
  createReviewResponseSchema,
  type ReviewListResponse,
  type ReviewResponse,
  type CreateReviewResponse,
} from '../schemas';

/**
 * Review API methods with Zod response validation
 */
export const reviewApi = {
  /**
   * List reviews with optional filtering
   */
  list: async (params?: { course_id?: string; page?: number; per_page?: number }): Promise<ReviewListResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get('/reviews', { params });
      const result = safeValidateResponse(reviewListResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[reviewApi.list] API call failed:', error);
      throw error;
    }
  },

  /**
   * Create a new review
   */
  create: async (course_id: string, rating: number, review_text?: string): Promise<CreateReviewResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.post('/reviews', { course_id, rating, review_text });
      const result = safeValidateResponse(createReviewResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[reviewApi.create] API call failed:', error);
      throw error;
    }
  },

  /**
   * Update a review
   */
  update: async (reviewId: string, data: { rating?: number; review_text?: string }): Promise<ReviewResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.put(`/reviews/${reviewId}`, data);
      const result = safeValidateResponse(reviewResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[reviewApi.update] API call failed:', error);
      throw error;
    }
  },

  /**
   * Delete a review
   */
  delete: async (reviewId: string): Promise<{ success: boolean; message?: string } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.delete(`/reviews/${reviewId}`);
      return response.data;
    } catch (error) {
      console.error('[reviewApi.delete] API call failed:', error);
      throw error;
    }
  },
};

export default reviewApi;
