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
  }).passthrough().optional(),
}).passthrough();

const courseImageSchema = z.object({
  image_id: z.string().optional(),
  image_path: z.string().nullable().optional(),
  is_primary: z.union([z.boolean(), z.number()]).optional().transform(v => v === true || v === 1),
  sort_order: z.number().optional(),
}).passthrough();

const courseCategorySchema = z.object({
  category_id: z.string().optional(),
  name: z.string().optional(),
}).passthrough();

const instructorCourseSchema = z.object({
  course_id: z.string(),
  title: z.string(),
  description: z.string().nullable().optional(),
  price: z.union([z.number().nonnegative(), z.string()]),
  difficulty: z.string().nullable().optional(),
  language: z.string().nullable().optional(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  images: z.array(courseImageSchema).optional(),
  categories: z.array(courseCategorySchema).optional(),
});

const instructorCourseStatsSchema = z.object({
  total_students: z.number().int().nonnegative(),
  total_revenue: z.union([z.number().nonnegative(), z.string()]),
  total_reviews: z.number().int().nonnegative(),
  average_rating: z.number().nonnegative(),
  total_lessons: z.number().int().nonnegative(),
});

const instructorCourseItemSchema = z.object({
  course: instructorCourseSchema,
  stats: instructorCourseStatsSchema,
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
  data: z.array(instructorCourseItemSchema),
});

export const instructorCreateResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: instructorCourseSchema,
});

export type InstructorProfile = z.infer<typeof instructorProfileResponseSchema>;
export type InstructorStats = z.infer<typeof instructorStatsResponseSchema>;
export type InstructorCourseList = z.infer<typeof instructorCourseListResponseSchema>;
export type InstructorCreateResponse = z.infer<typeof instructorCreateResponseSchema>;
