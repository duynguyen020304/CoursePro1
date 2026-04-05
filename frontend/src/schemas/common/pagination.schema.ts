import { z } from 'zod'

// Flat pagination schema - matches backend paginated() helper contract:
// { success, message, data: [...items], hasNextPage, hasPreviousPage, totalPage, totalItem }
export const paginationSchema = z.object({
  hasNextPage: z.boolean(),
  hasPreviousPage: z.boolean(),
  totalPage: z.number(),
  totalItem: z.number(),
})

// Legacy pagination schema (deprecated - for reference only)
// export const legacyPaginationSchema = z.object({
//   data: z.array(z.unknown()),
//   total: z.number(),
//   page: z.number(),
// })

// Type inference helper
export type PaginationSchema = z.infer<typeof paginationSchema>
