import { useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { adminUserApi, courseApi, orderApi } from '../../services/api';

interface Stats {
  totalUsers: number;
  totalCourses: number;
  totalOrders: number;
  totalRevenue: number;
}

interface Notification {
  id: number;
  type: string;
  message: string;
  time: string;
}

interface Order {
  order_id: string;
  total_amount?: number;
  status?: string;
  created_at?: string;
  user?: {
    first_name?: string;
    last_name?: string;
  };
}

export default function AdminDashboard() {
  const [dismissedNotificationIds, setDismissedNotificationIds] = useState<number[]>([]);
  const { data, isLoading } = useQuery<{ stats: Stats; recentOrders: Order[] }>({
    queryKey: ['admin', 'dashboard'],
    queryFn: async () => {
      const [usersRes, coursesRes, ordersRes] = await Promise.all([
        adminUserApi.list({ page: 1, per_page: 1 }).catch(() => null),
        courseApi.list({ page: 1, per_page: 1 }).catch(() => null),
        orderApi.list({ page: 1, per_page: 5 }).catch(() => null),
      ]);

      const recentOrders = (ordersRes?.data?.data ?? []) as Order[];

      return {
        stats: {
          totalUsers: usersRes?.data?.totalItem ?? 0,
          totalCourses: coursesRes?.data?.totalItem ?? 0,
          totalOrders: ordersRes?.data?.totalItem ?? 0,
          totalRevenue: recentOrders.reduce((sum: number, order: Order) => sum + (order.total_amount || 0), 0),
        },
        recentOrders: recentOrders.slice(0, 5),
      };
    },
  });

  const stats = data?.stats ?? {
    totalUsers: 0,
    totalCourses: 0,
    totalOrders: 0,
    totalRevenue: 0,
  };
  const recentOrders = data?.recentOrders ?? [];

  const notifications = useMemo(() => {
    const nextNotifications: Notification[] = [];
    if (stats.totalOrders > 0) {
      nextNotifications.push({
        id: 1,
        type: 'info',
        message: `${stats.totalOrders} new order(s) pending review`,
        time: '5 mins ago',
      });
    }
    if (stats.totalUsers > 10) {
      nextNotifications.push({
        id: 2,
        type: 'success',
        message: '10+ new students joined this week!',
        time: '1 hour ago',
      });
    }

    return nextNotifications.filter(
      (notification) => !dismissedNotificationIds.includes(notification.id)
    );
  }, [dismissedNotificationIds, stats.totalOrders, stats.totalUsers]);

  const dismissNotification = (id: number) => {
    setDismissedNotificationIds((prev) => [...prev, id]);
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
        <span className="text-sm text-gray-500">
          Last updated: {new Date().toLocaleTimeString()}
        </span>
      </div>

      {/* Notifications */}
      {notifications.length > 0 && (
        <div className="mb-6 space-y-2">
          <h2 className="text-sm font-semibold text-gray-700 mb-2">Notifications</h2>
          {notifications.map((notification) => (
            <div
              key={notification.id}
              className={`p-4 rounded-lg flex justify-between items-center ${
                notification.type === 'success'
                  ? 'bg-green-50 border-l-4 border-green-500'
                  : notification.type === 'warning'
                  ? 'bg-yellow-50 border-l-4 border-yellow-500'
                  : 'bg-blue-50 border-l-4 border-blue-500'
              }`}
            >
              <div>
                <p className="text-sm text-gray-800">{notification.message}</p>
                <p className="text-xs text-gray-500 mt-1">{notification.time}</p>
              </div>
              <button
                onClick={() => dismissNotification(notification.id)}
                className="text-gray-400 hover:text-gray-600"
              >
                x
              </button>
            </div>
          ))}
        </div>
      )}

      {/* Quick Links */}
      <div className="mb-8">
        <h2 className="text-sm font-semibold text-gray-700 mb-3">Quick Links</h2>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <Link
            to="/admin/courses"
            className="p-4 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow hover:shadow-lg transition"
          >
            <div className="text-2xl mb-2">📚</div>
            <div className="font-medium">Manage Courses</div>
          </Link>
          <Link
            to="/admin/users"
            className="p-4 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow hover:shadow-lg transition"
          >
            <div className="text-2xl mb-2">👥</div>
            <div className="font-medium">Manage Users</div>
          </Link>
          <Link
            to="/admin/revenue"
            className="p-4 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow hover:shadow-lg transition"
          >
            <div className="text-2xl mb-2">💰</div>
            <div className="font-medium">View Revenue</div>
          </Link>
          <Link
            to="/admin/upload-video"
            className="p-4 bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow hover:shadow-lg transition"
          >
            <div className="text-2xl mb-2">📹</div>
            <div className="font-medium">Upload Video</div>
          </Link>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {/* Users Card */}
        <div className="bg-white rounded-xl shadow p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-600 mb-1">Total Users</p>
              <p className="text-3xl font-bold text-gray-900">{stats.totalUsers.toLocaleString()}</p>
            </div>
            <div className="bg-blue-100 text-blue-600 p-3 rounded-lg">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
          </div>
          <Link to="/admin/users" className="text-sm text-blue-600 hover:text-blue-700 mt-4 inline-block">
            View all users →
          </Link>
        </div>

        {/* Courses Card */}
        <div className="bg-white rounded-xl shadow p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-600 mb-1">Total Courses</p>
              <p className="text-3xl font-bold text-gray-900">{stats.totalCourses}</p>
            </div>
            <div className="bg-green-100 text-green-600 p-3 rounded-lg">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
          </div>
          <Link to="/admin/courses" className="text-sm text-green-600 hover:text-green-700 mt-4 inline-block">
            Manage courses →
          </Link>
        </div>

        {/* Orders Card */}
        <div className="bg-white rounded-xl shadow p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-600 mb-1">Total Orders</p>
              <p className="text-3xl font-bold text-gray-900">{stats.totalOrders.toLocaleString()}</p>
            </div>
            <div className="bg-purple-100 text-purple-600 p-3 rounded-lg">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
          </div>
          <Link to="/admin/orders" className="text-sm text-purple-600 hover:text-purple-700 mt-4 inline-block">
            View orders →
          </Link>
        </div>

        {/* Revenue Card */}
        <div className="bg-white rounded-xl shadow p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-600 mb-1">Total Revenue</p>
              <p className="text-3xl font-bold text-gray-900">${stats.totalRevenue.toLocaleString()}</p>
            </div>
            <div className="bg-yellow-100 text-yellow-600 p-3 rounded-lg">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <Link to="/admin/revenue" className="text-sm text-yellow-600 hover:text-yellow-700 mt-4 inline-block">
            View details →
          </Link>
        </div>
      </div>

      {/* Recent Orders */}
      <div className="bg-white rounded-xl shadow p-6">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-lg font-semibold">Recent Orders</h2>
          <Link to="/admin/orders" className="text-sm text-indigo-600 hover:text-indigo-700">
            View all →
          </Link>
        </div>
        {recentOrders.length > 0 ? (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-3 px-2 text-sm font-medium text-gray-600">Order ID</th>
                  <th className="text-left py-3 px-2 text-sm font-medium text-gray-600">Customer</th>
                  <th className="text-left py-3 px-2 text-sm font-medium text-gray-600">Amount</th>
                  <th className="text-left py-3 px-2 text-sm font-medium text-gray-600">Status</th>
                  <th className="text-left py-3 px-2 text-sm font-medium text-gray-600">Date</th>
                </tr>
              </thead>
              <tbody>
                {recentOrders.map((order) => (
                  <tr key={order.order_id} className="border-b hover:bg-gray-50">
                    <td className="py-3 px-2 text-sm font-medium">{order.order_id?.substring(0, 8)}...</td>
                    <td className="py-3 px-2 text-sm">
                      {order.user?.first_name} {order.user?.last_name}
                    </td>
                    <td className="py-3 px-2 text-sm font-medium text-indigo-600">
                      ${(order.total_amount || 0).toLocaleString()}
                    </td>
                    <td className="py-3 px-2">
                      <span className={`px-2 py-1 rounded text-xs font-medium ${
                        order.status === 'completed' ? 'bg-green-100 text-green-600' :
                        order.status === 'pending' ? 'bg-yellow-100 text-yellow-600' :
                        'bg-gray-100 text-gray-600'
                      }`}>
                        {order.status || 'pending'}
                      </span>
                    </td>
                    <td className="py-3 px-2 text-sm text-gray-500">
                      {order.created_at ? new Date(order.created_at).toLocaleDateString() : ''}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <p className="text-gray-500 text-center py-8">No recent orders</p>
        )}
      </div>
    </div>
  );
}
