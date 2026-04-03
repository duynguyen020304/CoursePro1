import { z } from 'zod'

// Reset Password Schema
// Validates the password reset form data including token from URL

export const resetPasswordSchema = z
  .object({
    // Token from URL params - validated separately before form submission
    token: z.string().min(1, 'Token is required'),
    
    // Email from URL params
    email: z
      .string()
      .email('Please enter a valid email address'),
    
    // New password - min 6 characters
    password: z
      .string()
      .min(6, 'Password must be at least 6 characters'),
    
    // Password confirmation - must match password
    password_confirmation: z
      .string()
      .min(1, 'Please confirm your password'),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
  })

// Type inference
export type ResetPasswordFormData = z.infer<typeof resetPasswordSchema>

// Separate schema for URL param validation (token and email)
export const resetPasswordParamsSchema = z.object({
  token: z.string().min(1, 'Token is required'),
  email: z.string().email('Please enter a valid email address'),
})

export type ResetPasswordParams = z.infer<typeof resetPasswordParamsSchema>
