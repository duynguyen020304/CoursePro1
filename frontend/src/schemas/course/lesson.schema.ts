import { z } from 'zod';

/**
 * Lesson schema - represents a course lesson
 */
export const lessonSchema = z.object({
  id: z.string().uuid(),
  title: z.string(),
  description: z.string().nullable().optional(),
  duration: z.number().int().nonnegative().optional(),
  sort_order: z.number().int().nonnegative(),
  video_url: z.string().url().nullable().optional(),
  is_preview: z.boolean().optional(),
  created_at: z.string().datetime().optional(),
  updated_at: z.string().datetime().optional(),
});

/**
 * Video schema - represents a video associated with a lesson
 */
export const videoSchema = z.object({
  id: z.string().uuid(),
  lesson_id: z.string().uuid(),
  url: z.string(),
  duration: z.number().int().nonnegative().optional(),
  quality: z.string().optional(),
  is_hd: z.boolean().optional(),
  created_at: z.string().datetime().optional(),
});

/**
 * Resource schema - represents a resource associated with a lesson
 */
export const resourceSchema = z.object({
  id: z.string().uuid(),
  lesson_id: z.string().uuid(),
  title: z.string(),
  url: z.string(),
  type: z.string().optional(),
  created_at: z.string().datetime().optional(),
});

/**
 * LessonResponse - single lesson response
 */
export const lessonResponseSchema = z.object({
  lesson: lessonSchema,
});

/**
 * LessonListResponse - list of lessons
 */
export const lessonListResponseSchema = z.object({
  data: z.array(lessonSchema),
});

/**
 * VideoListResponse - list of videos for a lesson
 */
export const videoListResponseSchema = z.object({
  data: z.array(videoSchema),
});

/**
 * ResourceListResponse - list of resources for a lesson
 */
export const resourceListResponseSchema = z.object({
  data: z.array(resourceSchema),
});

// Type inference helpers
export type Lesson = z.infer<typeof lessonSchema>;
export type Video = z.infer<typeof videoSchema>;
export type Resource = z.infer<typeof resourceSchema>;
export type LessonResponse = z.infer<typeof lessonResponseSchema>;
export type LessonListResponse = z.infer<typeof lessonListResponseSchema>;
export type VideoListResponse = z.infer<typeof videoListResponseSchema>;
export type ResourceListResponse = z.infer<typeof resourceListResponseSchema>;
