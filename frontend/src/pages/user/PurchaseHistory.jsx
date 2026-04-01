import { useState, useEffect } from 'react';
import { orderApi } from '../../services/api';
import { Link } from 'react-router-dom';

export default function PurchaseHistory() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchOrders() {
      try {
        const response = await orderApi.list();
        // Handle paginated response - data.data.data for Laravel pagination
        const ordersData = response.data.data?.data || response.data.data || [];
        setOrders(ordersData);
      } catch (error) {
        console.error('Failed to fetch orders:', error);
      } finally {
        setLoading(false);
      }
    }
    fetchOrders();
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 mb-8">Purchase History</h1>

      {orders.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-gray-500 text-lg mb-4">You haven&apos;t made any purchases yet.</p>
          <Link
            to="/courses"
            className="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700"
          >
            Browse Courses
          </Link>
        </div>
      ) : (
        <div className="space-y-4">
          {orders.map((order) => (
            <div key={order.order_id} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-semibold text-gray-900">Order #{order.order_id.slice(-8)}</h3>
                  <p className="text-sm text-gray-500">
                    {new Date(order.order_date).toLocaleDateString()}
                  </p>
                </div>
                <div className="text-right">
                  <p className="text-lg font-bold text-indigo-600">${Number(order.total_amount).toFixed(2)}</p>
                  <p className="text-sm text-gray-500 capitalize">{order.payment?.payment_status || 'Pending'}</p>
                </div>
              </div>

              <div className="border-t pt-4">
                <h4 className="text-sm font-medium text-gray-700 mb-2">Courses:</h4>
                <div className="space-y-2">
                  {order.details?.map((detail) => (
                    <div key={detail.course_id} className="flex justify-between items-center">
                      <Link
                        to={`/courses/${detail.course_id}`}
                        className="text-indigo-600 hover:text-indigo-700"
                      >
                        {detail.course?.title}
                      </Link>
                      <span className="text-gray-600">${Number(detail.price).toFixed(2)}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
