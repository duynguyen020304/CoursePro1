import { describe, expect, it } from 'vitest';
import {
  createOrderResponseSchema,
  orderDetailResponseSchema,
} from '../../../schemas/order/apiResponses.schema';

const sampleOrderResponse = {
  success: true,
  message: 'Order created successfully',
  data: {
    order_id: 'order_123',
    user_id: 'user_123',
    order_date: '2026-04-07T09:00:00.000Z',
    status: null,
    total_amount: '299.98',
    is_active: true,
    created_at: '2026-04-07T09:00:00.000Z',
    updated_at: null,
    details: [
      {
        order_id: 'order_123',
        course_id: 'course_1',
        price: '149.99',
        course: {
          course_id: 'course_1',
          title: 'TypeScript Basics',
          description: 'Intro course',
          price: '149.99',
          difficulty: 'beginner',
          language: 'English',
          created_by: 'instructor_1',
          is_active: true,
        },
      },
    ],
    payment: {
      payment_id: 'payment_123',
      order_id: 'order_123',
      amount: '299.98',
      payment_method: 'pending',
      payment_status: 'pending',
      payment_date: '2026-04-07T09:00:00.000Z',
      created_at: '2026-04-07T09:00:00.000Z',
    },
  },
};

describe('Order API response schemas', () => {
  it('validates the order creation envelope returned by the backend', () => {
    const result = createOrderResponseSchema.safeParse(sampleOrderResponse);

    expect(result.success).toBe(true);
    if (result.success) {
      expect(result.data.data.order_date).toBe(sampleOrderResponse.data.order_date);
      expect(result.data.data.total_amount).toBe(299.98);
      expect(result.data.data.details?.[0].price).toBe(149.99);
      expect(result.data.data.payment?.payment_status).toBe('pending');
    }
  });

  it('validates the order detail envelope returned by OrderController@show', () => {
    const result = orderDetailResponseSchema.safeParse(sampleOrderResponse);

    expect(result.success).toBe(true);
    if (result.success) {
      expect(result.data.data.order_id).toBe(sampleOrderResponse.data.order_id);
    }
  });
});
