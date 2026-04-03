import { z } from 'zod'

// SignUp form validation schema with password confirmation
export const signupSchema = z
  .object({
    first_name: z
      .string()
      .min(1, 'First name is required'),
    last_name: z
      .string()
      .min(1, 'Last name is required'),
    email: z
      .string()
      .min(1, 'Email is required')
      .email('Invalid email address'),
    password: z
      .string()
      .min(1, 'Password is required')
      .min(6, 'Password must be at least 6 characters'),
    password_confirmation: z
      .string()
      .min(1, 'Please confirm your password'),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
  })

// Type inference
export type SignUpFormData = z.infer<typeof signupSchema>
