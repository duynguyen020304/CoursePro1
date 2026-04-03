import { z } from 'zod';
import { courseSchema } from '../course';

// Cart schema
export const cartSchema = z.object({
  id: z.string().uuid(),
  user_id: z.string().uuid(),
  created_at: z.string().datetime(),
});

// CartItem schema
export const cartItemSchema = z.object({
  id: z.string().uuid(),
  cart_id: z.string().uuid(),
  course_id: z.string().uuid(),
  quantity: z.number().int().positive(),
  course: courseSchema,
});

// CartResponse schema - returned by cartApi.get()
export const cartResponseSchema = z.object({
  cart: cartSchema,
  items: z.array(cartItemSchema),
  total: z.number().nonnegative(),
});

// AddToCartResponse schema - returned by cartApi.addItem()
export const addToCartResponseSchema = z.object({
  cart_item: cartItemSchema,
  cart: cartSchema,
});

// ClearCartResponse schema - returned by cartApi.clear()
export const clearCartResponseSchema = z.object({
  message: z.string(),
});

// Type inference helpers
export type Cart = z.infer<typeof cartSchema>;
export type CartItem = z.infer<typeof cartItemSchema>;
export type CartResponse = z.infer<typeof cartResponseSchema>;
export type AddToCartResponse = z.infer<typeof addToCartResponseSchema>;
export type ClearCartResponse = z.infer<typeof clearCartResponseSchema>;
