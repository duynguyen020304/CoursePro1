import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import UploadVideo from '../../pages/admin/UploadVideo';

const {
  toastSuccess,
  toastError,
  courseApiMock,
  lessonApiMock,
  uploadSingleVideoToS3Mock,
  uploadMultipartVideoToS3Mock,
  isAbortErrorMock,
} = vi.hoisted(() => ({
  toastSuccess: vi.fn(),
  toastError: vi.fn(),
  courseApiMock: {
    list: vi.fn(),
    getChapters: vi.fn(),
    getLessons: vi.fn(),
  },
  lessonApiMock: {
    initiateVideoUpload: vi.fn(),
    completeVideoUpload: vi.fn(),
    abortVideoUpload: vi.fn(),
  },
  uploadSingleVideoToS3Mock: vi.fn(),
  uploadMultipartVideoToS3Mock: vi.fn(),
  isAbortErrorMock: vi.fn((error: unknown) => Boolean(
    error && typeof error === 'object' && 'code' in error && (error as { code?: string }).code === 'ERR_CANCELED'
  )),
}));

vi.mock('../../services/api', () => ({
  courseApi: courseApiMock,
  lessonApi: lessonApiMock,
}));

vi.mock('react-hot-toast', () => ({
  __esModule: true,
  default: {
    success: toastSuccess,
    error: toastError,
  },
  Toaster: () => null,
}));

vi.mock('../../pages/admin/uploadVideo.upload', () => ({
  convertDurationMinutesToSeconds: (value?: number) => value == null ? undefined : Math.round(value * 60),
  isAbortError: isAbortErrorMock,
  uploadSingleVideoToS3: uploadSingleVideoToS3Mock,
  uploadMultipartVideoToS3: uploadMultipartVideoToS3Mock,
}));

