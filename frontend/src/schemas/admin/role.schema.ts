import { z } from 'zod';
import { paginationSchema } from '../common';

/**
 * Permission schema - represents a system permission
 */
export const permissionSchema = z.object({
  id: z.string().uuid(),
  name: z.string(),
  slug: z.string(),
  description: z.string().nullable().optional(),
  group: z.string().optional(),
  created_at: z.string().datetime().optional(),
});

/**
 * Role schema - represents a system role
 */
export const roleSchema = z.object({
  id: z.string().uuid(),
  name: z.string(),
  slug: z.string(),
  description: z.string().nullable().optional(),
  permissions: z.array(permissionSchema).optional(),
  created_at: z.string().datetime().optional(),
  updated_at: z.string().datetime().optional(),
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
  role: roleSchema,
});

/**
 * RolePermissionsResponse - role with its permissions
 */
export const rolePermissionsResponseSchema = z.object({
  role: roleSchema,
  permissions: z.array(permissionSchema),
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
  data: z.array(roleSchema.extend({ permissions: z.array(permissionSchema).optional() })),
});

export const roleApiResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: roleSchema.extend({ permissions: z.array(permissionSchema).optional() }),
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
