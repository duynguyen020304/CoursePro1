import { z } from 'zod'

// Password validation schema - minimum 6 characters
export const passwordSchema = z
  .string()
  .min(6, 'Password must be at least 6 characters')
