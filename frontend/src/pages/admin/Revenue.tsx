import { useState, useEffect, useRef } from 'react';
import { useForm, type SubmitHandler } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Toaster } from 'react-hot-toast';
import toast from 'react-hot-toast';
import { Chart, registerables } from 'chart.js';
import { orderApi } from '../../services/api';
import {
  revenueDateRangeSchema,
  type RevenueDateRangeFormData,
} from '../../schemas/admin/revenue.schema';

Chart.register(...registerables);

export default function Revenue() {
  const [loading, setLoading] = useState(true);
  const [revenueData, setRevenueData] = useState({
    totalRevenue: 0,
    monthlyRevenue: 0,
    ordersCount: 0,
    averageOrderValue: 0,
  });
  const [monthlyData, setMonthlyData] = useState<{ month: string; revenue: number }[]>([]);
  const [recentOrders, setRecentOrders] = useState<any[]>([]);
  const [topCourses, setTopCourses] = useState<{ name: string; revenue: number; orders: number }[]>([]);
  const chartRef = useRef<HTMLCanvasElement>(null);
  const chartInstanceRef = useRef<Chart | null>(null);

  const {
    register,
    handleSubmit,
    watch,
    formState: { errors },
  } = useForm<RevenueDateRangeFormData>({
    resolver: zodResolver(revenueDateRangeSchema),
    mode: 'onChange',
    defaultValues: {
      start_date: new Date(new Date().setMonth(new Date().getMonth() - 3)).toISOString().split('T')[0],
      end_date: new Date().toISOString().split('T')[0],
    },
  });

  const watchedDateRange = watch();

  function getTopCoursesByRevenue(orders: Array<{ details?: Array<{ course?: { title?: string }; price?: number }>; total_amount?: number }>) {
    const courseMap = new Map<string, { name: string; revenue: number; orders: number }>();
    orders.forEach(order => {
      const details = order.details ?? [];
      details.forEach(detail => {
        const courseName = detail.course?.title || 'Unknown Course';
        const existing = courseMap.get(courseName);
        courseMap.set(courseName, {
          name: courseName,
          // Attribute per-detail price (not order total) since one order can have multiple courses
          revenue: (existing?.revenue || 0) + (detail.price || 0),
          // Count each (order, course) pair once - avoid double-counting one order across multiple courses
          orders: (existing?.orders || 0) + 1,
        });
      });
    });
    return Array.from(courseMap.values())
      .sort((a, b) => b.revenue - a.revenue)
      .slice(0, 5);
  }

  useEffect(() => {
    async function fetchRevenueData() {
      try {
        const ordersRes = await orderApi.list({ page: 1, per_page: 100 }).catch(() => null);

        if (ordersRes?.data?.data) {
          const orders = ordersRes.data.data;

          // Filter by date range
          const filteredOrders = orders.filter((order: any) => {
            const orderDate = new Date(order.created_at);
            const startDate = new Date(watchedDateRange.start_date);
            const endDate = new Date(watchedDateRange.end_date);
            endDate.setHours(23, 59, 59, 999); // Include the entire end date
            return orderDate >= startDate && orderDate <= endDate;
          });

          const totalRevenue = filteredOrders.reduce((sum: number, order: any) => sum + (order.total_amount || 0), 0);
          const monthlyRevenue = filteredOrders
            .filter((order: any) => {
              const orderDate = new Date(order.created_at);
              const now = new Date();
              return orderDate.getMonth() === now.getMonth() && orderDate.getFullYear() === now.getFullYear();
            })
            .reduce((sum: number, order: any) => sum + (order.total_amount || 0), 0);
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
          const monthlyMap = new Map<string, number>();
          filteredOrders.forEach((order: any) => {
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
  }, [watchedDateRange.start_date, watchedDateRange.end_date]);

  // Create chart when monthlyData changes
  useEffect(() => {
    if (monthlyData.length === 0 || !chartRef.current) return;

    if (chartInstanceRef.current) {
      chartInstanceRef.current.destroy();
    }

    const ctx = chartRef.current.getContext('2d');
    if (!ctx) return;

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
              label: (context) => `$${(context.raw as number).toLocaleString()}`,
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => `$${Number(value).toLocaleString()}`,
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

  // Shadow mode: Also run Zod validation separately to show toast on error
  const onSubmit: SubmitHandler<RevenueDateRangeFormData> = async (data) => {
    // Additional Zod validation in shadow mode
    const zodResult = revenueDateRangeSchema.safeParse(data);
    if (!zodResult.success) {
      const zodErrors = zodResult.error.issues;
      if (zodErrors.length > 0) {
        const firstError = zodErrors[0];
        toast.error(firstError.message);
      }
      return;
    }

    // Date range is valid, data will be fetched via useEffect watching the form values
    console.log('Date range updated:', data);
  };

  return (
    <div>
      <Toaster position="top-right" />
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <h1 className="text-2xl font-bold text-gray-900">Revenue Analytics</h1>
        <div className="flex gap-2 flex-wrap">
          <form onChange={handleSubmit(onSubmit)} className="flex gap-2 flex-wrap">
            <div>
              <label className="text-xs text-gray-500 block mb-1">Start Date</label>
              <input
                type="date"
                {...register('start_date')}
                className={`border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                  errors.start_date ? 'border-red-500' : 'border-gray-300'
                }`}
              />
              {errors.start_date && (
                <p className="mt-1 text-xs text-red-500">{errors.start_date.message}</p>
              )}
            </div>
            <div>
              <label className="text-xs text-gray-500 block mb-1">End Date</label>
              <input
                type="date"
                {...register('end_date')}
                className={`border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                  errors.end_date ? 'border-red-500' : 'border-gray-300'
                }`}
              />
              {errors.end_date && (
                <p className="mt-1 text-xs text-red-500">{errors.end_date.message}</p>
              )}
            </div>
          </form>
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
