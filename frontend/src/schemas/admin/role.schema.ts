import { z } from 'zod';
import { paginationSchema } from '../common';

/**
 * Permission schema - represents a system permission
 */
export const permissionSchema = z.object({
  permission_id: z.string(),
  name: z.string(),
  display_name: z.string().optional(),
  description: z.string().nullable().optional(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().datetime().nullable().optional(),
  updated_at: z.string().datetime().nullable().optional(),
});

/**
 * Role schema - represents a system role
 */
export const roleSchema = z.object({
  role_id: z.string(),
  role_name: z.string(),
  permissions: z.array(permissionSchema).optional(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().datetime().nullable().optional(),
  updated_at: z.string().datetime().nullable().optional(),
});

/**
 * RoleListResponse - paginated list of roles
 */
export const roleListResponseSchema = z.object({
  data: z.array(roleSchema),
  pagination: paginationSchema.optional(),
});

/**
 * RoleResponse - single role response
 */
export const roleResponseSchema = z.object({
  data: roleSchema,
});

/**
 * RolePermissionsResponse - role with its permissions
 */
export const rolePermissionsResponseSchema = z.object({
  data: z.array(permissionSchema),
});

/**
 * PermissionListResponse - list of all permissions
 */
export const permissionListResponseSchema = z.object({
  data: z.array(permissionSchema),
});

// API response wrappers (Laravel { success, message, data } format)
export const roleListApiResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.array(roleSchema),
});

export const roleApiResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: roleSchema,
});

export const roleCreateApiResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: roleSchema,
});

export const permissionListApiResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.array(permissionSchema),
});

export const rolePermissionActionApiResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.array(permissionSchema).optional(),
});

// Type inference helpers
export type Permission = z.infer<typeof permissionSchema>;
export type Role = z.infer<typeof roleSchema>;
export type RoleListResponse = z.infer<typeof roleListResponseSchema>;
export type RoleResponse = z.infer<typeof roleResponseSchema>;
export type RolePermissionsResponse = z.infer<typeof rolePermissionsResponseSchema>;
export type PermissionListResponse = z.infer<typeof permissionListResponseSchema>;
