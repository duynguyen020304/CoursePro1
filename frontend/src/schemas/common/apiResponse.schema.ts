import { z } from 'zod'

// Discriminated union for API responses
export const apiResponseSchema = z.discriminatedUnion('success', [
  z.object({
    success: z.literal(true),
    data: z.unknown(),
    message: z.string().optional(),
  }),
  z.object({
    success: z.literal(false),
    error: z.string(),
    message: z.string().optional(),
  }),
])

// Type inference helpers
export type ApiSuccessResponse = z.infer<typeof apiResponseSchema>
