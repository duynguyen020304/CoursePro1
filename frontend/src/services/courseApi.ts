/**
 * Course API Service with Zod Response Validation
 *
 * This module provides typed API methods for course-related endpoints.
 * Responses are validated against Zod schemas to ensure type safety.
 */

import { z } from 'zod';
import {
  courseListResponseSchema,
  courseDetailResponseSchema,
  type CourseListResponse,
  type CourseDetailResponse,
} from '../schemas/course/apiResponses.schema';

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
    console.warn(`[courseApi] Response validation failed for ${responseKey}:`, result.error.issues);
    // Return raw data cast to expected type - preserves existing behavior
    return data as z.infer<T>;
  }
  return result.data;
}

export const courseApi = {
  /**
   * List courses with pagination
   * GET /courses
   */
  list: (params?: Record<string, unknown>) => {
    return api.get<CourseListResponse>('/courses', { params }).then((response) => {
      return validateResponse(
        courseListResponseSchema,
        response.data,
        'courseListResponse'
      );
    });
  },

  /**
   * Get single course by ID
   * GET /courses/:id
   */
  get: (id: string) => {
    return api.get<CourseDetailResponse>(`/courses/${id}`).then((response) => {
      return validateResponse(
        courseDetailResponseSchema,
        response.data,
        'courseDetailResponse'
      );
    });
  },

  /**
   * Search courses
   * GET /courses/search?q=query
   */
  search: (q: string) => {
    return api.get('/courses/search', { params: { q } });
  },

  // === Course Instructors ===

  getInstructors: (courseId: string) => {
    return api.get(`/courses/${courseId}/instructors`);
  },

  addInstructor: (courseId: string, instructor_id: string) => {
    return api.post(`/courses/${courseId}/instructors`, { instructor_id });
  },

  removeInstructor: (courseId: string, instructorId: string) => {
    return api.delete(`/courses/${courseId}/instructors/${instructorId}`);
  },

  // === Course Categories ===

  getCategories: (courseId: string) => {
    return api.get(`/courses/${courseId}/categories`);
  },

  addCategory: (courseId: string, category_id: string) => {
    return api.post(`/courses/${courseId}/categories`, { category_id });
  },

  removeCategory: (courseId: string, categoryId: string) => {
    return api.delete(`/courses/${courseId}/categories/${categoryId}`);
  },

  // === Course Images ===

  getImages: (courseId: string) => {
    return api.get(`/courses/${courseId}/images`);
  },

  addImage: (courseId: string, image_url: string, is_primary: boolean, sort_order: number) => {
    return api.post(`/courses/${courseId}/images`, { image_url, is_primary, sort_order });
  },

  updateImage: (courseId: string, imageId: string, data: Record<string, unknown>) => {
    return api.put(`/courses/${courseId}/images/${imageId}`, data);
  },

  deleteImage: (courseId: string, imageId: string) => {
    return api.delete(`/courses/${courseId}/images/${imageId}`);
  },

  // === Course Objectives ===

  getObjectives: (courseId: string) => {
    return api.get(`/courses/${courseId}/objectives`);
  },

  addObjective: (courseId: string, objective: string, sort_order: number) => {
    return api.post(`/courses/${courseId}/objectives`, { objective, sort_order });
  },

  updateObjective: (courseId: string, objectiveId: string, data: Record<string, unknown>) => {
    return api.put(`/courses/${courseId}/objectives/${objectiveId}`, data);
  },

  deleteObjective: (courseId: string, objectiveId: string) => {
    return api.delete(`/courses/${courseId}/objectives/${objectiveId}`);
  },

  // === Course Requirements ===

  getRequirements: (courseId: string) => {
    return api.get(`/courses/${courseId}/requirements`);
  },

  addRequirement: (courseId: string, requirement: string, sort_order: number) => {
    return api.post(`/courses/${courseId}/requirements`, { requirement, sort_order });
  },

  updateRequirement: (courseId: string, requirementId: string, data: Record<string, unknown>) => {
    return api.put(`/courses/${courseId}/requirements/${requirementId}`, data);
  },

  deleteRequirement: (courseId: string, requirementId: string) => {
    return api.delete(`/courses/${courseId}/requirements/${requirementId}`);
  },

  // === Course Chapters ===

  getChapters: (courseId: string) => {
    return api.get(`/courses/${courseId}/chapters`);
  },

  addChapter: (courseId: string, data: Record<string, unknown>) => {
    return api.post(`/courses/${courseId}/chapters`, data);
  },

  updateChapter: (courseId: string, chapterId: string, data: Record<string, unknown>) => {
    return api.put(`/courses/${courseId}/chapters/${chapterId}`, data);
  },

  deleteChapter: (courseId: string, chapterId: string) => {
    return api.delete(`/courses/${courseId}/chapters/${chapterId}`);
  },

  // === Course Lessons ===

  getLessons: (courseId: string, chapterId: string) => {
    return api.get(`/courses/${courseId}/chapters/${chapterId}/lessons`);
  },

  addLesson: (courseId: string, chapterId: string, data: Record<string, unknown>) => {
    return api.post(`/courses/${courseId}/chapters/${chapterId}/lessons`, data);
  },
};

export default courseApi;