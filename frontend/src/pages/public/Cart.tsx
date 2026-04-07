import { useCart } from '../../contexts/CartContext';
import { Link, useNavigate } from 'react-router-dom';

export default function Cart() {
  const { cart, items, loading, removeItem, clearCart } = useCart();
  const navigate = useNavigate();

  const total = items.reduce((sum, item) => sum + (item.course?.price || 0) * (item.quantity || 1), 0);

  const handleCheckout = () => {
    navigate('/checkout');
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  if (!cart || items.length === 0) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-8 text-center">
        <h1 className="text-2xl font-bold text-gray-900 mb-4">Your Cart is Empty</h1>
        <p className="text-gray-600 mb-8">Looks like you haven&apos;t added any courses yet.</p>
        <Link
          to="/courses"
          className="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700"
        >
          Browse Courses
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 className="text-2xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Cart Items */}
        <div className="lg:col-span-2 space-y-4">
          {items.map((item) => (
            <div key={item.cart_item_id} className="bg-white rounded-lg shadow p-4 flex gap-4">
              {item.course?.thumbnail_url ? (
                <img
                  src={item.course.thumbnail_url}
                  alt={item.course.title}
                  className="w-32 h-24 object-cover rounded"
                />
              ) : (
                <div className="w-32 h-24 bg-gray-200 rounded flex items-center justify-center">
                  <span className="text-gray-400 text-sm">No image</span>
                </div>
              )}
              <div className="flex-1">
                <h3 className="font-semibold text-gray-900">{item.course?.title}</h3>
                <p className="text-indigo-600 font-bold mt-2">${String(item.course?.price || 0)}</p>
              </div>
              <button
                onClick={() => removeItem(item.cart_item_id)}
                className="text-red-500 hover:text-red-700 self-start"
              >
                Remove
              </button>
            </div>
          ))}

          <button
            onClick={clearCart}
            className="text-gray-600 hover:text-gray-800 text-sm"
          >
            Clear Cart
          </button>
        </div>

        {/* Order Summary */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-lg shadow p-6 sticky top-24">
            <h2 className="text-lg font-semibold mb-4">Order Summary</h2>
            <div className="space-y-2 mb-4">
              <div className="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>${total.toFixed(2)}</span>
              </div>
              <div className="flex justify-between text-gray-600">
                <span>Tax</span>
                <span>$0.00</span>
              </div>
              <div className="border-t pt-2 flex justify-between font-bold text-lg">
                <span>Total</span>
                <span className="text-indigo-600">${total.toFixed(2)}</span>
              </div>
            </div>
            <button
              onClick={handleCheckout}
              className="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700"
            >
              Proceed to Checkout
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
