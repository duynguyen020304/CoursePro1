import { z } from 'zod';
import { paginationSchema } from '../common';

/**
 * Admin User schema - represents a user managed by admin
 */
export const adminUserSchema = z.object({
  id: z.string().uuid(),
  email: z.string().email(),
  first_name: z.string(),
  last_name: z.string(),
  role: z.string().optional(),
  role_id: z.string().uuid().nullable().optional(),
  avatar_url: z.string().url().nullable().optional(),
  phone: z.string().nullable().optional(),
  bio: z.string().nullable().optional(),
  is_active: z.boolean().optional(),
  deleted_at: z.string().datetime().nullable().optional(),
  email_verified_at: z.string().datetime().nullable().optional(),
  created_at: z.string().datetime().nullable().optional(),
  updated_at: z.string().datetime().nullable().optional(),
});

/**
 * AdminUserListResponse - paginated list of admin users
 */
export const adminUserListResponseSchema = z.object({
  data: z.array(adminUserSchema),
  pagination: paginationSchema.optional(),
});

/**
 * AdminUserResponse - single admin user response
 */
export const adminUserResponseSchema = z.object({
  user: adminUserSchema,
});

/**
 * CreateAdminUserResponse - response after creating a user
 */
export const createAdminUserResponseSchema = z.object({
  user: adminUserSchema,
  message: z.string().optional(),
});

/**
 * AssignRoleResponse - response after assigning a role to user
 */
export const assignRoleResponseSchema = z.object({
  user: adminUserSchema,
  message: z.string().optional(),
});

// Type inference helpers
export type AdminUser = z.infer<typeof adminUserSchema>;
export type AdminUserListResponse = z.infer<typeof adminUserListResponseSchema>;
export type AdminUserResponse = z.infer<typeof adminUserResponseSchema>;
export type CreateAdminUserResponse = z.infer<typeof createAdminUserResponseSchema>;
export type AssignRoleResponse = z.infer<typeof assignRoleResponseSchema>;
