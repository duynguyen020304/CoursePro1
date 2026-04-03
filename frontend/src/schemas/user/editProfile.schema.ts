import { z } from 'zod';
import { emailSchema } from '../common';

/**
 * EditProfile form schema using Zod
 * Validates user profile editing form data
 */
export const editProfileSchema = z.object({
  first_name: z
    .string()
    .min(1, 'First name is required'),
  last_name: z
    .string()
    .min(1, 'Last name is required'),
  email: emailSchema,
  phone: z
    .string()
    .optional()
    .or(z.literal('')),
  bio: z
    .string()
    .max(500, 'Bio must be 500 characters or less')
    .optional()
    .or(z.literal('')),
  profile_image: z
    .string()
    .url('Please enter a valid URL')
    .optional()
    .or(z.literal('')),
});

/**
 * Type inference from schema
 */
export type EditProfileFormData = z.infer<typeof editProfileSchema>;

/**
 * Validate edit profile form data
 * Returns the parsed data on success, throws on failure
 */
export function validateEditProfileForm(data: unknown) {
  return editProfileSchema.parse(data);
}

/**
 * Safe validate edit profile form data
 * Returns result object with success flag
 */
export function safeValidateEditProfileForm(data: unknown) {
  return editProfileSchema.safeParse(data);
}
