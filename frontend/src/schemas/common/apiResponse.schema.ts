import { z } from 'zod'

// Standard API response envelope - matches backend Controller helpers:
// Success: { success: true, message: string, data: T }
// Failure: { success: false, message: string, data: null }
export const apiResponseSchema = z.object({
  success: z.boolean(),
  message: z.string(),
  data: z.unknown().nullable().refine(val => val !== undefined, {
    message: 'data must not be undefined',
  }),
})

// Type inference helpers
export type ApiSuccessResponse = z.infer<typeof apiResponseSchema>
