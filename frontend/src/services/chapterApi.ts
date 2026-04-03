// Chapter API module with response validation
import api from './api';
import { z } from 'zod';
import { safeValidateResponse } from '../utils/apiValidator';
import {
  chapterListResponseSchema,
  chapterDetailResponseSchema,
  chapterResponseSchema,
  type ChapterListResponse,
  type ChapterDetailResponse,
  type ChapterResponse,
} from '../schemas';

/**
 * Chapter API methods with Zod response validation
 */
export const chapterApi = {
  /**
   * List all chapters for a course
   */
  list: async (courseId: string): Promise<ChapterListResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get(`/courses/${courseId}/chapters`);
      const result = safeValidateResponse(chapterListResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[chapterApi.list] API call failed:', error);
      throw error;
    }
  },

  /**
   * Create a new chapter for a course
   */
  create: async (courseId: string, data: { title: string; description?: string; sort_order?: number }): Promise<ChapterResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.post(`/courses/${courseId}/chapters`, data);
      const result = safeValidateResponse(chapterResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[chapterApi.create] API call failed:', error);
      throw error;
    }
  },

  /**
   * Update a chapter
   */
  update: async (courseId: string, chapterId: string, data: { title?: string; description?: string; sort_order?: number }): Promise<ChapterResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.put(`/courses/${courseId}/chapters/${chapterId}`, data);
      const result = safeValidateResponse(chapterResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[chapterApi.update] API call failed:', error);
      throw error;
    }
  },

  /**
   * Delete a chapter
   */
  delete: async (courseId: string, chapterId: string): Promise<{ success: boolean; message?: string } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.delete(`/courses/${courseId}/chapters/${chapterId}`);
      return response.data;
    } catch (error) {
      console.error('[chapterApi.delete] API call failed:', error);
      throw error;
    }
  },
};

export default chapterApi;
