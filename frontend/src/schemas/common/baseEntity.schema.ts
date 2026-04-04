import { z } from 'zod';

/**
 * Base entity schema - common audit fields shared across all entities
 * Includes UUID, active status, timestamps, and soft delete flag
 */
export const baseEntitySchema = z.object({
  id: z.string().uuid(),
  is_active: z.boolean().default(true),
  created_at: z.string().datetime().nullable().optional(),
  updated_at: z.string().datetime().nullable().optional(),
  deleted_at: z.string().datetime().nullable().optional(),
});

/**
 * Type representing a base entity with common audit fields
 */
export type BaseEntity = z.infer<typeof baseEntitySchema>;

/**
 * Helper function to extend baseEntitySchema with additional fields
 * Use this to create entity-specific schemas that include all base fields
 *
 * @example
 * const userSchema = extendBaseEntity(z.object({
 *   email: z.string().email(),
 *   name: z.string(),
 * }));
 */
export function extendBaseEntity<T extends z.ZodObject<any>>(schema: T) {
  return baseEntitySchema.merge(schema);
}
