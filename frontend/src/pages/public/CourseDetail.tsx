import { useQuery } from '@tanstack/react-query';
import { useState } from 'react';
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
    profile_image?: string | null;
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

function StarRating({ rating, size = 'md' }: { rating: number; size?: 'sm' | 'md' | 'lg' }) {
  const sizeClass = size === 'sm' ? 'text-sm' : size === 'lg' ? 'text-xl' : 'text-base';
  return (
    <span className={`${sizeClass} inline-flex items-center gap-0.5`}>
      {[1, 2, 3, 4, 5].map((star) => (
        <span
          key={star}
          className={star <= Math.round(rating) ? 'text-yellow-500' : 'text-gray-300'}
        >
          &#9733;
        </span>
      ))}
    </span>
  );
}

function RatingBar({ stars, count, total }: { stars: number; count: number; total: number }) {
  const pct = total > 0 ? (count / total) * 100 : 0;
  return (
    <div className="flex items-center gap-2 text-sm">
      <span className="w-12 text-right text-gray-700 font-medium">{stars} star</span>
      <div className="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
        <div
          className="h-full bg-yellow-500 rounded-full"
          style={{ width: `${pct}%` }}
        />
      </div>
      <span className="w-10 text-gray-500 text-xs">{pct.toFixed(0)}%</span>
    </div>
  );
}

