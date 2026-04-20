import { useReducer, useState, useCallback, useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import { motion, AnimatePresence } from 'framer-motion';
import toast, { Toaster } from 'react-hot-toast';
import {
  instructorApi,
  categoryApi,
  chapterApi,
  courseApi,
  lessonApi,
} from '../../../services/api';
import {
  uploadSingleVideoToS3,
  uploadMultipartVideoToS3,
} from '../../admin/uploadVideo.upload';
import CurriculumSection from './CurriculumSection';

/* ================================================================
   Types
   ================================================================ */

interface Category {
  id: string;
  name: string;
}

interface ResourceDraft {
  _id: string;
  file: File;
  title: string;
  sort_order: number;
}

interface LessonDraft {
  _id: string;
  title: string;
  content: string;
  sort_order: number;
  isExpanded: boolean;
  videoFile: File | null;
  resources: ResourceDraft[];
}

interface ChapterDraft {
  _id: string;
  title: string;
  description: string;
  sort_order: number;
  isExpanded: boolean;
  lessons: LessonDraft[];
}

interface CourseFormState {
  title: string;
  description: string;
  price: string;
  difficulty: string;
  language: string;
  category_ids: string[];
  objectives: string[];
  requirements: string[];
  chapters: ChapterDraft[];
}

interface ProgressState {
  isSubmitting: boolean;
  currentStep: string;
  stepIndex: number;
  totalSteps: number;
  errors: string[];
  courseId?: string;
}

type FormAction =
  | { type: 'SET_FIELD'; field: keyof CourseFormState; value: string | string[] }
  | { type: 'ADD_ARRAY_ITEM'; field: 'objectives' | 'requirements' }
  | { type: 'REMOVE_ARRAY_ITEM'; field: 'objectives' | 'requirements'; index: number }
  | { type: 'UPDATE_ARRAY_ITEM'; field: 'objectives' | 'requirements'; index: number; value: string }
  | { type: 'ADD_CHAPTER' }
  | { type: 'REMOVE_CHAPTER'; index: number }
  | { type: 'UPDATE_CHAPTER'; index: number; field: 'title' | 'description'; value: string }
  | { type: 'TOGGLE_CHAPTER'; index: number }
  | { type: 'ADD_LESSON'; chapterIndex: number }
  | { type: 'REMOVE_LESSON'; chapterIndex: number; lessonIndex: number }
  | { type: 'UPDATE_LESSON'; chapterIndex: number; lessonIndex: number; field: 'title' | 'content'; value: string }
  | { type: 'TOGGLE_LESSON'; chapterIndex: number; lessonIndex: number }
  | { type: 'SET_VIDEO_FILE'; chapterIndex: number; lessonIndex: number; file: File | null }
  | { type: 'ADD_RESOURCE'; chapterIndex: number; lessonIndex: number; file: File }
  | { type: 'REMOVE_RESOURCE'; chapterIndex: number; lessonIndex: number; resourceIndex: number }
  | { type: 'RESET_STATE'; state: Partial<CourseFormState> };

interface Course {
  title?: string;
  description?: string | null;
  price?: number;
  difficulty?: string;
  language?: string;
  categories?: Array<{ id: string }>;
  objectives?: Array<{ objective?: string }>;
  requirements?: Array<{ requirement?: string }>;
  chapters?: ChapterData[];
}

interface ChapterData {
  chapter_id: string | number;
  title?: string;
  description?: string;
  sort_order?: number;
  lessons?: LessonData[];
}

interface LessonData {
  lesson_id: string | number;
  title?: string;
  content?: string;
  sort_order?: number;
}

interface CourseFormProps {
  mode: 'create' | 'edit';
  initialData?: Course;
  courseId?: string;
  onSuccess: () => void;
  onCancel: () => void;
}

/* ================================================================
   Reducer
   ================================================================ */

const initialState: CourseFormState = {
  title: '',
  description: '',
  price: '',
  difficulty: 'All Level',
  language: 'Vietnamese',
  category_ids: [],
  objectives: [''],
  requirements: [''],
  chapters: [],
};

function formReducer(state: CourseFormState, action: FormAction): CourseFormState {
  switch (action.type) {
    case 'SET_FIELD':
      return { ...state, [action.field]: action.value };
    case 'RESET_STATE':
      return { ...state, ...action.state };
    case 'ADD_ARRAY_ITEM':
      return { ...state, [action.field]: [...state[action.field], ''] };
    case 'REMOVE_ARRAY_ITEM': {
      const arr = [...state[action.field]];
      if (arr.length > 1) arr.splice(action.index, 1);
      return { ...state, [action.field]: arr };
    }
    case 'UPDATE_ARRAY_ITEM': {
      const arr = [...state[action.field]];
      arr[action.index] = action.value;
      return { ...state, [action.field]: arr };
    }
    case 'ADD_CHAPTER':
      return {
        ...state,
        chapters: [
          ...state.chapters,
          {
            _id: crypto.randomUUID(),
            title: '',
            description: '',
            sort_order: state.chapters.length,
            isExpanded: true,
            lessons: [],
          },
        ],
      };
    case 'REMOVE_CHAPTER':
      return { ...state, chapters: state.chapters.filter((_, i) => i !== action.index) };
    case 'UPDATE_CHAPTER': {
      const chapters = [...state.chapters];
      chapters[action.index] = { ...chapters[action.index], [action.field]: action.value };
      return { ...state, chapters };
    }
    case 'TOGGLE_CHAPTER': {
      const chapters = [...state.chapters];
      chapters[action.index] = { ...chapters[action.index], isExpanded: !chapters[action.index].isExpanded };
      return { ...state, chapters };
    }
    case 'ADD_LESSON': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [
        ...ch.lessons,
        {
          _id: crypto.randomUUID(),
          title: '',
          content: '',
          sort_order: ch.lessons.length,
          isExpanded: true,
          videoFile: null,
          resources: [],
        },
      ];
      ch.isExpanded = true;
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'REMOVE_LESSON': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = ch.lessons.filter((_, i) => i !== action.lessonIndex);
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'UPDATE_LESSON': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [...ch.lessons];
      ch.lessons[action.lessonIndex] = { ...ch.lessons[action.lessonIndex], [action.field]: action.value };
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'TOGGLE_LESSON': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [...ch.lessons];
      ch.lessons[action.lessonIndex] = { ...ch.lessons[action.lessonIndex], isExpanded: !ch.lessons[action.lessonIndex].isExpanded };
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'SET_VIDEO_FILE': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [...ch.lessons];
      ch.lessons[action.lessonIndex] = { ...ch.lessons[action.lessonIndex], videoFile: action.file };
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'ADD_RESOURCE': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [...ch.lessons];
      const lesson = { ...ch.lessons[action.lessonIndex] };
      lesson.resources = [
        ...lesson.resources,
        {
          _id: crypto.randomUUID(),
          file: action.file,
          title: action.file.name,
          sort_order: lesson.resources.length,
        },
      ];
      ch.lessons[action.lessonIndex] = lesson;
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    case 'REMOVE_RESOURCE': {
      const chapters = [...state.chapters];
      const ch = { ...chapters[action.chapterIndex] };
      ch.lessons = [...ch.lessons];
      const lesson = { ...ch.lessons[action.lessonIndex] };
      lesson.resources = lesson.resources.filter((_, i) => i !== action.resourceIndex);
      ch.lessons[action.lessonIndex] = lesson;
      chapters[action.chapterIndex] = ch;
      return { ...state, chapters };
    }
    default:
      return state;
  }
}

/* ================================================================
   Validation
   ================================================================ */

function validateForm(state: CourseFormState): Record<string, string> {
  const errors: Record<string, string> = {};
  if (!state.title.trim()) errors.title = 'Course title is required';
  if (!state.description.trim()) errors.description = 'Description is required';
  else if (state.description.trim().length < 10) errors.description = 'Description must be at least 10 characters';
  if (!state.price.trim()) errors.price = 'Price is required';
  else if (isNaN(parseFloat(state.price)) || parseFloat(state.price) < 0) errors.price = 'Price must be a non-negative number';
  return errors;
}

/* ================================================================
   Helpers
   ================================================================ */

function extractId(response: { data: { data: unknown } }, field: string): string {
  const data = response.data.data as Record<string, unknown>;
  return String(data[field]);
}

function calculateTotalSteps(state: CourseFormState): number {
  let steps = 1;
  state.chapters.forEach((ch) => {
    steps += 1;
    ch.lessons.forEach((ls) => {
      steps += 1;
      if (ls.videoFile) steps += 1;
      steps += ls.resources.length;
    });
  });
  return steps;
}

/* ================================================================
   Main Component
   ================================================================ */

export default function CourseForm({ mode, initialData, courseId, onSuccess, onCancel }: CourseFormProps) {
  const [state, dispatch] = useReducer(formReducer, initialState);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [progress, setProgress] = useState<ProgressState>({
    isSubmitting: false,
    currentStep: '',
    stepIndex: 0,
    totalSteps: 0,
    errors: [],
  });
  const [isInitialized, setIsInitialized] = useState(false);
  const [selectedChapterIndex, setSelectedChapterIndex] = useState<number | null>(null);

  const { data: categories = [] } = useQuery<Category[]>({
    queryKey: ['instructor', 'course-form', 'categories'],
    queryFn: async () => {
      const response = await categoryApi.list();
      return response.data.success ? (response.data.data as Category[]) : [];
    },
  });

  // Initialize form with existing data when editing
  useEffect(() => {
    if (mode === 'edit' && initialData && !isInitialized) {
      const chaptersData = initialData.chapters?.map(ch => ({
        _id: String(ch.chapter_id),
        title: ch.title || '',
        description: ch.description || '',
        sort_order: ch.sort_order || 0,
        isExpanded: false,
        lessons: ch.lessons?.map(ls => ({
          _id: String(ls.lesson_id),
          title: ls.title || '',
          content: ls.content || '',
          sort_order: ls.sort_order || 0,
          isExpanded: false,
          videoFile: null,
          resources: [],
        })) || [],
      })) || [];

      dispatch({
        type: 'RESET_STATE',
        state: {
          title: initialData.title || '',
          description: initialData.description || '',
          price: initialData.price?.toString() || '',
          difficulty: initialData.difficulty || 'All Level',
          language: initialData.language || 'Vietnamese',
          category_ids: initialData.categories?.map(c => c.id) || [],
          objectives: initialData.objectives?.map(o => o.objective || '').filter(o => o.trim()) || [''],
          requirements: initialData.requirements?.map(r => r.requirement || '').filter(r => r.trim()) || [''],
          chapters: chaptersData,
        },
      });
      setIsInitialized(true);
    } else if (mode === 'create' && !isInitialized) {
      setIsInitialized(true);
    }
  }, [mode, initialData, isInitialized]);

  const toggleCategory = (id: string) => {
    const ids = state.category_ids.includes(id)
      ? state.category_ids.filter((i) => i !== id)
      : [...state.category_ids, id];
    dispatch({ type: 'SET_FIELD', field: 'category_ids', value: ids });
  };

  const handleSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    const validationErrors = validateForm(state);
    setErrors(validationErrors);
    if (Object.keys(validationErrors).length > 0) {
      const errorMessages = Object.values(validationErrors);
      if (errorMessages.length <= 5) {
        toast.error(`Missing required fields:\n${errorMessages.join('\n')}`);
      } else {
        toast.error(`Missing required fields:\n${errorMessages.slice(0, 5).join('\n')}\n+${errorMessages.length - 5} more`);
      }
      return;
    }

    const totalSteps = calculateTotalSteps(state);
    let stepIndex = 0;
    const advance = (label: string) => {
      stepIndex += 1;
      setProgress((prev) => ({ ...prev, currentStep: label, stepIndex }));
    };

    setProgress({
      isSubmitting: true,
      currentStep: mode === 'create' ? 'Creating course...' : 'Updating course...',
      stepIndex: 0,
      totalSteps,
      errors: []
    });

    try {
      if (mode === 'create') {
        // Create course
        advance('Creating course...');
        const courseRes = await instructorApi.createCourse({
          title: state.title,
          description: state.description,
          price: parseFloat(state.price),
          difficulty: state.difficulty,
          language: state.language,
          category_ids: state.category_ids,
          objectives: state.objectives.filter((o) => o.trim()),
          requirements: state.requirements.filter((r) => r.trim()),
        });
        const newCourseId = extractId(courseRes, 'course_id');

        // Create chapters + lessons + uploads
        for (let ci = 0; ci < state.chapters.length; ci++) {
          const ch = state.chapters[ci];
          advance(`Creating chapter: ${ch.title || `Chapter ${ci + 1}`}`);

          const chRes = await chapterApi.create(newCourseId, {
            title: ch.title,
            description: ch.description,
            sort_order: ci,
          });
          const chapterId = extractId(chRes, 'chapter_id');

          for (let li = 0; li < ch.lessons.length; li++) {
            const ls = ch.lessons[li];
            advance(`Creating lesson: ${ls.title || `Lesson ${li + 1}`}`);

            const lsRes = await courseApi.addLesson(newCourseId, chapterId, {
              title: ls.title,
              content: ls.content,
              sort_order: li,
            });
            const lessonId = extractId(lsRes, 'lesson_id');

            // Video upload
            if (ls.videoFile) {
              advance(`Uploading video for: ${ls.title}`);
              const initRes = await lessonApi.initiateVideoUpload(lessonId, {
                title: ls.videoFile.name,
                filename: ls.videoFile.name,
                mime_type: ls.videoFile.type,
                file_size_bytes: ls.videoFile.size,
                duration: 0,
                sort_order: 0,
              });
              const initData = (initRes.data.data ?? initRes.data) as Record<string, unknown>;
              const uploadMode = initData.upload_mode as string;
              const videoId = String(initData.video_id);

              if (uploadMode === 'single') {
                const etag = await uploadSingleVideoToS3(
                  ls.videoFile,
                  initData.upload as { url: string; headers?: Record<string, string> },
                  { signal: new AbortController().signal, onProgress: () => {} },
                );
                await lessonApi.completeVideoUpload(lessonId, videoId, { etag });
              } else {
                const parts = await uploadMultipartVideoToS3(
                  ls.videoFile,
                  initData.parts as { part_number: number; url: string; headers?: Record<string, string> }[],
                  initData.part_size_bytes as number,
                  { signal: new AbortController().signal, onProgress: () => {} },
                );
                await lessonApi.completeVideoUpload(lessonId, videoId, {
                  upload_id: initData.upload_id,
                  parts,
                });
              }
            }

            // Resources
            for (const res of ls.resources) {
              advance(`Adding resource: ${res.title}`);
              await lessonApi.addResource(lessonId, {
                resource_path: res.file.name,
                title: res.title,
                sort_order: res.sort_order,
              });
            }
          }
        }

        toast.success('Course created successfully!');
      } else {
        // Update existing course
        if (!courseId) throw new Error('Course ID is required for update');

        advance('Updating course details...');
        await instructorApi.updateCourse(courseId, {
          title: state.title,
          description: state.description,
          price: parseFloat(state.price),
          difficulty: state.difficulty,
          language: state.language,
          category_ids: state.category_ids,
          objectives: state.objectives.filter((o) => o.trim()),
          requirements: state.requirements.filter((r) => r.trim()),
        });

        toast.success('Course updated successfully!');
      }

      onSuccess();
    } catch (err: unknown) {
      const errorObj = err as { response?: { data?: { message?: string } } };
      const msg = errorObj.response?.data?.message || `An error occurred during course ${mode}.`;
      setProgress((prev) => ({ ...prev, errors: [msg] }));
    }
  }, [state, mode, courseId, onSuccess]);

  const closeProgress = () => {
    if (progress.errors.length > 0) {
      setProgress({ isSubmitting: false, currentStep: '', stepIndex: 0, totalSteps: 0, errors: [], courseId: progress.courseId });
    }
  };

  if (!isInitialized) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return (
    <div className="-m-6 min-h-screen flex flex-col">
      <Toaster position="top-right" />
      {/* ======== Progress Overlay ======== */}
      <AnimatePresence>
        {progress.isSubmitting && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center"
          >
            <motion.div
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              className="bg-white rounded-2xl p-10 max-w-md w-full mx-4 shadow-2xl"
            >
              <h3 className="font-sora text-xl font-bold text-slate-900 mb-1">
                {mode === 'create' ? 'Creating Your Course' : 'Updating Your Course'}
              </h3>
              <p className="text-slate-500 text-sm mb-6">{progress.currentStep}</p>

              <div className="h-2 bg-slate-100 rounded-full overflow-hidden mb-3">
                <motion.div
                  className="h-full bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-full"
                  animate={{ width: `${progress.totalSteps > 0 ? (progress.stepIndex / progress.totalSteps) * 100 : 0}%` }}
                  transition={{ duration: 0.4, ease: 'easeOut' }}
                />
              </div>
              <p className="text-xs text-slate-400 mb-4">
                Step {progress.stepIndex} of {progress.totalSteps}
              </p>

              {progress.errors.length > 0 && (
                <div className="mt-2 p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-700 text-sm">
                  {progress.errors.map((err, i) => <p key={i}>{err}</p>)}
                  <div className="flex gap-3 mt-3">
                    {progress.courseId && (
                      <button
                        onClick={() => {/* Navigate to edit */}}
                        className="text-indigo-600 font-medium text-sm hover:underline"
                      >
                        Continue editing
                      </button>
                    )}
                    <button onClick={closeProgress} className="text-slate-600 font-medium text-sm hover:underline">
                      Close
                    </button>
                  </div>
                </div>
              )}
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* ======== Hero Section ======== */}
      <section className="bg-gradient-to-br from-indigo-900 via-indigo-800 to-indigo-700 px-10 py-12">
        <p className="text-indigo-300 text-sm font-medium tracking-wide uppercase mb-1">
          {mode === 'create' ? 'New Course' : 'Edit Course'}
        </p>
        <h1 className="font-sora text-3xl font-bold text-white mb-8">
          {mode === 'create' ? 'Create Your Course' : 'Edit Your Course'}
        </h1>

        <div className="max-w-3xl space-y-5">
          <div>
            <input
              type="text"
              value={state.title}
              onChange={(e) => { dispatch({ type: 'SET_FIELD', field: 'title', value: e.target.value }); if (errors.title) setErrors((prev) => { const n = { ...prev }; delete n.title; return n; }); }}
              placeholder="Course Title *"
              className={`w-full bg-white/10 backdrop-blur-sm border rounded-xl px-5 py-4 text-xl font-sora font-semibold text-white placeholder:text-indigo-300/60 focus:ring-2 focus:ring-indigo-400 focus:bg-white/[0.15] focus:outline-none transition-all ${
                errors.title ? 'border-rose-400' : 'border-white/20'
              }`}
            />
            {errors.title && <p className="mt-1.5 text-sm text-rose-300">{errors.title}</p>}
          </div>
          <div>
            <textarea
              value={state.description}
              onChange={(e) => { dispatch({ type: 'SET_FIELD', field: 'description', value: e.target.value }); if (errors.description) setErrors((prev) => { const n = { ...prev }; delete n.description; return n; }); }}
              placeholder="Describe what students will learn..."
              rows={4}
              className={`w-full bg-white/10 backdrop-blur-sm border rounded-xl px-5 py-4 text-white placeholder:text-indigo-300/60 focus:ring-2 focus:ring-indigo-400 focus:bg-white/[0.15] focus:outline-none transition-all resize-none ${
                errors.description ? 'border-rose-400' : 'border-white/20'
              }`}
            />
            {errors.description && <p className="mt-1.5 text-sm text-rose-300">{errors.description}</p>}
          </div>
        </div>
      </section>

      {/* ======== Course Meta ======== */}
      <section className="bg-white border-b border-slate-200 px-10 py-8">
        <div className="flex items-center gap-2 mb-6">
          <div className="w-1 h-6 bg-indigo-500 rounded-full" />
          <h2 className="font-sora text-lg font-semibold text-slate-900">Course Details</h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1.5">Price ($) *</label>
            <input
              type="number"
              value={state.price}
              onChange={(e) => { dispatch({ type: 'SET_FIELD', field: 'price', value: e.target.value }); if (errors.price) setErrors((prev) => { const n = { ...prev }; delete n.price; return n; }); }}
              min="0"
              step="0.01"
              placeholder="99.99"
              className={`w-full px-4 py-2.5 border rounded-lg text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all ${
                errors.price ? 'border-rose-400' : 'border-slate-300'
              }`}
            />
            {errors.price && <p className="mt-1 text-xs text-rose-500">{errors.price}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1.5">Difficulty</label>
            <select
              value={state.difficulty}
              onChange={(e) => dispatch({ type: 'SET_FIELD', field: 'difficulty', value: e.target.value })}
              className="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all"
            >
              <option value="All Level">All Levels</option>
              <option value="Beginner">Beginner</option>
              <option value="Intermediate">Intermediate</option>
              <option value="Expert">Expert</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1.5">Language</label>
            <select
              value={state.language}
              onChange={(e) => dispatch({ type: 'SET_FIELD', field: 'language', value: e.target.value })}
              className="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all"
            >
              <option value="Vietnamese">Vietnamese</option>
              <option value="English">English</option>
              <option value="Japanese">Japanese</option>
              <option value="Korean">Korean</option>
              <option value="Chinese">Chinese</option>
            </select>
          </div>
        </div>

        {/* Categories as toggle pills */}
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-2">Categories</label>
          {categories.length > 0 ? (
            <div className="flex flex-wrap gap-2">
              {categories.map((cat) => {
                const selected = state.category_ids.includes(cat.id);
                return (
                  <button
                    key={cat.id}
                    type="button"
                    onClick={() => toggleCategory(cat.id)}
                    className={`px-3.5 py-1.5 rounded-full text-sm font-medium transition-all duration-200 ${
                      selected
                        ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                    }`}
                  >
                    {cat.name}
                  </button>
                );
              })}
            </div>
          ) : (
            <p className="text-sm text-slate-400">Loading categories...</p>
          )}
        </div>
      </section>

      {/* ======== Objectives ======== */}
      <section className="bg-slate-50 px-10 py-8">
        <div className="flex items-center gap-2 mb-6">
          <div className="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-bold">
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
          </div>
          <h2 className="font-sora text-lg font-semibold text-slate-900">Learning Objectives</h2>
        </div>
        <p className="text-sm text-slate-500 mb-4">What will students learn in this course?</p>
        <div className="space-y-3">
          <AnimatePresence initial={false}>
            {state.objectives.map((obj, i) => (
              <motion.div
                key={`obj-${i}`}
                initial={{ opacity: 0, x: -12 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -12, height: 0 }}
                transition={{ duration: 0.2 }}
                className="flex gap-2"
              >
                <span className="shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-semibold mt-2">
                  {i + 1}
                </span>
                <input
                  type="text"
                  value={obj}
                  onChange={(e) => dispatch({ type: 'UPDATE_ARRAY_ITEM', field: 'objectives', index: i, value: e.target.value })}
                  placeholder="e.g., Build real-world web applications"
                  className="flex-1 px-4 py-2.5 border border-slate-300 rounded-lg text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all"
                />
                {state.objectives.length > 1 && (
                  <button
                    type="button"
                    onClick={() => dispatch({ type: 'REMOVE_ARRAY_ITEM', field: 'objectives', index: i })}
                    className="shrink-0 px-2 text-slate-400 hover:text-rose-500 transition-colors"
                  >
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                )}
              </motion.div>
            ))}
          </AnimatePresence>
        </div>
        <button
          type="button"
          onClick={() => dispatch({ type: 'ADD_ARRAY_ITEM', field: 'objectives' })}
          className="mt-4 text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-1.5 transition-colors"
        >
          <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Add Objective
        </button>
      </section>

      {/* ======== Requirements ======== */}
      <section className="bg-white px-10 py-8">
        <div className="flex items-center gap-2 mb-6">
          <div className="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-bold">
            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
          </div>
          <h2 className="font-sora text-lg font-semibold text-slate-900">Prerequisites</h2>
        </div>
        <p className="text-sm text-slate-500 mb-4">What should students know before taking this course?</p>
        <div className="space-y-3">
          <AnimatePresence initial={false}>
            {state.requirements.map((req, i) => (
              <motion.div
                key={`req-${i}`}
                initial={{ opacity: 0, x: -12 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -12, height: 0 }}
                transition={{ duration: 0.2 }}
                className="flex gap-2"
              >
                <span className="shrink-0 w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-semibold mt-2">
                  {i + 1}
                </span>
                <input
                  type="text"
                  value={req}
                  onChange={(e) => dispatch({ type: 'UPDATE_ARRAY_ITEM', field: 'requirements', index: i, value: e.target.value })}
                  placeholder="e.g., Basic understanding of HTML and CSS"
                  className="flex-1 px-4 py-2.5 border border-slate-300 rounded-lg text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all"
                />
                {state.requirements.length > 1 && (
                  <button
                    type="button"
                    onClick={() => dispatch({ type: 'REMOVE_ARRAY_ITEM', field: 'requirements', index: i })}
                    className="shrink-0 px-2 text-slate-400 hover:text-rose-500 transition-colors"
                  >
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                )}
              </motion.div>
            ))}
          </AnimatePresence>
        </div>
        <button
          type="button"
          onClick={() => dispatch({ type: 'ADD_ARRAY_ITEM', field: 'requirements' })}
          className="mt-4 text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-1.5 transition-colors"
        >
          <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Add Requirement
        </button>
      </section>

      {/* ======== Curriculum Builder ======== */}
      <CurriculumSection
        chapters={state.chapters}
        dispatch={dispatch}
        mode={mode}
        selectedChapterIndex={selectedChapterIndex}
        onSelectChapter={setSelectedChapterIndex}
      />


      {/* ======== Sticky Action Bar ======== */}
      <div className="sticky bottom-0 bg-white/90 backdrop-blur-md border-t border-slate-200 px-10 py-4 flex items-center justify-between z-10">
        <button
          type="button"
          onClick={onCancel}
          className="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-medium transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          onClick={handleSubmit}
          className="px-8 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 font-semibold transition-colors shadow-sm shadow-indigo-200"
        >
          {mode === 'create' ? 'Create Course' : 'Save Changes'}
        </button>
      </div>
    </div>
  );
}
