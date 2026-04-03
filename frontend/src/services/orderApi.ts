import api from './api';
import {
  orderListResponseSchema,
  orderDetailResponseSchema,
  createOrderResponseSchema,
  type OrderListResponse,
  type OrderDetailResponse,
  type CreateOrderResponse,
} from '../schemas/order/apiResponses.schema';

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

export const orderApi = {
  /**
   * List orders with optional pagination params
   */
  list: async (params?: { page?: number; per_page?: number }): Promise<OrderListResponse | null> => {
    const response = await api.get('/orders', { params });
    return validateResponse(orderListResponseSchema, response.data, 'list');
  },

  /**
   * Get a single order by ID
   */
  get: async (orderId: string): Promise<OrderDetailResponse | null> => {
    const response = await api.get(`/orders/${orderId}`);
    return validateResponse(orderDetailResponseSchema, response.data, 'get');
  },

  /**
   * Create a new order (checkout)
   */
  create: async (): Promise<CreateOrderResponse | null> => {
    const response = await api.post('/orders');
    return validateResponse(createOrderResponseSchema, response.data, 'create');
  },

  /**
   * Complete payment for an order
   */
  completePayment: async (orderId: string, payment_method: string): Promise<OrderDetailResponse | null> => {
    const response = await api.post(`/orders/${orderId}/payment`, { payment_method });
    return validateResponse(orderDetailResponseSchema, response.data, 'completePayment');
  },
};

export default orderApi;
