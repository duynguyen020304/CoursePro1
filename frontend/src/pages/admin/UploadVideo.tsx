import { useState, useEffect } from 'react';
import { useForm, type SubmitHandler } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Toaster } from 'react-hot-toast';
import toast from 'react-hot-toast';
import { courseApi } from '../../services/api';
import {
  uploadVideoSchema,
  type UploadVideoFormData,
} from '../../schemas/course/uploadVideo.schema';

interface Course {
  course_id: string;
  title: string;
  chapters?: Chapter[];
}

interface Chapter {
  chapter_id: string;
  title: string;
  lessons?: Lesson[];
}

interface Lesson {
  lesson_id: string;
  title: string;
}

export default function UploadVideo() {
  const [courses, setCourses] = useState<Course[]>([]);
  const [selectedCourse, setSelectedCourse] = useState('');
  const [selectedChapter, setSelectedChapter] = useState('');
  const [selectedLesson, setSelectedLesson] = useState('');
  const [chapters, setChapters] = useState<Chapter[]>([]);
  const [lessons, setLessons] = useState<Lesson[]>([]);
  const [loading, setLoading] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [videoPreview, setVideoPreview] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    setValue,
    watch,
    formState: { errors },
  } = useForm<UploadVideoFormData>({
    resolver: zodResolver(uploadVideoSchema),
    mode: 'onBlur',
    defaultValues: {
      title: '',
      course_id: '',
      chapter_id: undefined,
      video_file: undefined,
      duration: undefined,
    },
  });

  const watchedVideoFile = watch('video_file');

  useEffect(() => {
    fetchCourses();
  }, []);

  async function fetchCourses() {
    setLoading(true);
    try {
      const response = await courseApi.list();
      const coursesData = Array.isArray(response.data.data)
        ? response.data.data
        : response.data.data?.data || [];
      setCourses(coursesData as Course[]);
    } catch (error) {
      console.error('Failed to fetch courses:', error);
      toast.error('Failed to load courses');
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (selectedCourse) {
      const course = courses.find((c) => c.course_id === selectedCourse);
      if (course) {
        setChapters(course.chapters || []);
      }
    }
  }, [selectedCourse, courses]);

  useEffect(() => {
    if (selectedChapter) {
      const chapter = chapters.find((c) => c.chapter_id === selectedChapter);
      if (chapter) {
        setLessons(chapter.lessons || []);
      }
    }
  }, [selectedChapter, chapters]);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setValue('video_file', file, { shouldValidate: true });
      setVideoPreview(URL.createObjectURL(file));
    }
  };

  // Shadow mode: Also run Zod validation separately to show toast on error
  const onSubmit: SubmitHandler<UploadVideoFormData> = async (data) => {
    // Additional Zod validation in shadow mode
    const zodResult = uploadVideoSchema.safeParse(data);
    if (!zodResult.success) {
      const zodErrors = zodResult.error.issues;
      if (zodErrors.length > 0) {
        const firstError = zodErrors[0];
        toast.error(firstError.message);
      }
      return;
    }

    setUploading(true);

    try {
      console.log('Uploading video:', {
        course_id: selectedCourse,
        chapter_id: selectedChapter,
        lesson_id: selectedLesson,
        ...data,
      });

      // Simulate upload delay
      await new Promise((resolve) => setTimeout(resolve, 2000));

      toast.success('Video uploaded successfully!');
      setVideoPreview(null);
    } catch (error) {
      console.error('Failed to upload video:', error);
      toast.error('Failed to upload video. Please try again.');
    } finally {
      setUploading(false);
    }
  };

  return (
    <div>
      <Toaster position="top-right" />
      <h1 className="text-2xl font-bold text-gray-900 mb-8">Upload Video</h1>

      <div className="max-w-2xl">
        <form
          onSubmit={handleSubmit(onSubmit)}
          className="bg-white rounded-xl shadow p-6 space-y-6"
        >
          {/* Course Selection */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Select Course *
            </label>
            <select
              value={selectedCourse}
              onChange={(e) => {
                setSelectedCourse(e.target.value);
                setValue('course_id', e.target.value, { shouldValidate: true });
                setSelectedChapter('');
                setSelectedLesson('');
                setChapters([]);
                setLessons([]);
              }}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              required
            >
              <option value="">Choose a course</option>
              {courses.map((course) => (
                <option key={course.course_id} value={course.course_id}>
                  {course.title}
                </option>
              ))}
            </select>
          </div>

          {/* Chapter Selection */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Select Chapter *
            </label>
            <select
              value={selectedChapter}
              onChange={(e) => {
                setSelectedChapter(e.target.value);
                setValue('chapter_id', e.target.value || undefined, {
                  shouldValidate: true,
                });
                setSelectedLesson('');
                setLessons([]);
              }}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              disabled={!selectedCourse}
              required
            >
              <option value="">Choose a chapter</option>
              {chapters.map((chapter) => (
                <option key={chapter.chapter_id} value={chapter.chapter_id}>
                  {chapter.title}
                </option>
              ))}
            </select>
          </div>

          {/* Lesson Selection */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Select Lesson *
            </label>
            <select
              value={selectedLesson}
              onChange={(e) => setSelectedLesson(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              disabled={!selectedChapter}
              required
            >
              <option value="">Choose a lesson</option>
              {lessons.map((lesson) => (
                <option key={lesson.lesson_id} value={lesson.lesson_id}>
                  {lesson.title}
                </option>
              ))}
            </select>
          </div>

          {/* Video Title */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Video Title *
            </label>
            <input
              type="text"
              {...register('title')}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Enter video title"
            />
            {errors.title && (
              <p className="mt-1 text-sm text-red-500">{errors.title.message}</p>
            )}
          </div>

          {/* Description */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Description
            </label>
            <textarea
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              rows={4}
              placeholder="Enter video description"
            />
          </div>

          {/* Video File Upload */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Video File *
            </label>
            <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
              <svg
                className="mx-auto h-12 w-12 text-gray-400"
                stroke="currentColor"
                fill="none"
                viewBox="0 0 48 48"
                aria-hidden="true"
              >
                <path
                  d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                  strokeWidth={2}
                  strokeLinecap="round"
                  strokeLinejoin="round"
                />
              </svg>
              <p className="mt-2 text-sm text-gray-600">
                Drag and drop your video file here, or{' '}
                <label className="text-indigo-600 hover:text-indigo-500 cursor-pointer">
                  browse
                  <input
                    type="file"
                    accept="video/*"
                    onChange={handleFileChange}
                    className="hidden"
                  />
                </label>
              </p>
              <p className="mt-1 text-xs text-gray-500">
                MP4, WebM, or OGV up to 500MB
              </p>
              {videoPreview && (
                <p className="mt-2 text-sm text-green-600">
                  Selected: {watchedVideoFile?.name}
                </p>
              )}
            </div>
            {errors.video_file && (
              <p className="mt-1 text-sm text-red-500">
                {errors.video_file.message as string}
              </p>
            )}
          </div>

          {/* Duration */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Duration (minutes)
            </label>
            <input
              type="number"
              {...register('duration', {
                setValueAs: (value) => (value === '' ? undefined : parseFloat(value)),
              })}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="e.g., 15"
              step="0.1"
              min="0"
            />
            {errors.duration && (
              <p className="mt-1 text-sm text-red-500">{errors.duration.message}</p>
            )}
          </div>

          {/* Free Preview */}
          <div className="flex items-center">
            <input
              type="checkbox"
              id="is_free_preview"
              className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label
              htmlFor="is_free_preview"
              className="ml-2 block text-sm text-gray-700"
            >
              Make this video available as a free preview
            </label>
          </div>

          {/* Submit Button */}
          <button
            type="submit"
            disabled={uploading}
            className={`w-full py-3 px-4 rounded-lg font-semibold text-white ${
              uploading
                ? 'bg-gray-400 cursor-not-allowed'
                : 'bg-indigo-600 hover:bg-indigo-700'
            }`}
          >
            {uploading ? (
              <span className="flex items-center justify-center gap-2">
                <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                  <circle
                    className="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    strokeWidth="4"
                    fill="none"
                  />
                  <path
                    className="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  />
                </svg>
                Uploading...
              </span>
            ) : (
              'Upload Video'
            )}
          </button>
        </form>
      </div>
    </div>
  );
}
