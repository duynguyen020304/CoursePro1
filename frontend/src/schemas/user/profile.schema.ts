import { z } from 'zod';
import { emailSchema } from '../common';

/**
 * Profile form schema using Zod
 * Used for validating profile update form data
 */
export const profileSchema = z.object({
  first_name: z
    .string()
    .min(1, 'First name is required')
    .max(100, 'First name must be less than 100 characters'),
  last_name: z
    .string()
    .min(1, 'Last name is required')
    .max(100, 'Last name must be less than 100 characters'),
  email: emailSchema,
  profile_image: z
    .string()
    .url('Please enter a valid URL')
    .optional()
    .or(z.literal('')),
});

/**
 * Type inference from schema
 */
export type ProfileFormData = z.infer<typeof profileSchema>;

/**
 * Validate profile form data
 * Returns the parsed data on success, throws on failure
 */
export function validateProfileForm(data: unknown) {
  return profileSchema.parse(data);
}

/**
 * Safe validate profile form data
 * Returns result object with success flag
 */
export function safeValidateProfileForm(data: unknown) {
  return profileSchema.safeParse(data);
}
