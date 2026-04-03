import { z } from 'zod';

/**
 * RoleManagement form schema using Zod
 * Validates role name and permissions array
 */
export const roleManagementSchema = z.object({
  role_name: z
    .string()
    .min(1, 'Role name is required')
    .min(2, 'Role name must be at least 2 characters')
    .max(50, 'Role name must be at most 50 characters'),
  permissions: z.array(z.string(), {
    error: 'Permissions are required',
  }),
});

/**
 * Type inference from schema
 */
export type RoleManagementFormData = z.infer<typeof roleManagementSchema>;

/**
 * Validate role management form data
 * Returns the parsed data on success, throws on failure
 */
export function validateRoleManagementForm(data: unknown) {
  return roleManagementSchema.parse(data);
}

/**
 * Safe validate role management form data
 * Returns result object with success flag
 */
export function safeValidateRoleManagementForm(data: unknown) {
  return roleManagementSchema.safeParse(data);
}
