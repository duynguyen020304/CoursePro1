import { z } from 'zod';
import { paginationSchema } from '../common';

export const reviewSchema = z.object({
  review_id: z.string(),
  user_id: z.string(),
  course_id: z.string(),
  rating: z.number().int().min(1).max(5),
  review_text: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  user: z.object({
    user_id: z.string(),
    first_name: z.string(),
    last_name: z.string(),
    profile_image: z.string().nullable().optional(),
  }).optional(),
});

export const reviewListResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.object({
    current_page: z.number().int().positive(),
    data: z.array(reviewSchema),
    total: z.number().int().nonnegative(),
    per_page: z.number().int().positive(),
  }).passthrough(),
});

export const reviewCreateResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: reviewSchema,
});

export const reviewUpdateResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: reviewSchema,
});

export const reviewDeleteResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
});

export type Review = z.infer<typeof reviewSchema>;
export type ReviewListResponse = z.infer<typeof reviewListResponseSchema>;
export type ReviewCreateResponse = z.infer<typeof reviewCreateResponseSchema>;
export type ReviewUpdateResponse = z.infer<typeof reviewUpdateResponseSchema>;
export type ReviewDeleteResponse = z.infer<typeof reviewDeleteResponseSchema>;