export default function CourseDetail() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const { addItem } = useCart();
  const [addingToCart, setAddingToCart] = useState(false);
  const [expandedChapters, setExpandedChapters] = useState<Set<string | number>>(new Set());

  const courseQuery = useQuery({
    queryKey: ['course', id],
    enabled: Boolean(id),
    queryFn: async () => {
      const response = await courseApi.get(id || '');
      return {
        course: (response.data?.data?.course as Course | null) ?? null,
        averageRating: response.data?.data?.average_rating || 0,
        totalReviews: response.data?.data?.total_reviews || 0,
      };
    },
  });

  const course = courseQuery.data?.course ?? null;
  const averageRating = courseQuery.data?.averageRating ?? 0;
  const totalReviews = courseQuery.data?.totalReviews ?? 0;
  const loading = courseQuery.isLoading;

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
    } catch {
      alert('Failed to add to cart');
    } finally {
      setAddingToCart(false);
    }
  };

  const toggleChapter = (chapterId: string | number) => {
    setExpandedChapters((prev) => {
      const next = new Set(prev);
      if (next.has(chapterId)) {
        next.delete(chapterId);
      } else {
        next.add(chapterId);
      }
      return next;
    });
  };

  const totalLessons =
    course?.chapters?.reduce((acc, ch) => acc + (ch.lessons?.length || 0), 0) || 0;

  // Rating distribution
  const ratingDist = [5, 4, 3, 2, 1].map((star) => ({
    star,
    count: course?.reviews?.filter((r) => Math.round(r.rating) === star).length || 0,
  }));

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-700" />
      </div>
    );
  }

  if (!course) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Course not found</h2>
          <p className="text-gray-500">The course you're looking for doesn't exist.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white">
      {/* ===== HERO BANNER (Udemy-style dark bar) ===== */}
      <section className="bg-gradient-to-r from-gray-900 via-purple-900 to-gray-900 text-white">
        <div className="max-w-[1340px] mx-auto px-6 lg:px-8 py-10 lg:py-14">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Hero Left Content */}
            <div className="lg:col-span-2">
              <h1 className="text-3xl lg:text-4xl font-extrabold leading-tight mb-4">
                {course.title}
              </h1>
              <p className="text-lg text-purple-100 mb-5 leading-relaxed">
                {course.description}
              </p>

              <div className="flex items-center flex-wrap gap-2 mb-3">
                <span className="text-yellow-400 font-bold text-lg">
                  {averageRating.toFixed(1)}
                </span>
                <StarRating rating={averageRating} size="md" />
                <span className="text-purple-200 text-sm underline cursor-pointer hover:text-white">
                  ({totalReviews.toLocaleString()} ratings)
                </span>
                <span className="text-purple-200 text-sm">
                  {totalReviews.toLocaleString()} students
                </span>
              </div>

              <p className="text-sm text-purple-200 mb-2">
                Created by{' '}
                <span className="text-purple-200 underline cursor-pointer hover:text-white font-medium">
                  {course.instructor?.user?.first_name} {course.instructor?.user?.last_name}
                </span>
              </p>

              <div className="flex items-center gap-4 text-sm text-purple-200">
                {course.created_at && (
                  <span className="flex items-center gap-1">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Last updated {new Date(course.created_at).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}
                  </span>
                )}
                <span className="flex items-center gap-1">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" /></svg>
                  English
                </span>
              </div>
            </div>

            {/* Hero Right - Preview image (hidden on mobile, shown in sidebar card on desktop) */}
            <div className="hidden lg:block" />
          </div>
        </div>
      </section>

      {/* ===== STICKY PRICING SIDEBAR ===== */}
      <div className="max-w-[1340px] mx-auto px-6 lg:px-8">
        <div className="relative -mt-6 lg:-mt-36 flex justify-end">
          <div className="w-full lg:w-[380px] bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden sticky top-24 z-10">
            {/* Thumbnail */}
            {course.images?.[0]?.image_url ? (
              <img
                src={course.images[0].image_url}
                alt={course.title}
                className="w-full h-48 object-cover"
              />
            ) : (
              <div className="w-full h-48 bg-gradient-to-br from-purple-600 to-indigo-700 flex items-center justify-center">
                <svg className="w-16 h-16 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
              </div>
            )}

            {/* Price + CTA */}
            <div className="p-5">
              <div className="flex items-baseline gap-3 mb-5">
                <span className="text-3xl font-extrabold text-gray-900">
                  ${course.price || 0}
                </span>
                {course.price && course.price > 0 && (
                  <span className="text-lg text-gray-400 line-through">
                    ${(course.price * 2.5).toFixed(0)}
                  </span>
                )}
                {course.price && course.price > 0 && (
                  <span className="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded">
                    {Math.round(100 - (course.price / (course.price * 2.5)) * 100)}% off
                  </span>
                )}
              </div>

              {course.price === 0 && (
                <p className="text-green-600 font-semibold text-sm mb-4">Free course</p>
              )}

              {/* Buy Now */}
              <button
                onClick={handleAddToCart}
                disabled={addingToCart}
                className="w-full bg-purple-700 hover:bg-purple-800 disabled:opacity-50 text-white py-3.5 rounded-none font-bold text-base transition-colors mb-2"
              >
                {addingToCart ? 'Adding...' : 'Buy now'}
              </button>

              {/* Add to Cart */}
              <button
                onClick={handleAddToCart}
                disabled={addingToCart}
                className="w-full bg-white hover:bg-gray-50 disabled:opacity-50 text-purple-700 border-2 border-purple-700 py-3.5 rounded-none font-bold text-base transition-colors mb-4"
              >
                Add to cart
              </button>

              <p className="text-center text-xs text-gray-500 mb-5">
                30-Day Money-Back Guarantee Full Access Lifetime
              </p>

              {/* Course includes */}
              <div className="border-t border-gray-100 pt-4 space-y-3">
                <h4 className="text-sm font-bold text-gray-900">This course includes:</h4>
                <ul className="space-y-2.5">
                  <li className="flex items-center gap-3 text-sm text-gray-700">
                    <svg className="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    {course.chapters?.length || 0} chapters of content
                  </li>
                  <li className="flex items-center gap-3 text-sm text-gray-700">
                    <svg className="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    {totalLessons} lessons
                  </li>
                  <li className="flex items-center gap-3 text-sm text-gray-700">
                    <svg className="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    Access on mobile and TV
                  </li>
                  <li className="flex items-center gap-3 text-sm text-gray-700">
                    <svg className="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="MInfinity 13h0m-6-6h0m0 12h0" /></svg>
                    Full lifetime access
                  </li>
                  <li className="flex items-center gap-3 text-sm text-gray-700">
                    <svg className="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Certificate of completion
                  </li>
                </ul>
              </div>

              {/* Share */}
              <div className="border-t border-gray-100 mt-4 pt-4 text-center">
                <button className="text-sm text-purple-700 font-bold hover:underline">
                  Share
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* ===== MAIN CONTENT (below hero, beside sidebar) ===== */}
      <div className="max-w-[1340px] mx-auto px-6 lg:px-8 pb-16">
        <div className="lg:mr-[420px]">

          {/* What you'll learn */}
          {course.objectives && course.objectives.length > 0 && (
            <section className="mt-10 mb-10">
              <div className="bg-gray-50 border border-gray-200 p-6 lg:p-8">
                <h2 className="text-2xl font-extrabold text-gray-900 mb-5">
                  What you'll learn
                </h2>
                <ul className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                  {course.objectives.map((obj) => (
                    <li key={obj.objective_id} className="flex items-start gap-3">
                      <svg className="w-5 h-5 text-purple-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" /></svg>
                      <span className="text-gray-700 text-sm leading-relaxed">{obj.objective}</span>
                    </li>
                  ))}
                </ul>
              </div>
            </section>
          )}

          {/* Course Content */}
          {course.chapters && course.chapters.length > 0 && (
            <section className="mb-10">
              <div className="flex items-baseline justify-between mb-4">
                <h2 className="text-2xl font-extrabold text-gray-900">
                  Course content
                </h2>
              </div>
              <div className="flex items-center gap-4 text-sm text-gray-600 mb-4">
                <span>{course.chapters.length} sections</span>
                <span className="text-gray-300">|</span>
                <span>{totalLessons} lectures</span>
              </div>

              {/* Expand/Collapse All */}
              <button
                onClick={() => {
                  if (expandedChapters.size === course.chapters!.length) {
                    setExpandedChapters(new Set());
                  } else {
                    setExpandedChapters(new Set(course.chapters!.map((c) => c.chapter_id)));
                  }
                }}
                className="text-sm text-purple-700 font-bold mb-3 hover:underline inline-block"
              >
                {expandedChapters.size === course.chapters.length
                  ? 'Collapse all sections'
                  : 'Expand all sections'}
              </button>

              {/* Accordion */}
              <div className="border border-gray-200 rounded-none overflow-hidden">
                {course.chapters.map((chapter, idx) => {
                  const isExpanded = expandedChapters.has(chapter.chapter_id);
                  const lessonCount = chapter.lessons?.length || 0;
                  return (
                    <div key={chapter.chapter_id} className={idx > 0 ? 'border-t border-gray-200' : ''}>
                      {/* Section header */}
                      <button
                        onClick={() => toggleChapter(chapter.chapter_id)}
                        className="w-full flex items-center justify-between px-5 py-4 bg-gray-50 hover:bg-gray-100 transition-colors text-left"
                      >
                        <div className="flex items-center gap-3">
                          <svg
                            className={`w-4 h-4 text-gray-500 transition-transform flex-shrink-0 ${
                              isExpanded ? 'rotate-180' : ''
                            }`}
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                          </svg>
                          <span className="font-bold text-gray-900 text-sm">
                            {chapter.title}
                          </span>
                        </div>
                        <span className="text-xs text-gray-500 flex-shrink-0">
                          {lessonCount} {lessonCount === 1 ? 'lecture' : 'lectures'}
                        </span>
                      </button>

                      {/* Lessons list */}
                      {isExpanded && chapter.lessons && chapter.lessons.length > 0 && (
                        <div className="bg-white">
                          {chapter.lessons.map((lesson, lessonIdx) => (
                            <div
                              key={lesson.lesson_id}
                              className={`flex items-center gap-3 px-5 py-3 pl-12 ${
                                lessonIdx > 0 ? 'border-t border-gray-100' : ''
                              }`}
                            >
                              <svg className="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                              </svg>
                              <span className="text-sm text-gray-700">{lesson.title}</span>
                            </div>
                          ))}
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            </section>
          )}

          {/* Requirements */}
          {course.requirements && course.requirements.length > 0 && (
            <section className="mb-10">
              <h2 className="text-2xl font-extrabold text-gray-900 mb-4">Requirements</h2>
              <ul className="space-y-2">
                {course.requirements.map((req) => (
                  <li key={req.requirement_id} className="flex items-start gap-2 text-gray-700">
                    <span className="text-gray-400 mt-1">&#8226;</span>
                    <span>{req.requirement}</span>
                  </li>
                ))}
              </ul>
            </section>
          )}

          {/* Description */}
          {course.description && (
            <section className="mb-10">
              <h2 className="text-2xl font-extrabold text-gray-900 mb-4">Description</h2>
              <div className="text-gray-700 leading-relaxed whitespace-pre-line">
                {course.description}
              </div>
            </section>
          )}

          {/* Instructor */}
          <section className="mb-10">
            <h2 className="text-2xl font-extrabold text-gray-900 mb-5">Instructor</h2>
            <div className="flex items-start gap-4">
              {/* Avatar */}
              <div className="w-20 h-20 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                {course.instructor?.user?.profile_image ? (
                  <img
                    src={course.instructor.user.profile_image}
                    alt="Instructor"
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <span className="text-2xl font-bold text-purple-700">
                    {course.instructor?.user?.first_name?.[0] || 'I'}
                  </span>
                )}
              </div>
              <div>
                <h3 className="text-lg font-bold text-gray-900">
                  {course.instructor?.user?.first_name} {course.instructor?.user?.last_name}
                </h3>
                <div className="flex items-center gap-2 mt-1 text-sm text-gray-500">
                  <StarRating rating={averageRating} size="sm" />
                  <span>{averageRating.toFixed(1)} rating</span>
                </div>
                <div className="flex items-center gap-4 mt-2 text-sm text-gray-500">
                  <span className="flex items-center gap-1">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    {totalReviews} students
                  </span>
                  <span className="flex items-center gap-1">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    {course.chapters?.length || 0} courses
                  </span>
                </div>
              </div>
            </div>
          </section>

          {/* Reviews */}
          {course.reviews && course.reviews.length > 0 && (
            <section className="mb-10">
              <h2 className="text-2xl font-extrabold text-gray-900 mb-5">Student feedback</h2>

              <div className="flex flex-col md:flex-row gap-8 mb-8">
                {/* Rating summary */}
                <div className="flex flex-col items-center justify-center">
                  <span className="text-5xl font-extrabold text-gray-900">
                    {averageRating.toFixed(1)}
                  </span>
                  <StarRating rating={averageRating} size="lg" />
                  <span className="text-sm text-gray-500 mt-1">Course Rating</span>
                </div>

                {/* Rating bars */}
                <div className="flex-1 space-y-1.5">
                  {ratingDist.map(({ star, count }) => (
                    <RatingBar key={star} stars={star} count={count} total={course.reviews!.length} />
                  ))}
                </div>
              </div>

              {/* Individual reviews */}
              <div className="space-y-6">
                {course.reviews.map((review) => (
                  <div key={review.review_id} className="flex gap-4">
                    {/* Avatar */}
                    <div className="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                      <span className="text-sm font-bold text-gray-600">
                        {review.user?.first_name?.[0] || 'U'}
                      </span>
                    </div>
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-0.5">
                        <span className="font-bold text-gray-900 text-sm">
                          {review.user?.first_name} {review.user?.last_name}
                        </span>
                      </div>
                      <div className="flex items-center gap-2 mb-2">
                        <StarRating rating={review.rating} size="sm" />
                      </div>
                      {review.review_text && (
                        <p className="text-sm text-gray-700 leading-relaxed">
                          {review.review_text}
                        </p>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </section>
          )}
        </div>
      </div>
    </div>
  );
}
