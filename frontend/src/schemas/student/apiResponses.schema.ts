import { z } from 'zod';

const studentSchema = z.object({
  student_id: z.string(),
  user_id: z.string(),
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

const purchasedCourseSchema = z.object({
  course_id: z.string(),
  title: z.string(),
  description: z.string().nullable().optional(),
  price: z.number().nonnegative(),
  thumbnail_url: z.string().nullable().optional(),
});

export const studentProfileSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.object({
    student: studentSchema,
    purchased_courses: z.array(purchasedCourseSchema).optional(),
  }),
});

export const hasPurchasedSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.object({
    has_purchased: z.boolean(),
  }),
});

export type StudentProfile = z.infer<typeof studentProfileSchema>;
export type HasPurchased = z.infer<typeof hasPurchasedSchema>;
