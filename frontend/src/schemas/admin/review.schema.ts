import { z } from 'zod';
import { paginationSchema } from '../common';

/**
 * AdminReview schema - represents a course review (admin context)
 */
export const adminReviewSchema = z.object({
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
 * AdminReviewListResponse - paginated list of reviews
 */
export const adminReviewListResponseSchema = z.object({
  data: z.array(adminReviewSchema),
  pagination: paginationSchema.optional(),
});

/**
 * AdminReviewResponse - single review response
 */
export const adminReviewResponseSchema = z.object({
  review: adminReviewSchema,
});

/**
 * AdminCreateReviewResponse - response after creating a review
 */
export const adminCreateReviewResponseSchema = z.object({
  review: adminReviewSchema,
  message: z.string().optional(),
});

// Type inference helpers
export type AdminReview = z.infer<typeof adminReviewSchema>;
export type AdminReviewListResponse = z.infer<typeof adminReviewListResponseSchema>;
export type AdminReviewResponse = z.infer<typeof adminReviewResponseSchema>;
export type AdminCreateReviewResponse = z.infer<typeof adminCreateReviewResponseSchema>;
