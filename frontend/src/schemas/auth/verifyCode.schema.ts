import { z } from 'zod'

// VerifyCode form schema for email verification
export const verifyCodeSchema = z.object({
  email: z
    .string()
    .email('Please enter a valid email address'),
  code: z
    .string()
    .regex(/^[0-9]{6}$/, 'Code must be 6 digits'),
})

export type VerifyCodeFormData = z.infer<typeof verifyCodeSchema>
