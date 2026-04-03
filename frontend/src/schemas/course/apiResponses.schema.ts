import { z } from 'zod';
import { paginationSchema } from '../common';

// User schema for nested User within Instructor
const userSchema = z.object({
  id: z.string().uuid(),
  name: z.string(),
  email: z.string().email(),
  avatar_url: z.string().url().nullable(),
  created_at: z.string().datetime(),
});

// Instructor schema
const instructorSchema = z.object({
  id: z.string().uuid(),
  user_id: z.string().uuid(),
  biography: z.string().nullable(),
  user: userSchema,
});

// Category schema
const categorySchema = z.object({
  id: z.string().uuid(),
  name: z.string(),
  parent_id: z.string().uuid().nullable(),
});

// Lesson schema
const lessonSchema = z.object({
  id: z.string().uuid(),
  title: z.string(),
  duration: z.number().int().nonnegative(),
  sort_order: z.number().int().nonnegative(),
  video_url: z.string().url().nullable(),
});

// Chapter schema
const chapterSchema = z.object({
  id: z.string().uuid(),
  title: z.string(),
  sort_order: z.number().int().nonnegative(),
  lessons: z.array(lessonSchema),
});

// Course schema (exported for use in other schemas)
export const courseSchema = z.object({
  id: z.string().uuid(),
  title: z.string(),
  description: z.string(),
  price: z.number().nonnegative(),
  thumbnail_url: z.string().url().nullable(),
  difficulty: z.enum(['beginner', 'intermediate', 'advanced']),
  language: z.string(),
  category_ids: z.array(z.string().uuid()),
  is_published: z.boolean(),
  created_at: z.string().datetime(),
});

// API Response Schemas

/**
 * CourseListResponse - Paginated list of courses
 */
export const courseListResponseSchema = z.object({
  data: z.array(courseSchema),
  pagination: paginationSchema,
});

/**
 * CourseDetailResponse - Single course with instructors, chapters
 */
export const courseDetailResponseSchema = z.object({
  course: courseSchema,
  instructors: z.array(instructorSchema),
  chapters: z.array(chapterSchema),
});

// Type inference helpers
export type Course = z.infer<typeof courseSchema>;
export type Chapter = z.infer<typeof chapterSchema>;
export type Lesson = z.infer<typeof lessonSchema>;
export type Instructor = z.infer<typeof instructorSchema>;
export type Category = z.infer<typeof categorySchema>;
export type CourseListResponse = z.infer<typeof courseListResponseSchema>;
export type CourseDetailResponse = z.infer<typeof courseDetailResponseSchema>;
