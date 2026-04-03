import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useForm, type SubmitHandler } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Toaster } from 'react-hot-toast';
import toast from 'react-hot-toast';
import { orderApi } from '../../services/api';
import { useCart } from '../../contexts/CartContext';
import {
  checkoutSchema,
  type CheckoutFormData,
  safeValidateCreditCard,
} from '../../schemas/order/checkout.schema';

export default function Checkout() {
  const navigate = useNavigate();
  const { cart, items, clearCart } = useCart();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [selectedMethod, setSelectedMethod] = useState<string>('credit_card');
  const [cardFocused, setCardFocused] = useState('');
  const [processingPayment, setProcessingPayment] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
    watch,
  } = useForm<CheckoutFormData>({
    resolver: zodResolver(checkoutSchema),
    mode: 'onBlur',
    defaultValues: {
      payment_method: 'credit_card',
      save_card: false,
    },
  });

  const total = items.reduce((sum, item) => sum + parseFloat(item.course?.price || 0), 0);
  const formData = watch();

  const formatCardNumber = (value: string): string => {
    const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    const matches = v.match(/\d{4,16}/g);
    const match = (matches && matches[0]) || '';
    const parts = [];
    for (let i = 0, len = match.length; i < len; i += 4) {
      parts.push(match.substring(i, i + 4));
    }
    if (parts.length) {
      return parts.join(' ');
    }
    return value;
  };

  const formatExpiry = (value: string): string => {
    const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    if (v.length >= 2) {
      return v.substring(0, 2) + '/' + v.substring(2, 4);
    }
    return v;
  };

  const handleCardNumberChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    e.target.value = formatCardNumber(e.target.value);
  };

  const handleExpiryChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    e.target.value = formatExpiry(e.target.value);
  };

  const simulatePaymentProcessing = (): Promise<{ success: boolean; transaction_id: string }> => {
    return new Promise((resolve, reject) => {
      setTimeout(() => {
        // Simulate 90% success rate
        if (Math.random() > 0.1) {
          resolve({ success: true, transaction_id: `TXN-${Date.now()}` });
        } else {
          reject(new Error('Payment processing failed. Please try again.'));
        }
      }, 2000);
    });
  };

  const onSubmit: SubmitHandler<CheckoutFormData> = async (data) => {
    setError('');
    setProcessingPayment(true);

    try {
      // Validate card details for credit card payments with Zod
      if (selectedMethod === 'credit_card') {
        const cardData = {
          card_number: data.card_number || '',
          card_holder_name: data.card_holder_name || '',
          expiry: data.expiry || '',
          cvv: data.cvv || '',
          save_card: data.save_card,
        };

        const cardValidation = safeValidateCreditCard(cardData);
        if (!cardValidation.success) {
          const firstError = cardValidation.error.issues[0];
          toast.error(firstError.message);
          setProcessingPayment(false);
          return;
        }
      }

      // Create order
      const orderResponse = await orderApi.create();
      const orderId = orderResponse.data.data.order_id;

      // Simulate payment processing
      await simulatePaymentProcessing();

      // Complete payment
      await orderApi.completePayment(orderId, selectedMethod);

      // Clear cart
      await clearCart();

      // Redirect to success
      navigate('/my-courses', {
        state: {
          message: 'Purchase successful! Your courses are now available.',
          order_id: orderId,
        },
      });
    } catch (err: unknown) {
      const errorObj = err as Error;
      setError(errorObj.message || 'Checkout failed. Please try again.');
    } finally {
      setProcessingPayment(false);
      setLoading(false);
    }
  };

  useEffect(() => {
    if (!cart || items.length === 0) {
      navigate('/cart');
    }
  }, [cart, items, navigate]);

  if (!cart || items.length === 0) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-8 text-center">
        <p className="text-gray-600">Your cart is empty</p>
      </div>
    );
  }

  return (
    <div className="max-w-6xl mx-auto px-4 py-8">
      <Toaster position="top-right" />
      <h1 className="text-2xl font-bold text-gray-900 mb-8">Checkout</h1>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column - Payment Details */}
        <div className="lg:col-span-2 space-y-6">
          <form onSubmit={handleSubmit(onSubmit)}>
            {error && (
              <div className="bg-red-50 border border-red-200 text-red-600 p-4 rounded-lg text-sm">
                <div className="flex items-center gap-2">
                  <span>⚠️</span>
                  {error}
                </div>
              </div>
            )}

            {/* Payment Method Selection */}
            <div className="bg-white rounded-xl shadow p-6 mb-6">
              <h2 className="text-lg font-semibold mb-4 flex items-center gap-2">
                <span>💳</span> Payment Method
                <span className="text-xs text-gray-500 font-normal">(Secure & Encrypted)</span>
              </h2>

              <div className="space-y-3">
                {/* Credit Card */}
                <label
                  className={`flex items-center gap-3 cursor-pointer p-4 rounded-lg border-2 transition ${
                    selectedMethod === 'credit_card'
                      ? 'border-indigo-600 bg-indigo-50'
                      : 'border-gray-200 hover:border-gray-300'
                  }`}
                >
                  <input
                    type="radio"
                    value="credit_card"
                    checked={selectedMethod === 'credit_card'}
                    onChange={(e) => setSelectedMethod(e.target.value)}
                    className="h-4 w-4 text-indigo-600"
                  />
                  <span className="flex-1 font-medium">Credit / Debit Card</span>
                  <div className="flex gap-1">
                    <span className="text-xs bg-blue-600 text-white px-2 py-1 rounded">Visa</span>
                    <span className="text-xs bg-red-600 text-white px-2 py-1 rounded">MC</span>
                    <span className="text-xs bg-orange-500 text-white px-2 py-1 rounded">Amex</span>
                  </div>
                </label>

                {/* PayPal */}
                <label
                  className={`flex items-center gap-3 cursor-pointer p-4 rounded-lg border-2 transition ${
                    selectedMethod === 'paypal'
                      ? 'border-indigo-600 bg-indigo-50'
                      : 'border-gray-200 hover:border-gray-300'
                  }`}
                >
                  <input
                    type="radio"
                    value="paypal"
                    checked={selectedMethod === 'paypal'}
                    onChange={(e) => setSelectedMethod(e.target.value)}
                    className="h-4 w-4 text-indigo-600"
                  />
                  <span className="flex-1 font-medium">PayPal</span>
                  <span className="text-xs bg-blue-800 text-white px-2 py-1 rounded">PayPal</span>
                </label>

                {/* Apple Pay */}
                <label
                  className={`flex items-center gap-3 cursor-pointer p-4 rounded-lg border-2 transition ${
                    selectedMethod === 'applepay'
                      ? 'border-indigo-600 bg-indigo-50'
                      : 'border-gray-200 hover:border-gray-300'
                  }`}
                >
                  <input
                    type="radio"
                    value="applepay"
                    checked={selectedMethod === 'applepay'}
                    onChange={(e) => setSelectedMethod(e.target.value)}
                    className="h-4 w-4 text-indigo-600"
                  />
                  <span className="flex-1 font-medium">Apple Pay</span>
                  <span className="text-xs bg-black text-white px-2 py-1 rounded"> Pay</span>
                </label>

                {/* Google Pay */}
                <label
                  className={`flex items-center gap-3 cursor-pointer p-4 rounded-lg border-2 transition ${
                    selectedMethod === 'googlepay'
                      ? 'border-indigo-600 bg-indigo-50'
                      : 'border-gray-200 hover:border-gray-300'
                  }`}
                >
                  <input
                    type="radio"
                    value="googlepay"
                    checked={selectedMethod === 'googlepay'}
                    onChange={(e) => setSelectedMethod(e.target.value)}
                    className="h-4 w-4 text-indigo-600"
                  />
                  <span className="flex-1 font-medium">Google Pay</span>
                  <span className="text-xs bg-gray-800 text-white px-2 py-1 rounded">G Pay</span>
                </label>

                {/* Bank Transfer */}
                <label
                  className={`flex items-center gap-3 cursor-pointer p-4 rounded-lg border-2 transition ${
                    selectedMethod === 'bank_transfer'
                      ? 'border-indigo-600 bg-indigo-50'
                      : 'border-gray-200 hover:border-gray-300'
                  }`}
                >
                  <input
                    type="radio"
                    value="bank_transfer"
                    checked={selectedMethod === 'bank_transfer'}
                    onChange={(e) => setSelectedMethod(e.target.value)}
                    className="h-4 w-4 text-indigo-600"
                  />
                  <span className="flex-1 font-medium">Bank Transfer</span>
                  <span className="text-xs bg-gray-600 text-white px-2 py-1 rounded">Wire</span>
                </label>
              </div>
            </div>

            {/* Card Details - Only show for credit card */}
            {selectedMethod === 'credit_card' && (
              <div className="bg-white rounded-xl shadow p-6 mb-6">
                <h2 className="text-lg font-semibold mb-4">Card Details</h2>

                {/* Card Preview */}
                <div className="bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-700 rounded-xl p-4 text-white mb-6 relative overflow-hidden">
                  <div className="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                  <div className="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full -ml-12 -mb-12"></div>

                  <div className="relative z-10">
                    <div className="text-sm opacity-75 mb-1">Card Number</div>
                    <div className="text-xl font-mono mb-4 tracking-wider">
                      {formData.card_number || '•••• •••• •••• ••••'}
                    </div>

                    <div className="flex justify-between">
                      <div>
                        <div className="text-xs opacity-75">Card Holder</div>
                        <div className="font-medium">
                          {formData.card_holder_name || 'YOUR NAME'}
                        </div>
                      </div>
                      <div>
                        <div className="text-xs opacity-75">Expires</div>
                        <div className="font-medium">{formData.expiry || 'MM/YY'}</div>
                      </div>
                    </div>

                    <div className="mt-4 flex justify-end">
                      <div className="flex gap-2">
                        <div className="w-12 h-8 bg-white/20 rounded"></div>
                        <div className="w-12 h-8 bg-white/20 rounded"></div>
                      </div>
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="md:col-span-2">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Card Number
                    </label>
                    <input
                      type="text"
                      className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono ${
                        errors.card_number ? 'border-red-300' : 'border-gray-300'
                      }`}
                      placeholder="1234 5678 9012 3456"
                      maxLength={19}
                      onFocus={() => setCardFocused('number')}
                      onBlur={() => setCardFocused('')}
                      {...register('card_number', {
                        onChange: handleCardNumberChange,
                      })}
                    />
                    {errors.card_number && (
                      <p className="mt-1 text-sm text-red-500">{errors.card_number.message}</p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Card Holder Name
                    </label>
                    <input
                      type="text"
                      className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                        errors.card_holder_name ? 'border-red-300' : 'border-gray-300'
                      }`}
                      placeholder="John Doe"
                      {...register('card_holder_name')}
                    />
                    {errors.card_holder_name && (
                      <p className="mt-1 text-sm text-red-500">
                        {errors.card_holder_name.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Expiry Date
                    </label>
                    <input
                      type="text"
                      className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono ${
                        errors.expiry ? 'border-red-300' : 'border-gray-300'
                      }`}
                      placeholder="MM/YY"
                      maxLength={5}
                      onFocus={() => setCardFocused('expiry')}
                      onBlur={() => setCardFocused('')}
                      {...register('expiry', {
                        onChange: handleExpiryChange,
                      })}
                    />
                    {errors.expiry && (
                      <p className="mt-1 text-sm text-red-500">{errors.expiry.message}</p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      CVV / CVC
                    </label>
                    <input
                      type="text"
                      className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono ${
                        errors.cvv ? 'border-red-300' : 'border-gray-300'
                      }`}
                      placeholder="123"
                      maxLength={4}
                      onFocus={() => setCardFocused('cvv')}
                      onBlur={() => setCardFocused('')}
                      {...register('cvv')}
                    />
                    {errors.cvv && (
                      <p className="mt-1 text-sm text-red-500">{errors.cvv.message}</p>
                    )}
                  </div>
                </div>

                <div className="mt-4 flex items-center gap-2">
                  <input
                    type="checkbox"
                    id="save-card"
                    className="h-4 w-4 text-indigo-600 rounded"
                    {...register('save_card')}
                  />
                  <label htmlFor="save-card" className="text-sm text-gray-600">
                    Save this card for future purchases
                  </label>
                </div>
              </div>
            )}

            {/* PayPal Info */}
            {selectedMethod === 'paypal' && (
              <div className="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                <div className="flex items-start gap-3">
                  <span className="text-2xl">ℹ️</span>
                  <div>
                    <h3 className="font-semibold text-blue-900 mb-1">
                      You will be redirected to PayPal
                    </h3>
                    <p className="text-sm text-blue-700">
                      After clicking &quot;Pay Now&quot;, you will be redirected to PayPal&apos;s
                      secure website to complete your purchase.
                    </p>
                  </div>
                </div>
              </div>
            )}

            {/* Apple Pay Info */}
            {selectedMethod === 'applepay' && (
              <div className="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-6">
                <div className="flex items-start gap-3">
                  <span className="text-2xl">🍎</span>
                  <div>
                    <h3 className="font-semibold text-gray-900 mb-1">Pay with Apple Pay</h3>
                    <p className="text-sm text-gray-600">
                      Use Touch ID or Face ID to complete your purchase securely with Apple Pay.
                    </p>
                  </div>
                </div>
              </div>
            )}

            {/* Google Pay Info */}
            {selectedMethod === 'googlepay' && (
              <div className="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-6">
                <div className="flex items-start gap-3">
                  <span className="text-2xl">G</span>
                  <div>
                    <h3 className="font-semibold text-gray-900 mb-1">Pay with Google Pay</h3>
                    <p className="text-sm text-gray-600">
                      Complete your purchase quickly and securely with Google Pay.
                    </p>
                  </div>
                </div>
              </div>
            )}

            {/* Bank Transfer Info */}
            {selectedMethod === 'bank_transfer' && (
              <div className="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
                <div className="flex items-start gap-3">
                  <span className="text-2xl">🏦</span>
                  <div>
                    <h3 className="font-semibold text-yellow-900 mb-1">Bank Transfer Details</h3>
                    <p className="text-sm text-yellow-700 mb-3">
                      After placing your order, you will receive bank transfer instructions via
                      email. Your courses will be activated once payment is confirmed.
                    </p>
                    <ul className="text-sm text-yellow-700 list-disc list-inside space-y-1">
                      <li>Processing time: 1-3 business days</li>
                      <li>Bank: Vietcombank</li>
                      <li>Account: 1234567890</li>
                    </ul>
                  </div>
                </div>
              </div>
            )}

            {/* Billing Address */}
            <div className="bg-white rounded-xl shadow p-6 mb-6">
              <h2 className="text-lg font-semibold mb-4">Billing Information</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    First Name
                  </label>
                  <input
                    type="text"
                    className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                      errors.first_name ? 'border-red-300' : 'border-gray-300'
                    }`}
                    placeholder="John"
                    {...register('first_name')}
                  />
                  {errors.first_name && (
                    <p className="mt-1 text-sm text-red-500">{errors.first_name.message}</p>
                  )}
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Last Name
                  </label>
                  <input
                    type="text"
                    className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                      errors.last_name ? 'border-red-300' : 'border-gray-300'
                    }`}
                    placeholder="Doe"
                    {...register('last_name')}
                  />
                  {errors.last_name && (
                    <p className="mt-1 text-sm text-red-500">{errors.last_name.message}</p>
                  )}
                </div>
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Email Address
                  </label>
                  <input
                    type="email"
                    className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                      errors.email ? 'border-red-300' : 'border-gray-300'
                    }`}
                    placeholder="john@example.com"
                    {...register('email')}
                  />
                  {errors.email && (
                    <p className="mt-1 text-sm text-red-500">{errors.email.message}</p>
                  )}
                </div>
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Country
                  </label>
                  <select
                    className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                      errors.country ? 'border-red-300' : 'border-gray-300'
                    }`}
                    {...register('country')}
                  >
                    <option value="">Select Country</option>
                    <option value="VN">Vietnam</option>
                    <option value="US">United States</option>
                    <option value="GB">United Kingdom</option>
                    <option value="CA">Canada</option>
                    <option value="AU">Australia</option>
                    <option value="SG">Singapore</option>
                    <option value="JP">Japan</option>
                    <option value="KR">South Korea</option>
                  </select>
                  {errors.country && (
                    <p className="mt-1 text-sm text-red-500">{errors.country.message}</p>
                  )}
                </div>
              </div>
            </div>

            <button
              type="submit"
              disabled={loading || processingPayment}
              className="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-lg shadow-lg"
            >
              {processingPayment ? (
                <span className="flex items-center justify-center gap-2">
                  <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                    <circle
                      className="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      strokeWidth="4"
                      fill="none"
                    />
                    <path
                      className="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    />
                  </svg>
                  Processing Payment...
                </span>
              ) : (
                `Pay $${total.toFixed(2)}`
              )}
            </button>

            <p className="text-xs text-gray-500 text-center mt-4 flex items-center justify-center gap-2">
              <span>🔒</span> Your payment information is secure and encrypted
            </p>
          </form>
        </div>

        {/* Right Column - Order Summary */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-xl shadow p-6 sticky top-4">
            <h2 className="text-lg font-semibold mb-4">Order Summary</h2>

            <div className="space-y-3 mb-4">
              {items.map((item) => (
                <div key={item.cart_item_id} className="flex gap-3 items-start">
                  {item.course?.images?.[0]?.image_url ? (
                    <img
                      src={item.course.images[0].image_url}
                      alt={item.course.title}
                      className="w-16 h-12 object-cover rounded"
                    />
                  ) : (
                    <div className="w-16 h-12 bg-gray-200 rounded flex items-center justify-center">
                      <span className="text-xs text-gray-400">No img</span>
                    </div>
                  )}
                  <div className="flex-1">
                    <p className="text-sm font-medium text-gray-900 line-clamp-2">
                      {item.course?.title}
                    </p>
                    <p className="text-sm text-indigo-600 font-semibold">
                      ${item.course?.price || 0}
                    </p>
                  </div>
                </div>
              ))}
            </div>

            <div className="border-t pt-4 space-y-2">
              <div className="flex justify-between text-sm text-gray-600">
                <span>Subtotal</span>
                <span>${total.toFixed(2)}</span>
              </div>
              <div className="flex justify-between text-sm text-gray-600">
                <span>Tax</span>
                <span>$0.00</span>
              </div>
              <div className="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t">
                <span>Total</span>
                <span className="text-indigo-600">${total.toFixed(2)}</span>
              </div>
            </div>

            <div className="mt-6 p-4 bg-green-50 rounded-lg">
              <div className="flex items-start gap-2">
                <span className="text-green-600 text-lg">✓</span>
                <div className="text-sm text-green-800">
                  <p className="font-medium mb-1">30-Day Money-Back Guarantee</p>
                  <p className="text-xs text-green-600">
                    Full refund if you&apos;re not satisfied within 30 days
                  </p>
                </div>
              </div>
            </div>

            <div className="mt-4 p-4 bg-blue-50 rounded-lg">
              <div className="flex items-start gap-2">
                <span className="text-blue-600 text-lg">🎓</span>
                <div className="text-sm text-blue-800">
                  <p className="font-medium mb-1">Certificate Included</p>
                  <p className="text-xs text-blue-600">
                    Earn a certificate upon course completion
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
