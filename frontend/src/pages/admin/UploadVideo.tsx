import { useEffect, useMemo, useRef, useState, type ChangeEvent } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import { useForm, type SubmitHandler } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Toaster } from 'react-hot-toast';
import toast from 'react-hot-toast';
import { courseApi, lessonApi } from '../../services/api';
import {
  uploadVideoSchema,
  type UploadVideoFormData,
} from '../../schemas/course/uploadVideo.schema';
import {
  convertDurationMinutesToSeconds,
  isAbortError,
  uploadMultipartVideoToS3,
  uploadSingleVideoToS3,
  type MultipartUploadPart,
  type SingleUploadContract,
} from './uploadVideo.upload';

interface Course {
  course_id: string;
  title: string;
}

interface Chapter {
  chapter_id: string;
  title: string;
}

interface Lesson {
  lesson_id: string;
  title: string;
}

interface InitiateUploadData {
  video_id: string;
  upload_mode: 'single' | 'multipart';
  storage_key: string;
  upload_id: string | null;
  part_size_bytes: number | null;
  single_upload: SingleUploadContract | null;
  multipart_parts: MultipartUploadPart[] | null;
}

type UploadPhase = 'idle' | 'initiating' | 'uploading' | 'finalizing';

export default function UploadVideo() {
  const [selectedCourse, setSelectedCourse] = useState('');
  const [selectedChapter, setSelectedChapter] = useState('');
  const [selectedLesson, setSelectedLesson] = useState('');
  const [uploadPhase, setUploadPhase] = useState<UploadPhase>('idle');
  const [progressPercent, setProgressPercent] = useState(0);
  const [videoPreview, setVideoPreview] = useState<string | null>(null);
  const [currentAbortController, setCurrentAbortController] = useState<AbortController | null>(null);
  const abortControllerRef = useRef<AbortController | null>(null);

  const {
    register,
    handleSubmit,
    setValue,
    reset,
    watch,
    formState: { errors },
  } = useForm<UploadVideoFormData>({
    resolver: zodResolver(uploadVideoSchema),
    mode: 'onBlur',
    defaultValues: {
      title: '',
      course_id: '' as never,
      chapter_id: '' as never,
      lesson_id: '' as never,
      video_file: undefined,
      duration: undefined,
    },
  });

  const watchedVideoFile = watch('video_file');
  const isUploading = uploadPhase !== 'idle';
  const uploadPhaseLabel = useMemo(() => {
    switch (uploadPhase) {
      case 'initiating':
        return 'Preparing upload';
      case 'uploading':
        return 'Uploading to S3';
      case 'finalizing':
        return 'Finalizing lesson video';
      default:
        return null;
    }
  }, [uploadPhase]);

  const { data: courses = [], isLoading: isCoursesLoading } = useQuery<Course[]>({
    queryKey: ['admin', 'upload-video', 'courses'],
    queryFn: async () => {
      const response = await courseApi.list();
      return (response.data.data ?? []) as Course[];
    },
  });

  const { data: chapters = [], isLoading: isChaptersLoading } = useQuery<Chapter[]>({
    queryKey: ['admin', 'upload-video', 'chapters', selectedCourse],
    enabled: Boolean(selectedCourse),
    queryFn: async () => {
      const response = await courseApi.getChapters(selectedCourse);
      return (response.data.data as Chapter[] | undefined) ?? [];
    },
  });

  const { data: lessons = [], isLoading: isLessonsLoading } = useQuery<Lesson[]>({
    queryKey: ['admin', 'upload-video', 'lessons', selectedCourse, selectedChapter],
    enabled: Boolean(selectedCourse && selectedChapter),
    queryFn: async () => {
      const response = await courseApi.getLessons(selectedCourse, selectedChapter);
      return (response.data.data as Lesson[] | undefined) ?? [];
    },
  });

  useEffect(() => () => {
    if (videoPreview) {
      URL.revokeObjectURL(videoPreview);
    }
  }, [videoPreview]);

  const handleFileChange = (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) {
      return;
    }

    if (videoPreview) {
      URL.revokeObjectURL(videoPreview);
    }

    setValue('video_file', file, { shouldValidate: true });
    setVideoPreview(URL.createObjectURL(file));
  };

  const handleCancelUpload = () => {
    currentAbortController?.abort();
  };

  const resetFormState = () => {
    if (videoPreview) {
      URL.revokeObjectURL(videoPreview);
    }

    reset({
      title: '',
      course_id: '' as never,
      chapter_id: '' as never,
      lesson_id: '' as never,
      video_file: undefined,
      duration: undefined,
    });
    setSelectedCourse('');
    setSelectedChapter('');
    setSelectedLesson('');
    setVideoPreview(null);
    setProgressPercent(0);
    setUploadPhase('idle');
    setCurrentAbortController(null);
    abortControllerRef.current = null;
  };

  const uploadVideoMutation = useMutation({
    mutationFn: async (data: UploadVideoFormData) => {
      const abortController = new AbortController();
      abortControllerRef.current = abortController;
      setCurrentAbortController(abortController);
      setProgressPercent(0);
      setUploadPhase('initiating');

      let initiatedUpload: InitiateUploadData | null = null;

      try {
        const initiateResponse = await lessonApi.initiateVideoUpload(data.lesson_id, {
          title: data.title,
          filename: data.video_file.name,
          mime_type: data.video_file.type,
          file_size_bytes: data.video_file.size,
          duration: convertDurationMinutesToSeconds(data.duration),
          sort_order: 0,
        });

        initiatedUpload = initiateResponse.data.data as InitiateUploadData;
        setUploadPhase('uploading');

        if (initiatedUpload.upload_mode === 'single' && initiatedUpload.single_upload) {
          const etag = await uploadSingleVideoToS3(data.video_file, initiatedUpload.single_upload, {
            signal: abortController.signal,
            onProgress: setProgressPercent,
          });

          setUploadPhase('finalizing');
          await lessonApi.completeVideoUpload(data.lesson_id, initiatedUpload.video_id, { etag });
          return;
        }

        if (initiatedUpload.upload_mode === 'multipart' && initiatedUpload.multipart_parts && initiatedUpload.part_size_bytes) {
          const parts = await uploadMultipartVideoToS3(
            data.video_file,
            initiatedUpload.multipart_parts,
            initiatedUpload.part_size_bytes,
            {
              signal: abortController.signal,
              onProgress: setProgressPercent,
            }
          );

          setUploadPhase('finalizing');
          await lessonApi.completeVideoUpload(data.lesson_id, initiatedUpload.video_id, {
            upload_id: initiatedUpload.upload_id,
            parts,
          });
          return;
        }

        throw new Error('Upload contract was incomplete');
      } catch (error) {
        if (initiatedUpload) {
          try {
            await lessonApi.abortVideoUpload(data.lesson_id, initiatedUpload.video_id, {
              upload_id: initiatedUpload.upload_id ?? undefined,
            });
          } catch (abortError) {
            console.error('Failed to abort video upload:', abortError);
          }
        }

        throw error;
      }
    },
    onSuccess: () => {
      toast.success('Video uploaded successfully');
      resetFormState();
    },
    onError: (error) => {
      console.error('Failed to upload video:', error);
      toast.error(isAbortError(error) ? 'Upload canceled' : 'Failed to upload video');
      setUploadPhase('idle');
      setProgressPercent(0);
      setCurrentAbortController(null);
      abortControllerRef.current = null;
    },
    onSettled: () => {
      setCurrentAbortController(null);
      abortControllerRef.current = null;
      setUploadPhase('idle');
    },
  });

  const onSubmit: SubmitHandler<UploadVideoFormData> = async (data) => {
    const zodResult = uploadVideoSchema.safeParse(data);
    if (!zodResult.success) {
      const firstError = zodResult.error.issues[0];
      if (firstError) {
        toast.error(firstError.message);
      }
      return;
    }

    await uploadVideoMutation.mutateAsync(data);
  };

  return (
    <div>
      <Toaster position="top-right" />
      <h1 className="mb-8 text-2xl font-bold text-gray-900">Upload Video</h1>

      <div className="max-w-2xl">
        <form
          onSubmit={handleSubmit(onSubmit)}
          className="space-y-6 rounded-xl bg-white p-6 shadow"
        >
          <div>
            <label htmlFor="course_id" className="mb-2 block text-sm font-medium text-gray-700">
              Select Course *
            </label>
            <select
              id="course_id"
              value={selectedCourse}
              onChange={(e) => {
                const value = e.target.value;
                setSelectedCourse(value);
                setValue('course_id', value as never, { shouldValidate: true });
                setSelectedChapter('');
                setSelectedLesson('');
                setValue('chapter_id', '' as never, { shouldValidate: true });
                setValue('lesson_id', '' as never, { shouldValidate: true });
              }}
              className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              disabled={isCoursesLoading || isUploading}
            >
              <option value="">Choose a course</option>
              {courses.map((course) => (
                <option key={course.course_id} value={course.course_id}>
                  {course.title}
                </option>
              ))}
            </select>
            {errors.course_id && (
              <p className="mt-1 text-sm text-red-500">{errors.course_id.message}</p>
            )}
          </div>

          <div>
            <label htmlFor="chapter_id" className="mb-2 block text-sm font-medium text-gray-700">
              Select Chapter *
            </label>
            <select
              id="chapter_id"
              value={selectedChapter}
              onChange={(e) => {
                const value = e.target.value;
                setSelectedChapter(value);
                setValue('chapter_id', value as never, { shouldValidate: true });
                setSelectedLesson('');
                setValue('lesson_id', '' as never, { shouldValidate: true });
              }}
              className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              disabled={!selectedCourse || isUploading || isChaptersLoading}
            >
              <option value="">Choose a chapter</option>
              {chapters.map((chapter) => (
                <option key={chapter.chapter_id} value={chapter.chapter_id}>
                  {chapter.title}
                </option>
              ))}
            </select>
            {errors.chapter_id && (
              <p className="mt-1 text-sm text-red-500">{errors.chapter_id.message}</p>
            )}
          </div>

          <div>
            <label htmlFor="lesson_id" className="mb-2 block text-sm font-medium text-gray-700">
              Select Lesson *
            </label>
            <select
              id="lesson_id"
              value={selectedLesson}
              onChange={(e) => {
                const value = e.target.value;
                setSelectedLesson(value);
                setValue('lesson_id', value as never, { shouldValidate: true });
              }}
              className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              disabled={!selectedChapter || isUploading || isLessonsLoading}
            >
              <option value="">Choose a lesson</option>
              {lessons.map((lesson) => (
                <option key={lesson.lesson_id} value={lesson.lesson_id}>
                  {lesson.title}
                </option>
              ))}
            </select>
            {errors.lesson_id && (
              <p className="mt-1 text-sm text-red-500">{errors.lesson_id.message}</p>
            )}
          </div>

          <div>
            <label htmlFor="title" className="mb-2 block text-sm font-medium text-gray-700">
              Video Title *
            </label>
            <input
              id="title"
              type="text"
              {...register('title')}
              className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Enter video title"
              disabled={isUploading}
            />
            {errors.title && (
              <p className="mt-1 text-sm text-red-500">{errors.title.message}</p>
            )}
          </div>

          <div>
            <label htmlFor="video_file" className="mb-2 block text-sm font-medium text-gray-700">
              Video File *
            </label>
            <div className="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center">
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
                <label className="cursor-pointer text-indigo-600 hover:text-indigo-500">
                  browse
                  <input
                    id="video_file"
                    type="file"
                    accept="video/*"
                    onChange={handleFileChange}
                    className="hidden"
                    disabled={isUploading}
                  />
                </label>
              </p>
              <p className="mt-1 text-xs text-gray-500">
                MP4, WebM, OGV, or MOV up to 500MB
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

          <div>
            <label htmlFor="duration" className="mb-2 block text-sm font-medium text-gray-700">
              Duration (minutes)
            </label>
            <input
              id="duration"
              type="number"
              {...register('duration', {
                setValueAs: (value) => (value === '' ? undefined : parseFloat(value)),
              })}
              className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="e.g., 15"
              step="0.1"
              min="0"
              disabled={isUploading}
            />
            {errors.duration && (
              <p className="mt-1 text-sm text-red-500">{errors.duration.message}</p>
            )}
          </div>

          {uploadPhaseLabel && (
            <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
              <div className="mb-2 flex items-center justify-between text-sm font-medium text-indigo-900">
                <span>{uploadPhaseLabel}</span>
                <span>{progressPercent}%</span>
              </div>
              <div className="h-2 w-full rounded-full bg-indigo-100">
                <div
                  className="h-2 rounded-full bg-indigo-600 transition-all"
                  style={{ width: `${progressPercent}%` }}
                />
              </div>
            </div>
          )}

          <div className="flex gap-3">
            <button
              type="submit"
              disabled={isUploading}
              className={`flex-1 rounded-lg px-4 py-3 font-semibold text-white ${
                isUploading
                  ? 'cursor-not-allowed bg-gray-400'
                  : 'bg-indigo-600 hover:bg-indigo-700'
              }`}
            >
              {isUploading ? 'Uploading...' : 'Upload Video'}
            </button>
            {isUploading && (
              <button
                type="button"
                onClick={handleCancelUpload}
                className="rounded-lg border border-gray-300 px-4 py-3 font-semibold text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
            )}
          </div>
        </form>
      </div>
    </div>
  );
}
