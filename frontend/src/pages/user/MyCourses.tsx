import { useQuery } from '@tanstack/react-query';
import { studentApi } from '../../services/api';
import { Link } from 'react-router-dom';

interface Course {
  course_id: string | number;
  title: string;
  images?: Array<{ image_url: string }>;
  instructor?: {
    user?: {
      first_name?: string;
      last_name?: string;
    };
  };
  chapters?: Array<{ chapter_id: string | number }>;
}

export default function MyCourses() {
  const { data: courses = [], isLoading } = useQuery<Course[]>({
    queryKey: ['student', 'purchased-courses'],
    queryFn: async () => {
      const response = await studentApi.getProfile();
      return response.data.data?.purchased_courses || [];
    },
  });

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 mb-8">My Courses</h1>

      {courses.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-gray-500 text-lg mb-4">You haven&apos;t purchased any courses yet.</p>
          <Link
            to="/courses"
            className="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700"
          >
            Browse Courses
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {courses.map((course) => (
            <Link
              key={course.course_id}
              to={`/watch/${course.course_id}`}
              className="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition"
            >
              {course.images?.[0]?.image_url ? (
                <img
                  src={course.images[0].image_url}
                  alt={course.title}
                  className="w-full h-48 object-cover"
                />
              ) : (
                <div className="w-full h-48 bg-gray-200 flex items-center justify-center">
                  <span className="text-gray-400">No image</span>
                </div>
              )}
              <div className="p-4">
                <h3 className="font-semibold text-lg text-gray-900 mb-2 line-clamp-2">
                  {course.title}
                </h3>
                <p className="text-gray-600 text-sm mb-2">
                  {course.instructor?.user?.first_name} {course.instructor?.user?.last_name}
                </p>
                <div className="flex items-center justify-between">
                  <span className="text-xs text-gray-500">
                    {course.chapters?.length || 0} chapters
                  </span>
                  <span className="text-indigo-600 text-sm font-medium">
                    Start learning →
                  </span>
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
