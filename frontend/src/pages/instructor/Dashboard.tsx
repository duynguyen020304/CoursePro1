import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { instructorApi } from '../../services/api';

interface Stats {
  total_courses?: number;
  total_students?: number;
  total_revenue?: number;
  total_reviews?: number;
  average_rating?: number | string;
  total_lessons?: number;
  recent_courses?: Array<{
    course_id: string | number;
    title?: string;
    price?: number;
    difficulty?: string;
  }>;
}

export default function InstructorDashboard() {
  const [stats, setStats] = useState<Stats | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    try {
      setLoading(true);
      const response = await instructorApi.getStats();
      if (response.data.success) {
        setStats(response.data.data);
      }
    } catch (err) {
      setError('Failed to load dashboard statistics');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-50 text-red-600 p-4 rounded-lg">
        {error}
      </div>
    );
  }

  const statCards = [
    { label: 'Total Courses', value: stats?.total_courses || 0, icon: '📚', color: 'bg-blue-500' },
    { label: 'Total Students', value: stats?.total_students || 0, icon: '👨‍🎓', color: 'bg-green-500' },
    { label: 'Total Revenue', value: `$${(stats?.total_revenue || 0).toLocaleString()}`, icon: '💰', color: 'bg-yellow-500' },
    { label: 'Total Reviews', value: stats?.total_reviews || 0, icon: '⭐', color: 'bg-purple-500' },
    { label: 'Avg Rating', value: stats?.average_rating || '0.0', icon: '📊', color: 'bg-pink-500' },
    { label: 'Total Lessons', value: stats?.total_lessons || 0, icon: '📖', color: 'bg-indigo-500' },
  ];

  return (
    <div className="space-y-6">
      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {statCards.map((stat, index) => (
          <div key={index} className="bg-white rounded-lg shadow-sm p-6">
            <div className="flex items-center">
              <div className={`${stat.color} rounded-lg p-3 text-white text-2xl mr-4`}>
                {stat.icon}
              </div>
              <div>
                <p className="text-sm text-gray-500">{stat.label}</p>
                <p className="text-2xl font-bold text-gray-800">{stat.value}</p>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Recent Courses */}
      <div className="bg-white rounded-lg shadow-sm p-6">
        <div className="flex justify-between items-center mb-4">
          <h3 className="text-lg font-semibold text-gray-800">Recent Courses</h3>
          <Link
            to="/instructor/courses"
            className="text-indigo-600 hover:text-indigo-700 text-sm font-medium"
          >
            View All →
          </Link>
        </div>

        {stats?.recent_courses && stats.recent_courses.length > 0 ? (
          <div className="space-y-4">
            {stats.recent_courses.map((course) => (
              <div
                key={course.course_id}
                className="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
              >
                <div>
                  <h4 className="font-medium text-gray-800">{course.title}</h4>
                  <p className="text-sm text-gray-500">
                    ${course.price} • {course.difficulty || 'All Levels'}
                  </p>
                </div>
                <Link
                  to={`/instructor/courses/${course.course_id}/edit`}
                  className="text-indigo-600 hover:text-indigo-700 text-sm font-medium"
                >
                  Edit
                </Link>
              </div>
            ))}
          </div>
        ) : (
          <div className="text-center py-8 text-gray-500">
            <p>No courses yet. Create your first course!</p>
            <Link
              to="/instructor/courses/create"
              className="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
            >
              Create Course
            </Link>
          </div>
        )}
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow-sm p-6">
        <h3 className="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
        <div className="flex gap-4">
          <Link
            to="/instructor/courses/create"
            className="flex-1 bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 transition-colors text-center"
          >
            Create New Course
          </Link>
          <Link
            to="/instructor/profile"
            className="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition-colors text-center"
          >
            Update Profile
          </Link>
        </div>
      </div>
    </div>
  );
}
