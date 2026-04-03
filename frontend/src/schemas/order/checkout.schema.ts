import { z } from 'zod';
import { emailSchema } from '../common';

/**
 * Checkout form schema using Zod
 * Validates payment card details and billing information
 * Note: This only validates format, card details are NOT stored
 */

// Card number: 16 digits, optionally with spaces
export const cardNumberSchema = z
  .string()
  .regex(/^\d[\d\s]{15,23}$/, 'Card number must be 16 digits')
  .transform((val) => val.replace(/\s/g, '')) // Remove spaces for validation
  .refine((val) => /^\d{16}$/.test(val), 'Card number must be exactly 16 digits');

// Card expiry: MM/YY format
export const cardExpirySchema = z
  .string()
  .regex(/^[0-9]{2}\/[0-9]{2}$/, 'Expiry must be in MM/YY format')
  .refine((val) => {
    const [month, year] = val.split('/').map(Number);
    const now = new Date();
    const currentYear = now.getFullYear() % 100;
    const currentMonth = now.getMonth() + 1;
    
    // Check if month is valid (1-12)
    if (month < 1 || month > 12) return false;
    
    // Check if card is not expired
    if (year < currentYear) return false;
    if (year === currentYear && month < currentMonth) return false;
    
    return true;
  }, 'Card has expired or invalid expiry date');

// Card CVC: 3 or 4 digits
export const cardCvcSchema = z
  .string()
  .regex(/^[0-9]{3,4}$/, 'CVC must be 3 or 4 digits');

// Card holder name
export const cardHolderNameSchema = z
  .string()
  .min(1, 'Card holder name is required')
  .min(2, 'Card holder name must be at least 2 characters');

// Billing information schemas
export const billingNameSchema = z
  .string()
  .min(1, 'First name is required')
  .min(2, 'First name must be at least 2 characters');

export const billingLastNameSchema = z
  .string()
  .min(1, 'Last name is required')
  .min(2, 'Last name must be at least 2 characters');

export const countrySchema = z
  .string()
  .min(1, 'Country is required');

/**
 * Credit card checkout schema
 * Used when payment method is 'credit_card'
 */
export const creditCardCheckoutSchema = z.object({
  card_number: cardNumberSchema,
  card_holder_name: cardHolderNameSchema,
  expiry: cardExpirySchema,
  cvv: cardCvcSchema,
  save_card: z.boolean().optional(),
});

/**
 * Full checkout schema for credit card payments
 * Includes billing information
 */
export const checkoutSchema = z.object({
  // Payment method selection
  payment_method: z.enum(['credit_card', 'paypal', 'applepay', 'googlepay', 'bank_transfer']),
  
  // Credit card fields (required when payment_method is credit_card)
  card_number: cardNumberSchema.optional(),
  card_holder_name: cardHolderNameSchema.optional(),
  expiry: cardExpirySchema.optional(),
  cvv: cardCvcSchema.optional(),
  save_card: z.boolean().optional(),
  
  // Billing information (always required)
  first_name: billingNameSchema,
  last_name: billingLastNameSchema,
  email: emailSchema,
  country: countrySchema,
});

/**
 * Type inference from schema
 */
export type CheckoutFormData = z.infer<typeof checkoutSchema>;
export type CreditCardFormData = z.infer<typeof creditCardCheckoutSchema>;

/**
 * Validate checkout form data
 * Returns the parsed data on success, throws on failure
 */
export function validateCheckoutForm(data: unknown) {
  return checkoutSchema.parse(data);
}

/**
 * Safe validate checkout form data
 * Returns result object with success flag
 */
export function safeValidateCheckoutForm(data: unknown) {
  return checkoutSchema.safeParse(data);
}

/**
 * Validate credit card data only
 * Returns result object with success flag
 */
export function safeValidateCreditCard(data: unknown) {
  return creditCardCheckoutSchema.safeParse(data);
}
