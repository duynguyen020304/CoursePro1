import { z } from 'zod';
import { courseSchema } from '../course';

/**
 * Order schema - represents an order in the system
 * Backend uses order_id (e.g. 'order_<uuid>') as the primary key
 * Includes loaded relations: user, details (with course), payment
 */
export const orderSchema = z.object({
  order_id: z.string(),
  user_id: z.string(),
  order_date: z.string().nullable().optional(),
  status: z.enum(['pending', 'processing', 'completed', 'cancelled', 'refunded']).nullable().optional(),
  total_amount: z.coerce.number().nonnegative(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().datetime().nullable().optional(),
  created_at: z.string().datetime().nullable().optional(),
  updated_at: z.string().datetime().nullable().optional(),
  // Include user relation when loaded (admin order listing)
  user: z.object({
    first_name: z.string().optional(),
    last_name: z.string().optional(),
    email: z.string().optional(),
  }).optional(),
  // Include details relation (array of order details, each with a course)
  details: z.array(z.object({
    order_id: z.string(),
    course_id: z.string(),
    price: z.coerce.number().nonnegative(),
    course: z.object({
      course_id: z.string(),
      title: z.string(),
      description: z.string().nullable().optional(),
      price: z.coerce.number().nonnegative().optional(),
      difficulty: z.string().nullable().optional(),
      language: z.string().nullable().optional(),
      created_by: z.string().nullable().optional(),
      is_active: z.boolean().nullable().optional(),
    }).nullable().optional(),
  })).optional(),
  // Include payment relation when loaded
  payment: z.object({
    payment_id: z.string(),
    order_id: z.string(),
    amount: z.coerce.number().nonnegative(),
    payment_method: z.string().optional(),
    payment_status: z.string().optional(),
    payment_date: z.string().nullable().optional(),
    created_at: z.string().nullable().optional(),
  }).optional(),
});

/**
 * OrderDetail schema - individual item within an order
 */
export const orderDetailSchema = z.object({
  order_id: z.string(),
  course_id: z.string(),
  price: z.coerce.number().nonnegative(),
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
  payment_id: z.string(),
  order_id: z.string(),
  amount: z.coerce.number().nonnegative(),
  payment_method: z.enum(['pending', 'credit_card', 'paypal', 'applepay', 'googlepay', 'bank_transfer']),
  payment_status: z.enum(['pending', 'processing', 'completed', 'failed', 'refunded']),
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
 * OrderDetailResponse - single order with loaded details and payment
 * Backend returns the standard envelope: { success, message, data: order }
 */
export const orderDetailResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: orderSchema,
});

/**
 * CreateOrderResponse - backend envelope returned after creating an order
 * The backend returns the created order in the data field.
 */
export const createOrderResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: orderSchema,
});

// Type inference helpers
export type Order = z.infer<typeof orderSchema>;
export type OrderDetail = z.infer<typeof orderDetailSchema>;
export type Payment = z.infer<typeof paymentSchema>;
export type OrderListResponse = z.infer<typeof orderListResponseSchema>;
export type OrderDetailResponse = z.infer<typeof orderDetailResponseSchema>;
export type CreateOrderResponse = z.infer<typeof createOrderResponseSchema>;
