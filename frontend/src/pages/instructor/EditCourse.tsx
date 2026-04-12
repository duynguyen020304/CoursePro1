import { useEffect, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useParams, useNavigate } from 'react-router-dom';
import { instructorApi, categoryApi, courseApi } from '../../services/api';

interface Category {
  id: string;
  name?: string;
}

interface Course {
  title?: string;
  description?: string | null;
  price?: number;
  difficulty?: string;
  language?: string;
  categories?: Array<{ id: string }>;
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
  category_ids: string[];
  objectives: string[];
  requirements: string[];
}

export default function EditCourse() {
  const { courseId } = useParams<{ courseId: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
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
  const [errors, setErrors] = useState<{ load?: string }>({});
  const [newChapter, setNewChapter] = useState({ title: '', description: '' });
  const [newLesson, setNewLesson] = useState({ chapterId: '', title: '', content: '' });

  const { data: course, isLoading, error } = useQuery<Course | null>({
    queryKey: ['instructor', 'course', courseId],
    enabled: Boolean(courseId),
    queryFn: async () => {
      const response = await instructorApi.getCourse(courseId!);
      return response.data.success ? (response.data.data as Course) : null;
    },
  });

  const { data: categories = [] } = useQuery<Category[]>({
    queryKey: ['instructor', 'course-edit', 'categories'],
    queryFn: async () => {
      const response = await categoryApi.list();
      return response.data.success ? (response.data.data as Category[]) : [];
    },
  });

  useEffect(() => {
    if (!course) {
      return;
    }

    setFormData({
      title: course.title || '',
      description: course.description || '',
      price: course.price?.toString() || '',
      difficulty: course.difficulty || 'All Level',
      language: course.language || 'Vietnamese',
      category_ids: course.categories?.map((category) => category.id) || [],
      objectives: course.objectives?.map((objective) => objective.objective || '') || [''],
      requirements: course.requirements?.map((requirement) => requirement.requirement || '') || [''],
    });
  }, [course]);

  const refreshCourse = () => {
    void queryClient.invalidateQueries({ queryKey: ['instructor', 'course', courseId] });
    void queryClient.invalidateQueries({ queryKey: ['instructor', 'courses'] });
    void queryClient.invalidateQueries({ queryKey: ['courses'] });
  };

  const updateCourseMutation = useMutation({
    mutationFn: async () => {
      return instructorApi.updateCourse(courseId!, {
        title: formData.title,
        description: formData.description,
        price: parseFloat(formData.price),
        difficulty: formData.difficulty,
        language: formData.language,
        category_ids: formData.category_ids,
        objectives: formData.objectives.filter((objective) => objective.trim()),
        requirements: formData.requirements.filter((requirement) => requirement.trim()),
      });
    },
    onSuccess: () => {
      refreshCourse();
      alert('Course updated successfully!');
    },
    onError: (err) => {
      console.error('Failed to update course:', err);
      alert('Failed to update course');
    },
  });

  const addChapterMutation = useMutation({
    mutationFn: async () => {
      return courseApi.addChapter(courseId!, {
        title: newChapter.title,
        description: newChapter.description,
        sort_order: course?.chapters?.length || 0,
      });
    },
    onSuccess: () => {
      setNewChapter({ title: '', description: '' });
      refreshCourse();
    },
    onError: (err) => {
      console.error('Failed to add chapter:', err);
      alert('Failed to add chapter');
    },
  });

  const deleteChapterMutation = useMutation({
    mutationFn: async (chapterId: string | number) => courseApi.deleteChapter(courseId!, chapterId),
    onSuccess: () => {
      refreshCourse();
    },
    onError: (err) => {
      console.error('Failed to delete chapter:', err);
      alert('Failed to delete chapter');
    },
  });

  const addLessonMutation = useMutation({
    mutationFn: async () => {
      const chapter = course?.chapters?.find((entry) => String(entry.chapter_id) === newLesson.chapterId);
      return courseApi.addLesson(courseId!, newLesson.chapterId, {
        title: newLesson.title,
        content: newLesson.content,
        sort_order: chapter?.lessons?.length || 0,
      });
    },
    onSuccess: () => {
      setNewLesson({ chapterId: '', title: '', content: '' });
      refreshCourse();
    },
    onError: (err) => {
      console.error('Failed to add lesson:', err);
      alert('Failed to add lesson');
    },
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleCategoryChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const options = e.target.options;
    const selected: string[] = [];
    for (let i = 0; i < options.length; i += 1) {
      if (options[i].selected) {
        selected.push(options[i].value);
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
      const newArray = formData[field].filter((_, itemIndex) => itemIndex !== index);
      setFormData((prev) => ({ ...prev, [field]: newArray }));
    }
  };

  const handleSaveDetails = async () => {
    await updateCourseMutation.mutateAsync();
  };

  const handleAddChapter = async () => {
    if (!newChapter.title.trim()) {
      return;
    }

    await addChapterMutation.mutateAsync();
  };

  const handleDeleteChapter = async (chapterId: string | number) => {
    if (!window.confirm('Delete this chapter and all its lessons?')) {
      return;
    }

    await deleteChapterMutation.mutateAsync(chapterId);
  };

  const handleAddLesson = async () => {
    if (!newLesson.chapterId || !newLesson.title.trim()) {
      return;
    }

    await addLessonMutation.mutateAsync();
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  if (error || !course) {
    return (
      <div className="bg-red-50 text-red-600 p-4 rounded-lg">
        {errors.load || 'Failed to load course'}
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Edit Course</h1>
        <button
          onClick={() => navigate('/instructor/courses')}
          className="text-gray-600 hover:text-gray-800"
        >
          ← Back to My Courses
        </button>
      </div>

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
                  {categories.map((category) => (
                    <option key={category.id} value={category.id}>
                      {category.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          </div>

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
              disabled={updateCourseMutation.isPending}
              className="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
            >
              {updateCourseMutation.isPending ? 'Saving...' : 'Save Changes'}
            </button>
          </div>
        </div>
      )}

      {activeTab === 'content' && (
        <div className="space-y-6">
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
                disabled={addChapterMutation.isPending || !newChapter.title.trim()}
                className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
              >
                Add
              </button>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow-sm p-6">
            <h2 className="text-lg font-semibold text-gray-800 mb-4">Chapters & Lessons</h2>

            {course.chapters && course.chapters.length > 0 ? (
              <div className="space-y-4">
                {course.chapters.map((chapter, chapterIndex) => (
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

                      <div className="mt-4 pt-4 border-t">
                        <div className="flex gap-2">
                          <input
                            type="text"
                            placeholder="New lesson title"
                            value={newLesson.chapterId === String(chapter.chapter_id) ? newLesson.title : ''}
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
                            disabled={
                              addLessonMutation.isPending
                              || newLesson.chapterId !== String(chapter.chapter_id)
                              || !newLesson.title.trim()
                            }
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
