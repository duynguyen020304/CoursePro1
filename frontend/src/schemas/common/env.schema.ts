import { z } from 'zod'

// Environment variable validation schema
// Validates all VITE_* environment variables at runtime

/**
 * Schema for VITE_API_URL - the base API endpoint
 * Must be a valid URL string
 */
export const envApiUrlSchema = z
  .string({
    error: (issue) => issue.input === undefined 
      ? 'VITE_API_URL is required' 
      : 'VITE_API_URL must be a string',
  })
  .min(1, 'VITE_API_URL cannot be empty')

/**
 * Schema for VITE_GOOGLE_CLIENT_ID - Google OAuth client ID
 * Must be a non-empty string (Google client IDs are typically long alphanumeric strings)
 */
export const envGoogleClientIdSchema = z
  .string({
    error: (issue) => issue.input === undefined 
      ? 'VITE_GOOGLE_CLIENT_ID is required' 
      : 'VITE_GOOGLE_CLIENT_ID must be a string',
  })
  .min(1, 'VITE_GOOGLE_CLIENT_ID cannot be empty')

/**
 * Combined environment variables schema
 * Validates all required VITE_* variables
 */
export const envSchema = z.object({
  VITE_API_URL: envApiUrlSchema,
  VITE_GOOGLE_CLIENT_ID: envGoogleClientIdSchema,
})

/**
 * Type inference helper - infer TypeScript type from schema
 */
export type EnvVars = z.infer<typeof envSchema>
