import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { courseApi, categoryApi, instructorPublicApi } from '../../services/api';
import { Swiper, SwiperSlide } from 'swiper/react';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

export default function Home() {
  const [featuredCourses, setFeaturedCourses] = useState([]);
  const [categories, setCategories] = useState([]);
  const [instructors, setInstructors] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchData() {
      try {
        const [coursesRes, categoriesRes, instructorsRes] = await Promise.all([
          courseApi.list({ limit: 10 }),
          categoryApi.list({ nested: true }),
          instructorPublicApi.list({ limit: 6 }),
        ]);

        const coursesData = coursesRes.data.data?.data || coursesRes.data.data || [];
        const categoriesData = categoriesRes.data.data || [];
        const instructorsData = instructorsRes.data.data?.data || instructorsRes.data.data || [];

        setFeaturedCourses(Array.isArray(coursesData) ? coursesData : []);
        setCategories(Array.isArray(categoriesData) ? categoriesData : []);
        setInstructors(Array.isArray(instructorsData) ? instructorsData : []);
      } catch (error) {
        console.error('Failed to fetch homepage data:', error);
      } finally {
        setLoading(false);
      }
    }
    fetchData();
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
      {/* Hero Section */}
      <section className="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-20 relative overflow-hidden">
        <div
          className="absolute inset-0 opacity-10"
          style={{
            backgroundImage: 'url("https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=1920")',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }}
        />
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
          <h1 className="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">
            Learn Without Limits
          </h1>
          <p className="text-xl md:text-2xl mb-8 text-indigo-100">
            Access thousands of courses from industry experts
          </p>
          <div className="flex justify-center gap-4 flex-wrap">
            <Link
              to="/courses"
              className="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition shadow-lg"
            >
              Explore Courses
            </Link>
            <Link
              to="/signup"
              className="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition"
            >
              Get Started
            </Link>
          </div>
        </div>
      </section>

      {/* Featured Courses - Swiper Slider */}
      <section className="py-16 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">
              Featured Courses
            </h2>
            <p className="text-gray-600">
              Discover our most popular courses
            </p>
          </div>

          {featuredCourses.length > 0 ? (
            <Swiper
              modules={[Navigation, Pagination, Autoplay]}
              spaceBetween={30}
              slidesPerView={1}
              navigation
              pagination={{ clickable: true }}
              autoplay={{ delay: 5000 }}
              breakpoints={{
                640: { slidesPerView: 2 },
                768: { slidesPerView: 2 },
                1024: { slidesPerView: 3 },
              }}
              className="!pb-12"
            >
              {featuredCourses.map((course) => (
                <SwiperSlide key={course.course_id}>
                  <Link
                    to={`/courses/${course.course_id}`}
                    className="block bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition h-full"
                  >
                    {course.images?.[0]?.image_url ? (
                      <img
                        src={course.images[0].image_url}
                        alt={course.title}
                        className="w-full h-48 object-cover"
                        onError={(e) => {
                          e.target.src = 'https://placehold.co/400x200/E2E8F0/94A3B8?text=No+Image';
                        }}
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
                      <p className="text-gray-600 text-sm mb-3">
                        {course.instructor?.user?.first_name} {course.instructor?.user?.last_name}
                      </p>
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-1">
                          <span className="text-yellow-500 font-semibold">
                            {(course.average_rating || 0).toFixed(1)}
                          </span>
                          <span className="text-gray-400 text-sm">
                            ({course.total_ratings || 0})
                          </span>
                        </div>
                        <span className="text-indigo-600 font-bold">
                          ${course.price || 0}
                        </span>
                      </div>
                    </div>
                  </Link>
                </SwiperSlide>
              ))}
            </Swiper>
          ) : (
            <div className="text-center py-12">
              <p className="text-gray-500">No featured courses available.</p>
            </div>
          )}

          <div className="text-center mt-8">
            <Link
              to="/courses"
              className="text-indigo-600 font-semibold hover:text-indigo-700"
            >
              View All Courses →
            </Link>
          </div>
        </div>
      </section>

      {/* Categories */}
      <section className="py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">
              Browse Categories
            </h2>
            <p className="text-gray-600">
              Find courses that match your interests
            </p>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {categories.slice(0, 8).map((category) => (
              <Link
                key={category.id}
                to={`/categories/${category.id}`}
                className="bg-gradient-to-br from-indigo-50 to-purple-50 p-6 rounded-xl shadow-md hover:shadow-lg transition text-center"
              >
                <div className="text-3xl mb-3">
                  {category.name === 'Technology' ? '💻' :
                   category.name === 'Business' ? '📊' :
                   category.name === 'Design' ? '🎨' :
                   category.name === 'Marketing' ? '📈' :
                   category.name === 'Photography' ? '📷' :
                   category.name === 'Music' ? '🎵' : '📚'}
                </div>
                <h3 className="font-semibold text-gray-900">{category.name}</h3>
                {category.children?.length > 0 && (
                  <p className="text-sm text-gray-500 mt-1">
                    {category.children.length} subcategories
                  </p>
                )}
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* About Section */}
      <section className="py-16 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-2 gap-12 items-center">
            <div>
              <h2 className="text-3xl font-bold text-gray-900 mb-6">
                About CoursePro
              </h2>
              <p className="text-gray-600 mb-6 leading-relaxed">
                We provide high-quality online courses to help you improve your skills and advance your career.
                Join us to learn from industry experts across various fields! With our experienced team of
                instructors and up-to-date curriculum, we're committed to delivering the best learning experience.
              </p>
              <Link
                to="/courses"
                className="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition"
              >
                View Courses
              </Link>
            </div>
            <div>
              <img
                src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600"
                alt="About us"
                className="rounded-xl shadow-lg w-full h-80 object-cover"
              />
            </div>
          </div>
        </div>
      </section>

      {/* Instructors - Swiper Slider */}
      <section className="py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">
              Top Instructors
            </h2>
            <p className="text-gray-600">
              Learn from industry experts
            </p>
          </div>

          {instructors.length > 0 ? (
            <Swiper
              modules={[Navigation, Pagination, Autoplay]}
              spaceBetween={30}
              slidesPerView={1}
              navigation
              pagination={{ clickable: true }}
              autoplay={{ delay: 5000 }}
              breakpoints={{
                640: { slidesPerView: 2 },
                768: { slidesPerView: 2 },
                1024: { slidesPerView: 3 },
              }}
              className="!pb-12"
            >
              {instructors.map((instructor) => (
                <SwiperSlide key={instructor.instructor_id}>
                  <div className="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition text-center p-6">
                    <img
                      src={instructor.user?.profile_image || 'https://placehold.co/200x200/E2E8F0/94A3B8?text=Instructor'}
                      alt={`${instructor.user?.first_name} ${instructor.user?.last_name}`}
                      className="w-32 h-32 rounded-full mx-auto mb-4 object-cover"
                      onError={(e) => {
                        e.target.src = 'https://placehold.co/200x200/E2E8F0/94A3B8?text=Instructor';
                      }}
                    />
                    <h3 className="font-semibold text-lg text-gray-900">
                      {instructor.user?.first_name} {instructor.user?.last_name}
                    </h3>
                    <p className="text-indigo-600 text-sm">
                      {instructor.biography ? instructor.biography.substring(0, 80) + '...' : 'Expert Instructor'}
                    </p>
                  </div>
                </SwiperSlide>
              ))}
            </Swiper>
          ) : (
            <div className="text-center py-12">
              <p className="text-gray-500">No instructors available.</p>
            </div>
          )}
        </div>
      </section>

      {/* Testimonials - Swiper Slider */}
      <section className="py-16 bg-gradient-to-br from-indigo-600 to-purple-600 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold mb-4">
              What Our Students Say
            </h2>
            <p className="text-indigo-100">
              Join thousands of satisfied learners
            </p>
          </div>

          <Swiper
            modules={[Navigation, Pagination, Autoplay]}
            spaceBetween={30}
            slidesPerView={1}
            navigation
            pagination={{ clickable: true }}
            autoplay={{ delay: 5000 }}
            breakpoints={{
              640: { slidesPerView: 2 },
              768: { slidesPerView: 3 },
            }}
            className="!pb-12"
          >
            {[
              {
                name: 'Nguyen Van A',
                course: 'Web Development',
                text: 'The web development course really helped me improve my programming skills and get a better job. The instructor was enthusiastic and the content was detailed.',
                rating: 5,
              },
              {
                name: 'Tran Thi B',
                course: 'Digital Marketing',
                text: 'The online marketing course was very useful. I applied what I learned to my work and saw clear results. Thank you to the platform for providing quality courses!',
                rating: 5,
              },
              {
                name: 'Le Van C',
                course: 'Graphic Design',
                text: 'The instructor was very enthusiastic and easy to understand, helping me quickly grasp graphic design skills. Very satisfied! Will recommend to family and friends.',
                rating: 5,
              },
            ].map((testimonial, idx) => (
              <SwiperSlide key={idx}>
                <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 h-full">
                  <div className="flex items-center gap-4 mb-4">
                    <div className="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold">
                      {testimonial.name.charAt(0)}
                    </div>
                    <div>
                      <h4 className="font-semibold">{testimonial.name}</h4>
                      <p className="text-indigo-200 text-sm">{testimonial.course} Student</p>
                    </div>
                  </div>
                  <p className="text-indigo-100 italic mb-4">"{testimonial.text}"</p>
                  <div className="flex gap-1 text-yellow-400">
                    {[...Array(testimonial.rating)].map((_, i) => (
                      <span key={i}>★</span>
                    ))}
                  </div>
                </div>
              </SwiperSlide>
            ))}
          </Swiper>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-16 bg-gray-900 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 className="text-3xl font-bold mb-4">
            Ready to Start Learning?
          </h2>
          <p className="text-xl mb-8 text-gray-300">
            Join thousands of students already learning on CoursePro
          </p>
          <Link
            to="/signup"
            className="bg-indigo-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition inline-block"
          >
            Create Free Account
          </Link>
        </div>
      </section>
    </div>
  );
}
