import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { instructorApi, categoryApi, courseApi } from '../../services/api';

interface Category {
  id: string | number;
  name?: string;
}

interface Course {
  title?: string;
  description?: string | null;
  price?: number;
  difficulty?: string;
  language?: string;
  categories?: Array<{ id: string | number }>;
  objectives?: Array<{ objective?: string }>;
  requirements?: Array<{ requirement?: string }>;
  chapters?: Chapter[];
}

interface Chapter {
  chapter_id: string | number;
  title?: string;
  lessons?: Lesson[];
}

interface Lesson {
  lesson_id: string | number;
  title?: string;
}

interface FormData {
  title: string;
  description: string;
  price: string;
  difficulty: string;
  language: string;
  category_ids: (string | number)[];
  objectives: string[];
  requirements: string[];
}

export default function EditCourse() {
  const { courseId } = useParams<{ courseId: string }>();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [categories, setCategories] = useState<Category[]>([]);
  const [course, setCourse] = useState<Course | null>(null);
  const [activeTab, setActiveTab] = useState('details');
  const [formData, setFormData] = useState<FormData>({
    title: '',
    description: '',
    price: '',
    difficulty: 'All Level',
    language: 'Vietnamese',
    category_ids: [],
    objectives: [''],
    requirements: [''],
  });
  const [chapters, setChapters] = useState<Chapter[]>([]);
  const [errors, setErrors] = useState<{ load?: string }>({});

  useEffect(() => {
    fetchCourse();
    fetchCategories();
  }, [courseId]);

  const fetchCourse = async () => {
    try {
      setLoading(true);
      const response = await instructorApi.getCourse(courseId!);
      if (response.data.success) {
        const courseData = response.data.data;
        setCourse(courseData);
        setFormData({
          title: courseData.title || '',
          description: courseData.description || '',
          price: courseData.price?.toString() || '',
          difficulty: courseData.difficulty || 'All Level',
          language: courseData.language || 'Vietnamese',
          category_ids: courseData.categories?.map((c: { id: string | number }) => c.id) || [],
          objectives: courseData.objectives?.map((o: { objective?: string }) => o.objective || '') || [''],
          requirements: courseData.requirements?.map((r: { requirement?: string }) => r.requirement || '') || [''],
        });
        setChapters(courseData.chapters || []);
      }
    } catch (err) {
      console.error('Failed to fetch course:', err);
      setErrors({ load: 'Failed to load course' });
    } finally {
      setLoading(false);
    }
  };

  const fetchCategories = async () => {
    try {
      const response = await categoryApi.list();
      if (response.data.success) {
        setCategories(response.data.data as Category[]);
      }
    } catch (err) {
      console.error('Failed to fetch categories:', err);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleCategoryChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const options = e.target.options;
    const selected: (string | number)[] = [];
    for (let i = 0; i < options.length; i++) {
      if (options[i].selected) {
        selected.push(parseInt(options[i].value));
      }
    }
    setFormData((prev) => ({ ...prev, category_ids: selected }));
  };

  const handleArrayChange = (field: keyof FormData, index: number, value: string) => {
    const newArray = [...(formData[field] as string[])];
    newArray[index] = value;
    setFormData((prev) => ({ ...prev, [field]: newArray }));
  };

  const addArrayItem = (field: 'objectives' | 'requirements') => {
    setFormData((prev) => ({ ...prev, [field]: [...prev[field], ''] }));
  };

  const removeArrayItem = (field: 'objectives' | 'requirements', index: number) => {
    if (formData[field].length > 1) {
      const newArray = formData[field].filter((_, i) => i !== index);
      setFormData((prev) => ({ ...prev, [field]: newArray }));
    }
  };

  const handleSaveDetails = async () => {
    try {
      setSaving(true);
      const payload = {
        title: formData.title,
        description: formData.description,
        price: parseFloat(formData.price),
        difficulty: formData.difficulty,
        language: formData.language,
        category_ids: formData.category_ids,
        objectives: formData.objectives.filter((o) => o.trim()),
        requirements: formData.requirements.filter((r) => r.trim()),
      };

      await instructorApi.updateCourse(courseId!, payload);
      alert('Course updated successfully!');
    } catch (err) {
      console.error('Failed to update course:', err);
      alert('Failed to update course');
    } finally {
      setSaving(false);
    }
  };

  // Chapter management
  const [newChapter, setNewChapter] = useState({ title: '', description: '' });
  const [addingChapter, setAddingChapter] = useState(false);

  const handleAddChapter = async () => {
    if (!newChapter.title.trim()) return;

    try {
      setAddingChapter(true);
      const response = await courseApi.addChapter(courseId!, {
        title: newChapter.title,
        description: newChapter.description,
        sort_order: chapters.length,
      });

      if (response.data.success) {
        setChapters([...chapters, response.data.data as Chapter]);
        setNewChapter({ title: '', description: '' });
      }
    } catch (err) {
      console.error('Failed to add chapter:', err);
      alert('Failed to add chapter');
    } finally {
      setAddingChapter(false);
    }
  };

  const handleDeleteChapter = async (chapterId: string | number) => {
    if (!window.confirm('Delete this chapter and all its lessons?')) return;

    try {
      await courseApi.deleteChapter(courseId!, chapterId);
      setChapters(chapters.filter((c) => c.chapter_id !== chapterId));
    } catch (err) {
      console.error('Failed to delete chapter:', err);
      alert('Failed to delete chapter');
    }
  };

  // Lesson management
  const [newLesson, setNewLesson] = useState({ chapterId: '', title: '', content: '' });
  const [addingLesson, setAddingLesson] = useState(false);

  const handleAddLesson = async () => {
    if (!newLesson.chapterId || !newLesson.title.trim()) return;

    try {
      setAddingLesson(true);
      const chapter = chapters.find((c) => c.chapter_id === newLesson.chapterId);
      const response = await courseApi.addLesson(courseId!, newLesson.chapterId, {
        title: newLesson.title,
        content: newLesson.content,
        sort_order: chapter?.lessons?.length || 0,
      });

      if (response.data.success) {
        const lesson = response.data.data as Lesson;
        // Update chapters with new lesson
        setChapters(
          chapters.map((c) => {
            if (c.chapter_id === newLesson.chapterId) {
              return {
                ...c,
                lessons: [...(c.lessons || []), lesson],
              };
            }
            return c;
          })
        );
        setNewLesson({ chapterId: '', title: '', content: '' });
      }
    } catch (err) {
      console.error('Failed to add lesson:', err);
      alert('Failed to add lesson');
    } finally {
      setAddingLesson(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  if (errors.load) {
    return (
      <div className="bg-red-50 text-red-600 p-4 rounded-lg">{errors.load}</div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Edit Course</h1>
        <button
          onClick={() => navigate('/instructor/courses')}
          className="text-gray-600 hover:text-gray-800"
        >
          ← Back to My Courses
        </button>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200">
        <nav className="-mb-px flex gap-6">
          {['details', 'content'].map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`py-3 px-1 border-b-2 font-medium text-sm capitalize ${
                activeTab === tab
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700'
              }`}
            >
              {tab === 'details' ? 'Course Details' : 'Course Content'}
            </button>
          ))}
        </nav>
      </div>

      {/* Details Tab */}
      {activeTab === 'details' && (
        <div className="space-y-6">
          <div className="bg-white rounded-lg shadow-sm p-6">
            <h2 className="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>

            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Course Title *</label>
                <input
                  type="text"
                  name="title"
                  value={formData.title}
                  onChange={handleChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                  name="description"
                  value={formData.description}
                  onChange={handleChange}
                  rows={4}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div className="grid grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Price ($) *</label>
                  <input
                    type="number"
                    name="price"
                    value={formData.price}
                    onChange={handleChange}
                    min="0"
                    step="0.01"
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                  <select
                    name="difficulty"
                    value={formData.difficulty}
                    onChange={handleChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  >
                    <option value="All Level">All Levels</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Expert">Expert</option>
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Language</label>
                  <select
                    name="language"
                    value={formData.language}
                    onChange={handleChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  >
                    <option value="Vietnamese">Vietnamese</option>
                    <option value="English">English</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Categories</label>
                <select
                  multiple
                  value={formData.category_ids.map(String)}
                  onChange={handleCategoryChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg h-32"
                >
                  {categories.map((cat) => (
                    <option key={cat.id} value={cat.id}>
                      {cat.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          {/* Objectives */}
          <div className="bg-white rounded-lg shadow-sm p-6">
            <h2 className="text-lg font-semibold text-gray-800 mb-4">Learning Objectives</h2>
            {formData.objectives.map((objective, index) => (
              <div key={index} className="flex gap-2 mb-2">
                <input
                  type="text"
                  value={objective}
                  onChange={(e) => handleArrayChange('objectives', index, e.target.value)}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                />
                <button
                  type="button"
                  onClick={() => removeArrayItem('objectives', index)}
                  className="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg"
                >
                  ✕
                </button>
              </div>
            ))}
            <button
              type="button"
              onClick={() => addArrayItem('objectives')}
              className="mt-2 text-indigo-600 hover:text-indigo-700 text-sm font-medium"
            >
              + Add Objective
            </button>
          </div>

          {/* Requirements */}
          <div className="bg-white rounded-lg shadow-sm p-6">
            <h2 className="text-lg font-semibold text-gray-800 mb-4">Requirements</h2>
            {formData.requirements.map((requirement, index) => (
              <div key={index} className="flex gap-2 mb-2">
                <input
                  type="text"
                  value={requirement}
                  onChange={(e) => handleArrayChange('requirements', index, e.target.value)}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                />
                <button
                  type="button"
                  onClick={() => removeArrayItem('requirements', index)}
                  className="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg"
                >
                  ✕
                </button>
              </div>
            ))}
            <button
              type="button"
              onClick={() => addArrayItem('requirements')}
              className="mt-2 text-indigo-600 hover:text-indigo-700 text-sm font-medium"
            >
              + Add Requirement
            </button>
          </div>

          <div className="flex justify-end">
            <button
              onClick={handleSaveDetails}
              disabled={saving}
              className="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
            >
              {saving ? 'Saving...' : 'Save Changes'}
            </button>
          </div>
        </div>
      )}

      {/* Content Tab */}
      {activeTab === 'content' && (
        <div className="space-y-6">
          {/* Add Chapter */}
          <div className="bg-white rounded-lg shadow-sm p-6">
            <h2 className="text-lg font-semibold text-gray-800 mb-4">Add New Chapter</h2>
            <div className="flex gap-4">
              <input
                type="text"
                placeholder="Chapter title"
                value={newChapter.title}
                onChange={(e) => setNewChapter({ ...newChapter, title: e.target.value })}
                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
              />
              <input
                type="text"
                placeholder="Description (optional)"
                value={newChapter.description}
                onChange={(e) => setNewChapter({ ...newChapter, description: e.target.value })}
                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
              />
              <button
                onClick={handleAddChapter}
                disabled={addingChapter || !newChapter.title.trim()}
                className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
              >
                Add
              </button>
            </div>
          </div>

          {/* Chapters List */}
          <div className="bg-white rounded-lg shadow-sm p-6">
            <h2 className="text-lg font-semibold text-gray-800 mb-4">Chapters & Lessons</h2>

            {chapters.length > 0 ? (
              <div className="space-y-4">
                {chapters.map((chapter, chapterIndex) => (
                  <div key={chapter.chapter_id} className="border rounded-lg">
                    <div className="flex items-center justify-between p-4 bg-gray-50">
                      <div className="flex items-center gap-3">
                        <span className="text-gray-500 font-medium">#{chapterIndex + 1}</span>
                        <h3 className="font-medium text-gray-800">{chapter.title}</h3>
                      </div>
                      <button
                        onClick={() => handleDeleteChapter(chapter.chapter_id)}
                        className="text-red-600 hover:text-red-700 text-sm"
                      >
                        Delete
                      </button>
                    </div>

                    {/* Lessons */}
                    <div className="p-4 border-t">
                      {chapter.lessons && chapter.lessons.length > 0 ? (
                        <ul className="space-y-2">
                          {chapter.lessons.map((lesson, lessonIndex) => (
                            <li
                              key={lesson.lesson_id}
                              className="flex items-center justify-between p-2 bg-gray-50 rounded"
                            >
                              <div>
                                <span className="text-gray-500 text-sm mr-2">
                                  {chapterIndex + 1}.{lessonIndex + 1}
                                </span>
                                {lesson.title}
                              </div>
                            </li>
                          ))}
                        </ul>
                      ) : (
                        <p className="text-gray-500 text-sm">No lessons yet</p>
                      )}

                      {/* Add Lesson */}
                      <div className="mt-4 pt-4 border-t">
                        <div className="flex gap-2">
                          <input
                            type="text"
                            placeholder="New lesson title"
                            value={newLesson.chapterId === chapter.chapter_id ? newLesson.title : ''}
                            onChange={(e) =>
                              setNewLesson({
                                chapterId: String(chapter.chapter_id),
                                title: e.target.value,
                                content: '',
                              })
                            }
                            className="flex-1 px-3 py-1 text-sm border border-gray-300 rounded"
                          />
                          <button
                            onClick={handleAddLesson}
                            disabled={addingLesson || newLesson.chapterId !== chapter.chapter_id || !newLesson.title.trim()}
                            className="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded disabled:opacity-50"
                          >
                            + Add Lesson
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-gray-500 text-center py-8">No chapters yet. Add your first chapter above.</p>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