describe('UploadVideo page', () => {
  beforeEach(() => {
    vi.clearAllMocks();

    courseApiMock.list.mockResolvedValue({
      data: {
        data: [{ course_id: '550e8400-e29b-41d4-a716-446655440000', title: 'Course A' }],
      },
    });
    courseApiMock.getChapters.mockResolvedValue({
      data: {
        data: [{ chapter_id: '550e8400-e29b-41d4-a716-446655440001', title: 'Chapter A' }],
      },
    });
    courseApiMock.getLessons.mockResolvedValue({
      data: {
        data: [{ lesson_id: '550e8400-e29b-41d4-a716-446655440002', title: 'Lesson A' }],
      },
    });
  });

  it('shows lesson validation when a lesson is not selected', async () => {
    render(<UploadVideo />);

    await screen.findByRole('option', { name: 'Course A' });
    fireEvent.change(screen.getByLabelText(/select course/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440000' },
    });

    await screen.findByRole('option', { name: 'Chapter A' });
    fireEvent.change(screen.getByLabelText(/select chapter/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440001' },
    });

    fireEvent.change(screen.getByLabelText(/video title/i), {
      target: { value: 'New upload' },
    });

    const file = new File([new Uint8Array([1, 2, 3])], 'lesson.mp4', { type: 'video/mp4' });
    fireEvent.change(screen.getByLabelText(/video file/i), {
      target: { files: [file] },
    });

    fireEvent.click(screen.getByRole('button', { name: /upload video/i }));

    await screen.findByText('Lesson is required');
    expect(lessonApiMock.initiateVideoUpload).not.toHaveBeenCalled();
  });

  it('submits a single-upload flow, converts duration to seconds, and resets on success', async () => {
    lessonApiMock.initiateVideoUpload.mockResolvedValue({
      data: {
        data: {
          video_id: 'video-1',
          upload_mode: 'single',
          upload_id: null,
          part_size_bytes: null,
          single_upload: {
            url: 'https://upload.example.com/single',
            headers: { 'Content-Type': 'video/mp4' },
          },
          multipart_parts: null,
        },
      },
    });
    uploadSingleVideoToS3Mock.mockResolvedValue('"etag-single"');
    lessonApiMock.completeVideoUpload.mockResolvedValue({ data: { data: { video_id: 'video-1' } } });

    render(<UploadVideo />);

    await screen.findByRole('option', { name: 'Course A' });
    fireEvent.change(screen.getByLabelText(/select course/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440000' },
    });

    await screen.findByRole('option', { name: 'Chapter A' });
    fireEvent.change(screen.getByLabelText(/select chapter/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440001' },
    });

    await screen.findByRole('option', { name: 'Lesson A' });
    fireEvent.change(screen.getByLabelText(/select lesson/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440002' },
    });

    fireEvent.change(screen.getByLabelText(/video title/i), {
      target: { value: 'Upload success' },
    });
    fireEvent.change(screen.getByLabelText(/duration \(minutes\)/i), {
      target: { value: '1.5' },
    });

    const file = new File([new Uint8Array([1, 2, 3])], 'lesson.mp4', { type: 'video/mp4' });
    fireEvent.change(screen.getByLabelText(/video file/i), {
      target: { files: [file] },
    });

    fireEvent.click(screen.getByRole('button', { name: /upload video/i }));

    await waitFor(() => {
      expect(lessonApiMock.initiateVideoUpload).toHaveBeenCalledWith(
        '550e8400-e29b-41d4-a716-446655440002',
        expect.objectContaining({
          title: 'Upload success',
          duration: 90,
        })
      );
    });

    await waitFor(() => {
      expect(lessonApiMock.completeVideoUpload).toHaveBeenCalledWith(
        '550e8400-e29b-41d4-a716-446655440002',
        'video-1',
        { etag: '"etag-single"' }
      );
    });

    expect(toastSuccess).toHaveBeenCalledWith('Video uploaded successfully');
    await waitFor(() => {
      expect((screen.getByLabelText(/video title/i) as HTMLInputElement).value).toBe('');
      expect((screen.getByLabelText(/select course/i) as HTMLSelectElement).value).toBe('');
    });
  });

  it('aborts a pending upload when the user cancels', async () => {
    lessonApiMock.initiateVideoUpload.mockResolvedValue({
      data: {
        data: {
          video_id: 'video-cancel',
          upload_mode: 'single',
          upload_id: null,
          part_size_bytes: null,
          single_upload: {
            url: 'https://upload.example.com/single',
            headers: { 'Content-Type': 'video/mp4' },
          },
          multipart_parts: null,
        },
      },
    });

    uploadSingleVideoToS3Mock.mockImplementation(
      (_file: File, _upload: unknown, options: { signal: AbortSignal }) =>
        new Promise((_resolve, reject) => {
          options.signal.addEventListener('abort', () => reject({ code: 'ERR_CANCELED' }));
        })
    );

    render(<UploadVideo />);

    await screen.findByRole('option', { name: 'Course A' });
    fireEvent.change(screen.getByLabelText(/select course/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440000' },
    });
    await screen.findByRole('option', { name: 'Chapter A' });
    fireEvent.change(screen.getByLabelText(/select chapter/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440001' },
    });
    await screen.findByRole('option', { name: 'Lesson A' });
    fireEvent.change(screen.getByLabelText(/select lesson/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440002' },
    });
    fireEvent.change(screen.getByLabelText(/video title/i), {
      target: { value: 'Cancel me' },
    });

    const file = new File([new Uint8Array([1, 2, 3])], 'lesson.mp4', { type: 'video/mp4' });
    fireEvent.change(screen.getByLabelText(/video file/i), {
      target: { files: [file] },
    });

    fireEvent.click(screen.getByRole('button', { name: /upload video/i }));

    const cancelButton = await screen.findByRole('button', { name: /cancel/i });
    fireEvent.click(cancelButton);

    await waitFor(() => {
      expect(lessonApiMock.abortVideoUpload).toHaveBeenCalledWith(
        '550e8400-e29b-41d4-a716-446655440002',
        'video-cancel',
        { upload_id: undefined }
      );
    });
    expect(toastError).toHaveBeenCalledWith('Upload canceled');
  });

  it('aborts the pending upload when the S3 upload fails', async () => {
    lessonApiMock.initiateVideoUpload.mockResolvedValue({
      data: {
        data: {
          video_id: 'video-failure',
          upload_mode: 'single',
          upload_id: null,
          part_size_bytes: null,
          single_upload: {
            url: 'https://upload.example.com/single',
            headers: { 'Content-Type': 'video/mp4' },
          },
          multipart_parts: null,
        },
      },
    });
    uploadSingleVideoToS3Mock.mockRejectedValue(new Error('network broke'));

    render(<UploadVideo />);

    await screen.findByRole('option', { name: 'Course A' });
    fireEvent.change(screen.getByLabelText(/select course/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440000' },
    });
    await screen.findByRole('option', { name: 'Chapter A' });
    fireEvent.change(screen.getByLabelText(/select chapter/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440001' },
    });
    await screen.findByRole('option', { name: 'Lesson A' });
    fireEvent.change(screen.getByLabelText(/select lesson/i), {
      target: { value: '550e8400-e29b-41d4-a716-446655440002' },
    });
    fireEvent.change(screen.getByLabelText(/video title/i), {
      target: { value: 'Fail me' },
    });

    const file = new File([new Uint8Array([1, 2, 3])], 'lesson.mp4', { type: 'video/mp4' });
    fireEvent.change(screen.getByLabelText(/video file/i), {
      target: { files: [file] },
    });

    fireEvent.click(screen.getByRole('button', { name: /upload video/i }));

    await waitFor(() => {
      expect(lessonApiMock.abortVideoUpload).toHaveBeenCalledWith(
        '550e8400-e29b-41d4-a716-446655440002',
        'video-failure',
        { upload_id: undefined }
      );
    });
    expect(toastError).toHaveBeenCalledWith('Failed to upload video');
  });
});
