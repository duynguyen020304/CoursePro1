import { useState, useEffect } from 'react';
import { useParams, useSearchParams } from 'react-router-dom';
import { Link } from 'react-router-dom';
import { courseApi, categoryApi } from '../../services/api';

export default function CategoryPage() {
  const { id } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const [category, setCategory] = useState(null);
  const [courses, setCourses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [sortBy, setSortBy] = useState(searchParams.get('sort') || 'highest_rated');
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 6;

  useEffect(() => {
    async function fetchData() {
      try {
        setLoading(true);
        // Fetch category details
        const categoryRes = await categoryApi.get(id);
        setCategory(categoryRes.data.data);

        // Fetch courses in category
        const coursesRes = await courseApi.list({ category_id: id });
        const coursesData = coursesRes.data.data?.data || coursesRes.data.data || [];
        setCourses(Array.isArray(coursesData) ? coursesData : []);
      } catch (error) {
        console.error('Failed to fetch category data:', error);
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, [id]);

  // Apply sorting
  const sortedCourses = [...courses].sort((a, b) => {
    switch (sortBy) {
      case 'highest_rated':
        return (parseFloat(b.average_rating) || 0) - (parseFloat(a.average_rating) || 0);
      case 'newest':
        return new Date(b.created_at || b.createdAt) - new Date(a.created_at || a.createdAt);
      case 'most_popular':
        return (parseInt(b.total_ratings) || 0) - (parseInt(a.total_ratings) || 0);
      default:
        return 0;
    }
  });

  // Pagination
  const totalPages = Math.ceil(sortedCourses.length / itemsPerPage);
  const paginatedCourses = sortedCourses.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );

  const handleSortChange = (newSort) => {
    setSortBy(newSort);
    setSearchParams({ sort: newSort });
    setCurrentPage(1);
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  if (!category) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-16 text-center">
        <h1 className="text-2xl font-bold text-gray-900 mb-4">Category Not Found</h1>
        <Link to="/courses" className="text-indigo-600 hover:text-indigo-700">
          Browse All Courses →
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 py-8">
      {/* Category Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">{category.name}</h1>
        {category.description && (
          <p className="text-gray-600">{category.description}</p>
        )}
        <div className="flex gap-4 mt-4 text-sm text-gray-500">
          {category.total_courses && (
            <span>{category.total_courses} courses</span>
          )}
          {category.total_students && (
            <span>{category.total_students.toLocaleString()} students</span>
          )}
          {category.average_rating && (
            <span className="flex items-center gap-1">
              {parseFloat(category.average_rating).toFixed(1)}
              <span className="text-yellow-500">★</span>
            </span>
          )}
        </div>
      </div>

      {/* Controls */}
      <div className="flex justify-between items-center mb-6">
        <span className="text-gray-600">
          {sortedCourses.length} courses
        </span>
        <select
          value={sortBy}
          onChange={(e) => handleSortChange(e.target.value)}
          className="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="highest_rated">Highest Rated</option>
          <option value="newest">Newest</option>
          <option value="most_popular">Most Popular</option>
        </select>
      </div>

      {/* Courses Grid */}
      {paginatedCourses.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-gray-500 text-lg">No courses found in this category.</p>
        </div>
      ) : (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {paginatedCourses.map((course) => (
              <Link
                key={course.course_id}
                to={`/courses/${course.course_id}`}
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
                  <div className="flex items-center gap-2 mb-2">
                    <span className="text-yellow-500 font-semibold">
                      {(course.average_rating || 0).toFixed(1)} ★
                    </span>
                    <span className="text-gray-400 text-sm">
                      ({course.total_ratings || 0})
                    </span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-indigo-600 font-bold">
                      ${course.price || 0}
                    </span>
                    <span className="text-sm text-gray-500">
                      {course.total_lessons || 0} lessons
                    </span>
                  </div>
                </div>
              </Link>
            ))}
          </div>

          {/* Pagination */}
          {totalPages > 1 && (
            <div className="flex justify-center gap-2 mt-8">
              <button
                onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
                disabled={currentPage === 1}
                className="px-4 py-2 border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => (
                <button
                  key={page}
                  onClick={() => setCurrentPage(page)}
                  className={`px-4 py-2 border rounded-lg ${
                    currentPage === page
                      ? 'bg-indigo-600 text-white border-indigo-600'
                      : 'hover:bg-gray-50'
                  }`}
                >
                  {page}
                </button>
              ))}
              <button
                onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
                disabled={currentPage === totalPages}
                className="px-4 py-2 border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
}
