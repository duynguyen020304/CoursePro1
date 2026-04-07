import { z } from 'zod';
import { uuidSchema } from '../common';

/**
 * CreateCourse form schema using Zod
 * Validates course creation form data
 */
export const createCourseSchema = z.object({
  title: z
    .string()
    .min(1, 'Title is required'),
  description: z
    .string()
    .min(10, 'Description must be at least 10 characters'),
  price: z
    .string()
    .refine(
      (val) => !isNaN(parseFloat(val)) && parseFloat(val) >= 0,
      'Price must be a non-negative number'
    ),
  category_ids: z.array(uuidSchema).optional(),
  thumbnail: z.union([z.string(), z.instanceof(File)]).optional(),
});

/**
 * Type inference from schema
 */
export type CreateCourseFormData = z.infer<typeof createCourseSchema>;

/**
 * Validate create course form data
 * Returns the parsed data on success, throws on failure
 */
export function validateCreateCourseForm(data: unknown) {
  return createCourseSchema.parse(data);
}

/**
 * Safe validate create course form data
 * Returns result object with success flag
 */
export function safeValidateCreateCourseForm(data: unknown) {
  return createCourseSchema.safeParse(data);
}
