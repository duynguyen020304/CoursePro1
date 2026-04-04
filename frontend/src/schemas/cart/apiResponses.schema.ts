import { z } from 'zod';

const cartItemSchema = z.object({
  cart_item_id: z.string(),
  cart_id: z.string(),
  course_id: z.string(),
  quantity: z.number().int().positive(),
  price: z.number().nonnegative(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  course: z.object({
    course_id: z.string(),
    title: z.string(),
    description: z.string().nullable().optional(),
    price: z.number().nonnegative(),
    thumbnail_url: z.string().nullable().optional(),
  }).optional(),
});

export const cartSchema = z.object({
  cart_id: z.string(),
  user_id: z.string(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  items: z.array(cartItemSchema).optional(),
});

export const cartResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: cartSchema.nullable(),
});

export const cartItemResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: cartItemSchema,
});

export const clearCartResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
});

export type Cart = z.infer<typeof cartSchema>;
export type CartItem = z.infer<typeof cartItemSchema>;
export type CartResponse = z.infer<typeof cartResponseSchema>;
export type CartItemResponse = z.infer<typeof cartItemResponseSchema>;
export type ClearCartResponse = z.infer<typeof clearCartResponseSchema>;
