import { z } from 'zod';

/**
 * Revenue date range filter schema using Zod v4
 * Validates start_date and end_date with cross-field validation
 */

// Date range schema with end_date >= start_date validation
export const revenueDateRangeSchema = z
  .object({
    start_date: z.string().min(1, 'Start date is required'),
    end_date: z.string().min(1, 'End date is required'),
  })
  .refine(
    (data) => {
      // Only validate if both dates are provided and valid
      if (!data.start_date || !data.end_date) return true;
      const start = new Date(data.start_date);
      const end = new Date(data.end_date);
      return !isNaN(start.getTime()) && !isNaN(end.getTime()) && end >= start;
    },
    {
      message: 'End date must be greater than or equal to start date',
      path: ['end_date'],
    }
  );

/**
 * Type inference from schema
 */
export type RevenueDateRangeFormData = z.infer<typeof revenueDateRangeSchema>;

/**
 * Safe validate revenue date range data
 * Returns result object with success flag
 */
export function safeValidateRevenueDateRange(data: unknown) {
  return revenueDateRangeSchema.safeParse(data);
}
