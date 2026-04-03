import { z } from 'zod'

// Email validation schema with custom error message
export const emailSchema = z
  .string()
  .email('Please enter a valid email address')
