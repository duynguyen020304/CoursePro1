import { z } from 'zod';
import { lessonSchema } from './lesson.schema';

/**
 * Chapter schema - represents a course chapter
 */
export const chapterSchema = z.object({
  id: z.string().uuid(),
  title: z.string(),
  description: z.string().nullable().optional(),
  sort_order: z.number().int().nonnegative(),
  course_id: z.string().uuid().optional(),
  created_at: z.string().datetime().optional(),
  updated_at: z.string().datetime().optional(),
});

/**
 * ChapterWithLessons schema - chapter with its lessons
 */
export const chapterWithLessonsSchema = chapterSchema.extend({
  lessons: z.array(lessonSchema),
});

/**
 * ChapterListResponse - list of chapters
 */
export const chapterListResponseSchema = z.object({
  data: z.array(chapterSchema),
});

/**
 * ChapterDetailResponse - single chapter with lessons
 */
export const chapterDetailResponseSchema = z.object({
  chapter: chapterWithLessonsSchema,
});

/**
 * ChapterResponse - single chapter
 */
export const chapterResponseSchema = z.object({
  chapter: chapterSchema,
});

// Type inference helpers
export type Chapter = z.infer<typeof chapterSchema>;
export type ChapterWithLessons = z.infer<typeof chapterWithLessonsSchema>;
export type ChapterListResponse = z.infer<typeof chapterListResponseSchema>;
export type ChapterDetailResponse = z.infer<typeof chapterDetailResponseSchema>;
export type ChapterResponse = z.infer<typeof chapterResponseSchema>;
