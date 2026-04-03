import { z } from 'zod';
import { paginationSchema } from '../common';

/**
 * Public Instructor schema - represents an instructor visible on public pages
 */
export const publicInstructorSchema = z.object({
  id: z.string().uuid(),
  user_id: z.string().uuid(),
  biography: z.string().nullable().optional(),
  user: z.object({
    id: z.string().uuid(),
    name: z.string(),
    email: z.string().email(),
    avatar_url: z.string().url().nullable().optional(),
    created_at: z.string().datetime().optional(),
  }),
  courses_count: z.number().int().nonnegative().optional(),
  students_count: z.number().int().nonnegative().optional(),
  rating: z.number().nonnegative().optional(),
});

/**
 * InstructorListResponse - paginated list of public instructors
 */
export const instructorListResponseSchema = z.object({
  data: z.array(publicInstructorSchema),
  pagination: paginationSchema.optional(),
});

/**
 * InstructorDetailResponse - single instructor profile
 */
export const instructorDetailResponseSchema = z.object({
  instructor: publicInstructorSchema,
  courses: z.array(z.object({
    id: z.string().uuid(),
    title: z.string(),
  })).optional(),
});

// Type inference helpers
export type PublicInstructor = z.infer<typeof publicInstructorSchema>;
export type InstructorListResponse = z.infer<typeof instructorListResponseSchema>;
export type InstructorDetailResponse = z.infer<typeof instructorDetailResponseSchema>;
