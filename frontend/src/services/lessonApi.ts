// Lesson API module with response validation
import api from './api';
import { z } from 'zod';
import { safeValidateResponse } from '../utils/apiValidator';
import {
  lessonResponseSchema,
  lessonListResponseSchema,
  videoListResponseSchema,
  resourceListResponseSchema,
  type LessonResponse,
  type LessonListResponse,
  type VideoListResponse,
  type ResourceListResponse,
} from '../schemas';

/**
 * Lesson API methods with Zod response validation
 */
export const lessonApi = {
  /**
   * Get a single lesson by ID
   */
  get: async (lessonId: string): Promise<LessonResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get(`/lessons/${lessonId}`);
      const result = safeValidateResponse(lessonResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[lessonApi.get] API call failed:', error);
      throw error;
    }
  },

  /**
   * Update a lesson
   */
  update: async (lessonId: string, data: { title?: string; description?: string; sort_order?: number; is_preview?: boolean }): Promise<LessonResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.put(`/lessons/${lessonId}`, data);
      const result = safeValidateResponse(lessonResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[lessonApi.update] API call failed:', error);
      throw error;
    }
  },

  /**
   * Delete a lesson
   */
  delete: async (lessonId: string): Promise<{ success: boolean; message?: string } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.delete(`/lessons/${lessonId}`);
      return response.data;
    } catch (error) {
      console.error('[lessonApi.delete] API call failed:', error);
      throw error;
    }
  },

  /**
   * Get videos for a lesson
   */
  getVideos: async (lessonId: string): Promise<VideoListResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get(`/lessons/${lessonId}/videos`);
      const result = safeValidateResponse(videoListResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[lessonApi.getVideos] API call failed:', error);
      throw error;
    }
  },

  /**
   * Add a video to a lesson
   */
  addVideo: async (lessonId: string, data: { url: string; duration?: number; quality?: string; is_hd?: boolean }): Promise<{ video: unknown } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.post(`/lessons/${lessonId}/videos`, data);
      return response.data;
    } catch (error) {
      console.error('[lessonApi.addVideo] API call failed:', error);
      throw error;
    }
  },

  /**
   * Update a video
   */
  updateVideo: async (lessonId: string, videoId: string, data: { url?: string; duration?: number; quality?: string; is_hd?: boolean }): Promise<{ video: unknown } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.put(`/lessons/${lessonId}/videos/${videoId}`, data);
      return response.data;
    } catch (error) {
      console.error('[lessonApi.updateVideo] API call failed:', error);
      throw error;
    }
  },

  /**
   * Delete a video
   */
  deleteVideo: async (lessonId: string, videoId: string): Promise<{ success: boolean; message?: string } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.delete(`/lessons/${lessonId}/videos/${videoId}`);
      return response.data;
    } catch (error) {
      console.error('[lessonApi.deleteVideo] API call failed:', error);
      throw error;
    }
  },

  /**
   * Get resources for a lesson
   */
  getResources: async (lessonId: string): Promise<ResourceListResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get(`/lessons/${lessonId}/resources`);
      const result = safeValidateResponse(resourceListResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[lessonApi.getResources] API call failed:', error);
      throw error;
    }
  },

  /**
   * Add a resource to a lesson
   */
  addResource: async (lessonId: string, data: { title: string; url: string; type?: string }): Promise<{ resource: unknown } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.post(`/lessons/${lessonId}/resources`, data);
      return response.data;
    } catch (error) {
      console.error('[lessonApi.addResource] API call failed:', error);
      throw error;
    }
  },

  /**
   * Update a resource
   */
  updateResource: async (lessonId: string, resourceId: string, data: { title?: string; url?: string; type?: string }): Promise<{ resource: unknown } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.put(`/lessons/${lessonId}/resources/${resourceId}`, data);
      return response.data;
    } catch (error) {
      console.error('[lessonApi.updateResource] API call failed:', error);
      throw error;
    }
  },

  /**
   * Delete a resource
   */
  deleteResource: async (lessonId: string, resourceId: string): Promise<{ success: boolean; message?: string } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.delete(`/lessons/${lessonId}/resources/${resourceId}`);
      return response.data;
    } catch (error) {
      console.error('[lessonApi.deleteResource] API call failed:', error);
      throw error;
    }
  },
};

export default lessonApi;
