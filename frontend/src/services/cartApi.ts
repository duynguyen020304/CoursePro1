import api from './api';
import {
  cartResponseSchema,
  addToCartResponseSchema,
  clearCartResponseSchema,
  type CartResponse,
  type AddToCartResponse,
  type ClearCartResponse,
} from '../schemas/order/cart.schema';

/**
 * Validates API response data using Zod schema
 * Returns the validated data or logs error without throwing (preserves existing behavior)
 */
function validateResponse<T>(schema: z.ZodSchema<T>, data: unknown, endpoint: string): T | null {
  const result = schema.safeParse(data);
  if (!result.success) {
    // Return null to indicate validation failure, but don't throw
    // This preserves existing error handling behavior
    return null;
  }
  return result.data;
}

import { z } from 'zod';

export const cartApi = {
  /**
   * Get current cart
   */
  get: async (): Promise<CartResponse | null> => {
    const response = await api.get('/cart');
    return validateResponse(cartResponseSchema, response.data, 'get');
  },

  /**
   * Add item to cart
   */
  addItem: async (course_id: string, quantity = 1): Promise<AddToCartResponse | null> => {
    const response = await api.post('/cart/items', { course_id, quantity });
    return validateResponse(addToCartResponseSchema, response.data, 'addItem');
  },

  /**
   * Remove item from cart
   */
  removeItem: async (cartItemId: string): Promise<ClearCartResponse | null> => {
    const response = await api.delete(`/cart/items/${cartItemId}`);
    return validateResponse(clearCartResponseSchema, response.data, 'removeItem');
  },

  /**
   * Clear entire cart
   */
  clear: async (): Promise<ClearCartResponse | null> => {
    const response = await api.delete('/cart');
    return validateResponse(clearCartResponseSchema, response.data, 'clear');
  },
};

export default cartApi;
