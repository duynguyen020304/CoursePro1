import { z } from 'zod';
import { paginationSchema } from '../common';

/**
 * Review schema - represents a course review
 */
export const reviewSchema = z.object({
  id: z.string().uuid(),
  user_id: z.string().uuid(),
  course_id: z.string().uuid(),
  rating: z.number().int().min(1).max(5),
  review_text: z.string().nullable().optional(),
  created_at: z.string().datetime().optional(),
  updated_at: z.string().datetime().optional(),
  user: z.object({
    id: z.string().uuid(),
    name: z.string(),
    avatar_url: z.string().url().nullable().optional(),
  }).optional(),
  course: z.object({
    id: z.string().uuid(),
    title: z.string(),
  }).optional(),
});

/**
 * ReviewListResponse - paginated list of reviews
 */
export const reviewListResponseSchema = z.object({
  data: z.array(reviewSchema),
  pagination: paginationSchema.optional(),
});

/**
 * ReviewResponse - single review response
 */
export const reviewResponseSchema = z.object({
  review: reviewSchema,
});

/**
 * CreateReviewResponse - response after creating a review
 */
export const createReviewResponseSchema = z.object({
  review: reviewSchema,
  message: z.string().optional(),
});

// Type inference helpers
export type Review = z.infer<typeof reviewSchema>;
export type ReviewListResponse = z.infer<typeof reviewListResponseSchema>;
export type ReviewResponse = z.infer<typeof reviewResponseSchema>;
export type CreateReviewResponse = z.infer<typeof createReviewResponseSchema>;
