import { z } from 'zod';
import { emailSchema, passwordSchema } from '../common';

// Step 1: Send verification code - only email needed
export const step1Schema = z.object({
  email: emailSchema,
});

// Step 2: Verify code - 6 digit code
export const step2Schema = z.object({
  code: z
    .string()
    .regex(/^[0-9]{6}$/, 'Code must be exactly 6 digits'),
});

// Step 3: Reset password - new password + confirmation
export const step3Schema = z
  .object({
    code: z
      .string()
      .regex(/^[0-9]{6}$/, 'Code must be exactly 6 digits'),
    newPassword: passwordSchema,
    confirmPassword: z.string(),
  })
  .refine((data) => data.newPassword === data.confirmPassword, {
    message: 'Passwords do not match',
    path: ['confirmPassword'],
  });

// Discriminated union for step-aware validation
export const forgotPasswordSchema = z.discriminatedUnion('step', [
  step1Schema.extend({ step: z.literal(1) }),
  step2Schema.extend({ step: z.literal(2) }),
  step3Schema.extend({ step: z.literal(3) }),
]);

// TypeScript types inferred from schemas
export type Step1Data = z.infer<typeof step1Schema>;
export type Step2Data = z.infer<typeof step2Schema>;
export type Step3Data = z.infer<typeof step3Schema>;
export type ForgotPasswordData = z.infer<typeof forgotPasswordSchema>;
