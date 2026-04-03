import { z } from 'zod';
import { paginationSchema } from '../common';

/**
 * Category schema - represents a course category
 */
export const categorySchema = z.object({
  id: z.string().uuid(),
  name: z.string(),
  slug: z.string().optional(),
  description: z.string().nullable().optional(),
  parent_id: z.string().uuid().nullable().optional(),
  sort_order: z.number().int().nonnegative().optional(),
  is_active: z.boolean().optional(),
  created_at: z.string().datetime().optional(),
  updated_at: z.string().datetime().optional(),
});

/**
 * CategoryListResponse - paginated list of categories
 */
export const categoryListResponseSchema = z.object({
  data: z.array(categorySchema),
  pagination: paginationSchema.optional(),
});

/**
 * CategoryDetailResponse - single category
 */
export const categoryDetailResponseSchema = z.object({
  category: categorySchema,
  subcategories: z.array(categorySchema).optional(),
});

// Type inference helpers
export type Category = z.infer<typeof categorySchema>;
export type CategoryListResponse = z.infer<typeof categoryListResponseSchema>;
export type CategoryDetailResponse = z.infer<typeof categoryDetailResponseSchema>;
