import { useQuery } from '@tanstack/react-query';
import { courseApi } from '../../services/api';

interface Course {
  course_id: string | number;
  title: string;
  price?: number | string;
  images?: Array<{ image_url?: string; image_path?: string }>;
  instructor?: {
    user?: {
      first_name?: string | null;
      last_name?: string | null;
    };
  };
}

export default function CourseManagement() {
  const { data: courses = [], isLoading } = useQuery<Course[]>({
    queryKey: ['admin', 'courses'],
    queryFn: async () => {
      const response = await courseApi.list();
      return (response.data.data ?? []) as Course[];
    },
  });

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Course Management</h1>
      </div>

      {isLoading ? (
        <div className="flex justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
      ) : (
        <div className="bg-white rounded-xl shadow overflow-hidden">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Course
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Instructor
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Price
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {courses.map((course) => (
                <tr key={course.course_id} className="hover:bg-gray-50">
                  <td className="px-6 py-4">
                    <div className="flex items-center">
                      {course.images?.[0]?.image_url ? (
                        <img
                          src={course.images[0].image_url || course.images[0].image_path}
                          alt={course.title}
                          className="h-10 w-10 rounded object-cover"
                        />
                      ) : (
                        <div className="h-10 w-10 rounded bg-gray-200" />
                      )}
                      <span className="ml-3 font-medium text-gray-900">{course.title}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-500">
                    {course.instructor?.user?.first_name} {course.instructor?.user?.last_name}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-900">
                    ${parseFloat(String(course.price) || '0').toFixed(2)}
                  </td>
                  <td className="px-6 py-4">
                    <span className="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                      Published
                    </span>
                  </td>
                  <td className="px-6 py-4 text-sm font-medium">
                    <button className="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button
                      type="button"
                      onClick={() => alert('Course deletion is not wired up in the API client yet.')}
                      className="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

    </div>
  );
}
