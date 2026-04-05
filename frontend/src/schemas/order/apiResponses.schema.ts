import { z } from 'zod';
import { courseSchema } from '../course';

/**
 * Order schema - represents an order in the system
 */
export const orderSchema = z.object({
  id: z.string().uuid(),
  user_id: z.string().uuid(),
  status: z.enum(['pending', 'processing', 'completed', 'cancelled', 'refunded']),
  total_amount: z.number().nonnegative(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().datetime().nullable().optional(),
  updated_at: z.string().datetime().nullable().optional(),
});

/**
 * OrderDetail schema - individual item within an order
 */
export const orderDetailSchema = z.object({
  id: z.string().uuid(),
  course_id: z.string().uuid(),
  price: z.number().nonnegative(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().datetime().nullable().optional(),
  updated_at: z.string().datetime().nullable().optional(),
  course: courseSchema,
});

/**
 * Payment schema - payment record for an order
 */
export const paymentSchema = z.object({
  id: z.string().uuid(),
  order_id: z.string().uuid(),
  amount: z.number().nonnegative(),
  payment_method: z.enum(['credit_card', 'paypal', 'applepay', 'googlepay', 'bank_transfer']),
  status: z.enum(['pending', 'completed', 'failed', 'refunded']),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().datetime().nullable().optional(),
  updated_at: z.string().datetime().nullable().optional(),
});

/**
 * OrderListResponse - paginated list of orders
 * Flat pagination contract from backend paginated() helper:
 * { success, message, data: [...orders], hasNextPage, hasPreviousPage, totalPage, totalItem }
 */
export const orderListResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.array(orderSchema),
  hasNextPage: z.boolean(),
  hasPreviousPage: z.boolean(),
  totalPage: z.number(),
  totalItem: z.number(),
});

/**
 * OrderDetailResponse - single order with details and payments
 */
export const orderDetailResponseSchema = z.object({
  order: orderSchema,
  order_details: z.array(orderDetailSchema),
  payments: z.array(paymentSchema),
});

/**
 * CreateOrderResponse - response after creating an order (includes client_secret for payment)
 */
export const createOrderResponseSchema = z.object({
  order: orderSchema,
  client_secret: z.string(),
});

// Type inference helpers
export type Order = z.infer<typeof orderSchema>;
export type OrderDetail = z.infer<typeof orderDetailSchema>;
export type Payment = z.infer<typeof paymentSchema>;
export type OrderListResponse = z.infer<typeof orderListResponseSchema>;
export type OrderDetailResponse = z.infer<typeof orderDetailResponseSchema>;
export type CreateOrderResponse = z.infer<typeof createOrderResponseSchema>;
