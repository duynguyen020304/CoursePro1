import { z } from 'zod';

const instructorSchema = z.object({
  instructor_id: z.string(),
  user_id: z.string(),
  biography: z.string().nullable().optional(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  user: z.object({
    user_id: z.string(),
    first_name: z.string(),
    last_name: z.string(),
    email: z.string().optional(),
    profile_image: z.string().nullable().optional(),
    role_id: z.string().optional(),
  }).optional(),
});

const instructorCourseSchema = z.object({
  course_id: z.string(),
  title: z.string(),
  description: z.string().nullable().optional(),
  price: z.number().nonnegative(),
  thumbnail_url: z.string().nullable().optional(),
  difficulty: z.string().nullable().optional(),
  language: z.string().nullable().optional(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  students_count: z.number().int().nonnegative().optional(),
  reviews_count: z.number().int().nonnegative().optional(),
});

export const instructorProfileResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: instructorSchema.extend({
    courses: z.array(instructorCourseSchema).optional(),
  }),
});

export const instructorStatsResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.object({
    total_students: z.number().int().nonnegative(),
    total_courses: z.number().int().nonnegative(),
    total_revenue: z.number().nonnegative(),
  }).passthrough(),
});

export const instructorCourseListResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.object({
    instructor: instructorSchema,
    courses: z.array(instructorCourseSchema).optional(),
  }),
});

export const instructorCreateResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: instructorSchema,
});

export type InstructorProfile = z.infer<typeof instructorProfileResponseSchema>;
export type InstructorStats = z.infer<typeof instructorStatsResponseSchema>;
export type InstructorCourseList = z.infer<typeof instructorCourseListResponseSchema>;
export type InstructorCreateResponse = z.infer<typeof instructorCreateResponseSchema>;
