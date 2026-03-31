import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { courseApi, studentApi, lessonApi } from '../../services/api';
import { useAuth } from '../../contexts/AuthContext';

export default function WatchVideo() {
  const { courseId, lessonId } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated, user } = useAuth();
  const [course, setCourse] = useState(null);
  const [hasPurchased, setHasPurchased] = useState(false);
  const [selectedLesson, setSelectedLesson] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('overview');
  const [completedLessons, setCompletedLessons] = useState([]);
  const [showShareModal, setShowShareModal] = useState(false);
  const [copySuccess, setCopySuccess] = useState('');

  useEffect(() => {
    async function fetchData() {
      try {
        if (isAuthenticated) {
          try {
            const purchaseCheck = await studentApi.hasPurchased(courseId);
            setHasPurchased(purchaseCheck.data.data?.has_purchased || false);
          } catch (err) {
            console.error('Failed to check purchase:', err);
          }
        }

        const response = await courseApi.get(courseId);
        const courseData = response.data.data;
        setCourse(courseData);

        const allLessons = courseData.chapters?.flatMap(ch => ch.lessons || []) || [];
        const lesson = allLessons.find(l => l.lesson_id === lessonId) || allLessons[0];
        setSelectedLesson(lesson);

        // Load completed lessons from localStorage
        const saved = localStorage.getItem(`completed_lessons_${courseId}`);
        if (saved) {
          setCompletedLessons(JSON.parse(saved));
        }
      } catch (error) {
        console.error('Failed to fetch course:', error);
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, [courseId, lessonId, isAuthenticated]);

  const toggleLessonComplete = (lessonId) => {
    const newCompleted = completedLessons.includes(lessonId)
      ? completedLessons.filter(id => id !== lessonId)
      : [...completedLessons, lessonId];

    setCompletedLessons(newCompleted);
    localStorage.setItem(`completed_lessons_${courseId}`, JSON.stringify(newCompleted));
  };

  const shareCourse = () => {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
      setCopySuccess('Link copied!');
      setTimeout(() => setCopySuccess(''), 2000);
    });
  };

  const downloadResource = (resource) => {
    const link = document.createElement('a');
    link.href = resource.file_url || resource.url;
    link.download = resource.name || 'resource';
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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

  if (!hasPurchased) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-16 text-center">
        <div className="text-6xl mb-4">🔒</div>
        <h1 className="text-2xl font-bold text-gray-900 mb-4">
          This course is not available
        </h1>
        <p className="text-gray-600 mb-8">
          Please purchase this course to access the content.
        </p>
        <button
          onClick={() => navigate(`/courses/${courseId}`)}
          className="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700"
        >
          View Course Details
        </button>
      </div>
    );
  }

  const allLessons = course.chapters?.flatMap(ch => ch.lessons || []) || [];
  const currentIndex = allLessons.findIndex(l => l.lesson_id === selectedLesson?.lesson_id);
  const prevLesson = allLessons[currentIndex - 1];
  const nextLesson = allLessons[currentIndex + 1];
  const progress = allLessons.length > 0
    ? Math.round((completedLessons.length / allLessons.length) * 100)
    : 0;

  return (
    <div className="max-w-7xl mx-auto px-4 py-4">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Video Player */}
        <div className="lg:col-span-2">
          <div className="bg-black rounded-xl overflow-hidden mb-4">
            {selectedLesson?.videos?.[0]?.url ? (
              <video
                src={selectedLesson.videos[0].url}
                controls
                className="w-full"
                style={{ maxHeight: '500px' }}
                autoPlay={false}
              >
                Your browser does not support the video tag.
              </video>
            ) : (
              <div className="aspect-video flex items-center justify-center text-white">
                <div className="text-center">
                  <div className="text-6xl mb-4">📹</div>
                  <p>Video not available</p>
                </div>
              </div>
            )}
          </div>

          {/* Lesson Title & Actions */}
          <div className="flex flex-wrap justify-between items-center gap-4 mb-4">
            <h1 className="text-2xl font-bold text-gray-900">
              {selectedLesson?.title || 'Select a lesson'}
            </h1>
            <div className="flex gap-2">
              <button
                onClick={() => toggleLessonComplete(selectedLesson?.lesson_id)}
                className={`px-4 py-2 rounded-lg font-medium transition ${
                  completedLessons.includes(selectedLesson?.lesson_id)
                    ? 'bg-green-600 text-white'
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                }`}
              >
                {completedLessons.includes(selectedLesson?.lesson_id) ? '✓ Completed' : 'Mark Complete'}
              </button>
              <button
                onClick={shareCourse}
                className="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition"
              >
                Share
              </button>
            </div>
          </div>
          {copySuccess && (
            <p className="text-green-600 text-sm mb-4">{copySuccess}</p>
          )}

          {/* Progress Bar */}
          <div className="mb-4">
            <div className="flex justify-between text-sm mb-1">
              <span className="text-gray-600">Course Progress</span>
              <span className="font-medium">{progress}%</span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2">
              <div
                className="bg-indigo-600 h-2 rounded-full transition-all"
                style={{ width: `${progress}%` }}
              />
            </div>
            <p className="text-xs text-gray-500 mt-1">
              {completedLessons.length} of {allLessons.length} lessons completed
            </p>
          </div>

          {/* Tabs */}
          <div className="bg-white rounded-xl shadow">
            <div className="border-b">
              <nav className="flex gap-4">
                {[
                  { id: 'overview', label: 'Overview' },
                  { id: 'resources', label: 'Resources' },
                  { id: 'notes', label: 'Notes' },
                  { id: 'announcements', label: 'Announcements' },
                ].map((tab) => (
                  <button
                    key={tab.id}
                    onClick={() => setActiveTab(tab.id)}
                    className={`px-4 py-3 font-medium text-sm border-b-2 transition ${
                      activeTab === tab.id
                        ? 'border-indigo-600 text-indigo-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700'
                    }`}
                  >
                    {tab.label}
                  </button>
                ))}
              </nav>
            </div>

            <div className="p-4">
              {activeTab === 'overview' && (
                <div>
                  <h3 className="font-semibold mb-2">About this lesson</h3>
                  <p className="text-gray-600 mb-4">{selectedLesson?.content || 'No description available.'}</p>
                  {selectedLesson?.videos?.length > 0 && (
                    <div className="text-sm text-gray-500">
                      <span className="font-medium">Duration: </span>
                      {selectedLesson.videos[0].duration || 'N/A'}
                    </div>
                  )}
                </div>
              )}

              {activeTab === 'resources' && (
                <div>
                  <h3 className="font-semibold mb-4">Downloadable Resources</h3>
                  {selectedLesson?.resources?.length > 0 ? (
                    <div className="space-y-2">
                      {selectedLesson.resources.map((resource) => (
                        <div
                          key={resource.resource_id}
                          className="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                        >
                          <div>
                            <p className="font-medium text-gray-900">{resource.name}</p>
                            <p className="text-sm text-gray-500">{resource.type}</p>
                          </div>
                          <button
                            onClick={() => downloadResource(resource)}
                            className="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700"
                          >
                            Download
                          </button>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p className="text-gray-500 text-center py-8">No resources available for this lesson.</p>
                  )}
                </div>
              )}

              {activeTab === 'notes' && (
                <div>
                  <h3 className="font-semibold mb-4">Your Notes</h3>
                  <textarea
                    className="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    rows={6}
                    placeholder="Take notes for this lesson..."
                    value={localStorage.getItem(`note_${selectedLesson?.lesson_id}`) || ''}
                    onChange={(e) => {
                      localStorage.setItem(`note_${selectedLesson?.lesson_id}`, e.target.value);
                    }}
                  />
                  <p className="text-sm text-gray-500 mt-2">
                    Notes are saved automatically in your browser.
                  </p>
                </div>
              )}

              {activeTab === 'announcements' && (
                <div>
                  <h3 className="font-semibold mb-4">Course Announcements</h3>
                  {course.announcements?.length > 0 ? (
                    <div className="space-y-4">
                      {course.announcements.map((announcement, idx) => (
                        <div key={idx} className="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                          <p className="font-medium text-gray-900 mb-1">{announcement.title}</p>
                          <p className="text-gray-600 text-sm">{announcement.content}</p>
                          <p className="text-xs text-gray-400 mt-2">
                            {new Date(announcement.created_at).toLocaleDateString()}
                          </p>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p className="text-gray-500 text-center py-8">No announcements at this time.</p>
                  )}
                </div>
              )}
            </div>
          </div>

          {/* Navigation */}
          <div className="flex justify-between mt-6">
            {prevLesson ? (
              <button
                onClick={() => {
                  setSelectedLesson(prevLesson);
                  navigate(`/watch/${courseId}/${prevLesson.lesson_id}`);
                }}
                className="text-indigo-600 hover:text-indigo-700 font-medium"
              >
                ← Previous: {prevLesson.title}
              </button>
            ) : (
              <div></div>
            )}
            {nextLesson ? (
              <button
                onClick={() => {
                  setSelectedLesson(nextLesson);
                  navigate(`/watch/${courseId}/${nextLesson.lesson_id}`);
                }}
                className="text-indigo-600 hover:text-indigo-700 font-medium"
              >
                Next: {nextLesson.title} →
              </button>
            ) : (
              <div></div>
            )}
          </div>
        </div>

        {/* Course Content Sidebar */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-xl shadow p-4 sticky top-4">
            <h2 className="font-bold text-lg mb-4">{course.title}</h2>
            <div className="mb-4 text-sm text-gray-600">
              <div className="flex justify-between">
                <span>Progress</span>
                <span className="font-medium">{progress}%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2 mt-1">
                <div
                  className="bg-green-600 h-2 rounded-full transition-all"
                  style={{ width: `${progress}%` }}
                />
              </div>
            </div>

            <div className="space-y-4 max-h-[500px] overflow-y-auto">
              {course.chapters?.map((chapter, chIndex) => (
                <div key={chapter.chapter_id}>
                  <h3 className="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <span className="bg-indigo-100 text-indigo-600 text-xs px-2 py-1 rounded">
                      {chIndex + 1}
                    </span>
                    {chapter.title}
                  </h3>
                  <div className="space-y-1 ml-6">
                    {chapter.lessons?.map((lesson, lIndex) => (
                      <button
                        key={lesson.lesson_id}
                        onClick={() => {
                          setSelectedLesson(lesson);
                          navigate(`/watch/${courseId}/${lesson.lesson_id}`);
                        }}
                        className={`block w-full text-left text-sm py-2 px-2 rounded flex items-center gap-2 ${
                          selectedLesson?.lesson_id === lesson.lesson_id
                            ? 'bg-indigo-100 text-indigo-600'
                            : 'text-gray-700 hover:bg-gray-100'
                        }`}
                      >
                        <span className={`text-xs ${
                          completedLessons.includes(lesson.lesson_id)
                            ? 'text-green-600'
                            : 'text-gray-400'
                        }`}>
                          {completedLessons.includes(lesson.lesson_id) ? '✓' : `${lIndex + 1}.`}
                        </span>
                        <span className="flex-1">{lesson.title}</span>
                        {lesson.videos?.length > 0 && (
                          <span className="text-xs">🎬</span>
                        )}
                        {lesson.is_free && (
                          <span className="text-xs bg-green-100 text-green-600 px-1 rounded">Free</span>
                        )}
                      </button>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Share Modal */}
      {showShareModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
          <div className="bg-white rounded-xl p-6 max-w-md mx-4">
            <h3 className="text-lg font-bold mb-4">Share this course</h3>
            <div className="flex gap-2 mb-4">
              <input
                type="text"
                readOnly
                value={window.location.href}
                className="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
              />
              <button
                onClick={() => {
                  navigator.clipboard.writeText(window.location.href);
                  setCopySuccess('Copied!');
                }}
                className="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700"
              >
                Copy
              </button>
            </div>
            <div className="flex gap-2">
              <a
                href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`}
                target="_blank"
                rel="noopener noreferrer"
                className="flex-1 text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Facebook
              </a>
              <a
                href={`https://twitter.com/intent/tweet?url=${encodeURIComponent(window.location.href)}`}
                target="_blank"
                rel="noopener noreferrer"
                className="flex-1 text-center px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600"
              >
                Twitter
              </a>
            </div>
            <button
              onClick={() => setShowShareModal(false)}
              className="mt-4 w-full py-2 text-gray-600 hover:text-gray-800"
            >
              Close
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
