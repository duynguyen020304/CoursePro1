import { z } from 'zod';
import { emailSchema, passwordSchema } from '../common';

/**
 * SignIn form schema using Zod
 * Composites emailSchema and passwordSchema from common schemas
 */
export const signinSchema = z.object({
  email: emailSchema,
  password: z
    .string()
    .min(1, 'Password is required')
    .pipe(passwordSchema),
});

/**
 * Type inference from schema
 */
export type SignInFormData = z.infer<typeof signinSchema>;

/**
 * Validate signin form data
 * Returns the parsed data on success, throws on failure
 */
export function validateSigninForm(data: unknown) {
  return signinSchema.parse(data);
}

/**
 * Safe validate signin form data
 * Returns result object with success flag
 */
export function safeValidateSigninForm(data: unknown) {
  return signinSchema.safeParse(data);
}
