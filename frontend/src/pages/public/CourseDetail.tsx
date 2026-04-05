import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { courseApi } from '../../services/api';
import { useAuth } from '../../contexts/AuthContext';
import { useCart } from '../../contexts/CartContext';

interface CourseObjective {
  objective_id: string | number;
  objective: string;
}

interface CourseRequirement {
  requirement_id: string | number;
  requirement: string;
}

interface CourseChapter {
  chapter_id: string | number;
  title: string;
  description?: string | null;
  lessons?: CourseLesson[];
}

interface CourseLesson {
  lesson_id: string | number;
  title: string;
}

interface CourseReview {
  review_id: string | number;
  rating: number;
  review_text?: string | null;
  user?: {
    first_name?: string | null;
    last_name?: string | null;
  };
}

interface CourseImage {
  image_url: string;
}

interface CourseInstructor {
  user?: {
    first_name?: string | null;
    last_name?: string | null;
  };
}

interface Course {
  course_id: string | number;
  title: string;
  description?: string;
  price?: number;
  created_at?: string | null;
  images?: Array<{ image_url?: string; image_path?: string }>;
  instructor?: CourseInstructor;
  objectives?: CourseObjective[];
  requirements?: CourseRequirement[];
  chapters?: CourseChapter[];
  reviews?: CourseReview[];
}

export default function CourseDetail() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const { addItem } = useCart();
  const [course, setCourse] = useState<Course | null>(null);
  const [averageRating, setAverageRating] = useState<number>(0);
  const [totalReviews, setTotalReviews] = useState<number>(0);
  const [loading, setLoading] = useState(true);
  const [addingToCart, setAddingToCart] = useState(false);

  useEffect(() => {
    async function fetchCourse() {
      try {
        const response = await courseApi.get(id || '');
        // New schema: response.data is { course, average_rating, total_reviews }
        setCourse(response.data.data.course);
        setAverageRating(response.data.data.average_rating || 0);
        setTotalReviews(response.data.data.total_reviews || 0);
      } catch (error) {
        console.error('Failed to fetch course:', error);
      } finally {
        setLoading(false);
      }
    }
    if (id) {
      fetchCourse();
    }
  }, [id]);

  const handleAddToCart = async () => {
    if (!isAuthenticated) {
      navigate('/signin');
      return;
    }

    if (!course) return;

    setAddingToCart(true);
    try {
      const result = await addItem(String(course.course_id));
      if (result.success) {
        alert('Added to cart successfully!');
      } else {
        alert(result.message);
      }
    } catch (error) {
      alert('Failed to add to cart');
    } finally {
      setAddingToCart(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  if (!course) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-8">
        <p className="text-center text-gray-500">Course not found</p>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Main Content */}
        <div className="lg:col-span-2">
          {/* Course Header */}
          <div className="mb-8">
            <h1 className="text-3xl font-bold text-gray-900 mb-4">{course.title}</h1>
            <p className="text-gray-600 text-lg mb-4">{course.description}</p>

            <div className="flex items-center gap-4 text-sm text-gray-500">
              <span>Created by {course.instructor?.user?.first_name} {course.instructor?.user?.last_name}</span>
              {course.created_at && (
                <span>Last updated {new Date(course.created_at).toLocaleDateString()}</span>
              )}
            </div>
          </div>

          {/* What you'll learn */}
          {course.objectives && course.objectives.length > 0 && (
            <div className="bg-gray-50 rounded-lg p-6 mb-8">
              <h2 className="text-xl font-semibold mb-4">What you'll learn</h2>
              <ul className="grid grid-cols-1 md:grid-cols-2 gap-2">
                {course.objectives.map((obj, index) => (
                  <li key={obj.objective_id} className="flex items-start gap-2">
                    <span className="text-green-500">✓</span>
                    <span className="text-gray-700">{obj.objective}</span>
                  </li>
                ))}
              </ul>
            </div>
          )}

          {/* Requirements */}
          {course.requirements && course.requirements.length > 0 && (
            <div className="mb-8">
              <h2 className="text-xl font-semibold mb-4">Requirements</h2>
              <ul className="list-disc list-inside text-gray-700 space-y-1">
                {course.requirements.map((req) => (
                  <li key={req.requirement_id}>{req.requirement}</li>
                ))}
              </ul>
            </div>
          )}

          {/* Course Content */}
          {course.chapters && course.chapters.length > 0 && (
            <div className="mb-8">
              <h2 className="text-xl font-semibold mb-4">Course Content</h2>
              <div className="space-y-4">
                {course.chapters.map((chapter) => (
                  <div key={chapter.chapter_id} className="border rounded-lg">
                    <div className="bg-gray-50 px-4 py-3">
                      <h3 className="font-semibold">{chapter.title}</h3>
                      {chapter.description && (
                        <p className="text-sm text-gray-600 mt-1">{chapter.description}</p>
                      )}
                    </div>
                    {chapter.lessons && chapter.lessons.length > 0 && (
                      <div className="divide-y">
                        {chapter.lessons.map((lesson) => (
                          <div key={lesson.lesson_id} className="px-4 py-3 flex items-center gap-2">
                            <span className="text-gray-400">▶</span>
                            <span className="text-gray-700">{lesson.title}</span>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Reviews */}
          {course.reviews && course.reviews.length > 0 && (
            <div>
              <h2 className="text-xl font-semibold mb-4">
                Reviews ({course.reviews.length})
              </h2>
              <div className="space-y-4">
                {course.reviews.map((review) => (
                  <div key={review.review_id} className="border rounded-lg p-4">
                    <div className="flex items-center justify-between mb-2">
                      <span className="font-semibold">
                        {review.user?.first_name} {review.user?.last_name}
                      </span>
                      <span className="text-yellow-500">{'★'.repeat(review.rating)}</span>
                    </div>
                    {review.review_text && (
                      <p className="text-gray-700">{review.review_text}</p>
                    )}
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Sidebar */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-xl shadow-lg p-6 sticky top-24">
            {course.images?.[0]?.image_url && (
              <img
                src={course.images[0].image_url}
                alt={course.title}
                className="w-full h-48 object-cover rounded-lg mb-4"
              />
            )}

            <div className="text-3xl font-bold text-indigo-600 mb-4">
              ${course.price || 0}
            </div>

            <button
              onClick={handleAddToCart}
              disabled={addingToCart}
              className="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 mb-3"
            >
              {addingToCart ? 'Adding...' : 'Add to Cart'}
            </button>

            <div className="space-y-3 text-sm text-gray-600">
              <div className="flex items-center gap-2">
                <span>📚</span>
                <span>{course.chapters?.length || 0} Chapters</span>
              </div>
              <div className="flex items-center gap-2">
                <span>📖</span>
                <span>
                  {course.chapters?.reduce((acc, ch) => acc + (ch.lessons?.length || 0), 0) || 0} Lessons
                </span>
              </div>
              <div className="flex items-center gap-2">
                <span>⭐</span>
                <span>
                  {averageRating.toFixed(1)} ({totalReviews} reviews)
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
