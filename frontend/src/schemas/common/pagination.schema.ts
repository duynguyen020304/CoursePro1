import { z } from 'zod'

// Generic pagination schema
export const paginationSchema = z.object({
  data: z.array(z.unknown()),
  total: z.number(),
  page: z.number(),
})

// Type inference helper
export type PaginationSchema = z.infer<typeof paginationSchema>
