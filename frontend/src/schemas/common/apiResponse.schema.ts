import { z } from 'zod'

// API response wrapper - simple object for easy .data access
export const apiResponseSchema = z.object({
  success: z.boolean(),
  data: z.unknown().optional(),
  message: z.string().optional(),
  error: z.string().optional(),
})

// Type inference helpers
export type ApiSuccessResponse = z.infer<typeof apiResponseSchema>
