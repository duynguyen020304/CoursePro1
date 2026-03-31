import { useState, useEffect, useRef } from 'react';
import { Chart, registerables } from 'chart.js';
import axios from 'axios';

Chart.register(...registerables);

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export default function Revenue() {
  const [loading, setLoading] = useState(true);
  const [revenueData, setRevenueData] = useState({
    totalRevenue: 0,
    monthlyRevenue: 0,
    ordersCount: 0,
    averageOrderValue: 0,
  });
  const [dateRange, setDateRange] = useState({
    start_date: new Date(new Date().setMonth(new Date().getMonth() - 3)).toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
  });
  const [monthlyData, setMonthlyData] = useState([]);
  const [recentOrders, setRecentOrders] = useState([]);
  const [topCourses, setTopCourses] = useState([]);
  const chartRef = useRef(null);
  const chartInstanceRef = useRef(null);

  useEffect(() => {
    async function fetchRevenueData() {
      try {
        const token = localStorage.getItem('token');
        const headers = { Authorization: `Bearer ${token}` };

        // Fetch orders and calculate revenue
        const ordersRes = await axios.get(`${API_BASE_URL}/orders`, { headers, params: { page: 1, per_page: 100 } }).catch(() => null);

        if (ordersRes?.data?.data) {
          // Handle paginated response - orders could be in data.data or data.data.data
          const orders = Array.isArray(ordersRes.data.data)
            ? ordersRes.data.data
            : (ordersRes.data.data?.data || []);

          // Filter by date range
          const filteredOrders = orders.filter(order => {
            const orderDate = new Date(order.created_at);
            return orderDate >= new Date(dateRange.start_date) && orderDate <= new Date(dateRange.end_date);
          });

          const totalRevenue = filteredOrders.reduce((sum, order) => sum + (order.total_amount || 0), 0);
          const monthlyRevenue = filteredOrders
            .filter(order => {
              const orderDate = new Date(order.created_at);
              const now = new Date();
              return orderDate.getMonth() === now.getMonth() && orderDate.getFullYear() === now.getFullYear();
            })
            .reduce((sum, order) => sum + (order.total_amount || 0), 0);
          const averageOrderValue = filteredOrders.length > 0
            ? totalRevenue / filteredOrders.length
            : 0;

          setRevenueData({
            totalRevenue,
            monthlyRevenue,
            ordersCount: filteredOrders.length,
            averageOrderValue,
          });

          setRecentOrders(filteredOrders.slice(0, 10));

          // Calculate monthly data for chart
          const monthlyMap = new Map();
          filteredOrders.forEach(order => {
            const date = new Date(order.created_at);
            const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
            monthlyMap.set(key, (monthlyMap.get(key) || 0) + (order.total_amount || 0));
          });

          const monthly = Array.from(monthlyMap.entries())
            .sort()
            .slice(-6)
            .map(([month, amount]) => ({
              month: new Date(month + '-01').toLocaleDateString('en-US', { month: 'short' }),
              revenue: amount,
            }));

          setMonthlyData(monthly);
          setTopCourses(getTopCoursesByRevenue(filteredOrders));
        }

        setLoading(false);
      } catch (error) {
        console.error('Failed to fetch revenue data:', error);
        setLoading(false);
      }
    }

    fetchRevenueData();
  }, [dateRange]);

  // Create chart when monthlyData changes
  useEffect(() => {
    if (monthlyData.length === 0 || !chartRef.current) return;

    if (chartInstanceRef.current) {
      chartInstanceRef.current.destroy();
    }

    const ctx = chartRef.current.getContext('2d');
    chartInstanceRef.current = new Chart(ctx, {
      type: 'line',
      data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
          label: 'Revenue',
          data: monthlyData.map(d => d.revenue),
          borderColor: '#4f46e5',
          backgroundColor: 'rgba(79, 70, 229, 0.1)',
          fill: true,
          tension: 0.4,
        }],
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) => `$${context.raw.toLocaleString()}`,
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => `$${value.toLocaleString()}`,
            },
          },
        },
      },
    });

    return () => {
      if (chartInstanceRef.current) {
        chartInstanceRef.current.destroy();
      }
    };
  }, [monthlyData]);

  function getTopCoursesByRevenue(orders) {
    const courseMap = new Map();
    orders.forEach(order => {
      const courseName = order.course?.title || 'Unknown Course';
      courseMap.set(courseName, {
        name: courseName,
        revenue: (courseMap.get(courseName)?.revenue || 0) + (order.total_amount || 0),
        orders: (courseMap.get(courseName)?.orders || 0) + 1,
      });
    });
    return Array.from(courseMap.values())
      .sort((a, b) => b.revenue - a.revenue)
      .slice(0, 5);
  }

  const handleDateRangeChange = (e) => {
    const { name, value } = e.target;
    setDateRange(prev => ({ ...prev, [name]: value }));
  };

  return (
    <div>
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <h1 className="text-2xl font-bold text-gray-900">Revenue Analytics</h1>
        <div className="flex gap-2 flex-wrap">
          <div>
            <label className="text-xs text-gray-500 block mb-1">Start Date</label>
            <input
              type="date"
              name="start_date"
              value={dateRange.start_date}
              onChange={handleDateRangeChange}
              className="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div>
            <label className="text-xs text-gray-500 block mb-1">End Date</label>
            <input
              type="date"
              name="end_date"
              value={dateRange.end_date}
              onChange={handleDateRangeChange}
              className="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
        </div>
      </div>

      {loading ? (
        <div className="flex justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
      ) : (
        <>
          {/* Overview Cards */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div className="bg-white rounded-xl shadow p-6">
              <p className="text-sm text-gray-600 mb-1">Total Revenue</p>
              <p className="text-3xl font-bold text-gray-900">${revenueData.totalRevenue.toLocaleString()}</p>
              <p className="text-sm text-gray-500 mt-2">Selected period</p>
            </div>

            <div className="bg-white rounded-xl shadow p-6">
              <p className="text-sm text-gray-600 mb-1">Monthly Revenue</p>
              <p className="text-3xl font-bold text-gray-900">${revenueData.monthlyRevenue.toLocaleString()}</p>
              <p className="text-sm text-gray-500 mt-2">Current month</p>
            </div>

            <div className="bg-white rounded-xl shadow p-6">
              <p className="text-sm text-gray-600 mb-1">Total Orders</p>
              <p className="text-3xl font-bold text-gray-900">{revenueData.ordersCount}</p>
              <p className="text-sm text-gray-500 mt-2">Selected period</p>
            </div>

            <div className="bg-white rounded-xl shadow p-6">
              <p className="text-sm text-gray-600 mb-1">Avg Order Value</p>
              <p className="text-3xl font-bold text-gray-900">${revenueData.averageOrderValue.toFixed(2)}</p>
              <p className="text-sm text-gray-500 mt-2">Selected period</p>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Revenue Chart */}
            <div className="bg-white rounded-xl shadow p-6">
              <h2 className="text-lg font-semibold mb-4">Revenue Trend (Last 6 Months)</h2>
              {monthlyData.length > 0 ? (
                <canvas ref={chartRef} height={200}></canvas>
              ) : (
                <p className="text-gray-500 text-center py-8">No revenue data available for the selected period</p>
              )}
            </div>

            {/* Top Courses */}
            <div className="bg-white rounded-xl shadow p-6">
              <h2 className="text-lg font-semibold mb-4">Top Courses by Revenue</h2>
              {topCourses.length > 0 ? (
                <div className="space-y-4">
                  {topCourses.map((course, index) => (
                    <div key={index} className="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-semibold text-sm">
                          {index + 1}
                        </div>
                        <div>
                          <p className="font-medium text-gray-900">{course.name}</p>
                          <p className="text-sm text-gray-500">{course.orders} orders</p>
                        </div>
                      </div>
                      <p className="font-semibold text-gray-900">${course.revenue.toLocaleString()}</p>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-gray-500 text-center py-8">No course data available</p>
              )}
            </div>
          </div>

          {/* Recent Orders */}
          <div className="bg-white rounded-xl shadow mt-6 p-6">
            <h2 className="text-lg font-semibold mb-4">Recent Transactions</h2>
            {recentOrders.length > 0 ? (
              <div className="overflow-x-auto">
                <table className="min-w-full">
                  <thead>
                    <tr className="text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                      <th className="pb-3">Order ID</th>
                      <th className="pb-3">Customer</th>
                      <th className="pb-3">Amount</th>
                      <th className="pb-3">Date</th>
                      <th className="pb-3">Status</th>
                    </tr>
                  </thead>
                  <tbody className="text-sm">
                    {recentOrders.map((order) => (
                      <tr key={order.order_id} className="border-b border-gray-100">
                        <td className="py-3 font-mono text-gray-600">#{order.order_id?.substring(0, 8)}...</td>
                        <td className="py-3">
                          {order.user?.first_name} {order.user?.last_name}
                        </td>
                        <td className="py-3 font-semibold text-indigo-600">
                          ${(order.total_amount || 0).toLocaleString()}
                        </td>
                        <td className="py-3 text-gray-500">
                          {new Date(order.created_at).toLocaleDateString()}
                        </td>
                        <td className="py-3">
                          <span className={`px-2 py-1 text-xs font-semibold rounded-full ${
                            order.status === 'completed'
                              ? 'bg-green-100 text-green-800'
                              : order.status === 'pending'
                              ? 'bg-yellow-100 text-yellow-800'
                              : 'bg-gray-100 text-gray-800'
                          }`}>
                            {order.status || 'pending'}
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            ) : (
              <p className="text-gray-500 text-center py-8">No recent transactions</p>
            )}
          </div>
        </>
      )}
    </div>
  );
}
