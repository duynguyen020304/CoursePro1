import { beforeEach, describe, expect, it, vi } from 'vitest';
import axios from 'axios';
import {
  convertDurationMinutesToSeconds,
  isAbortError,
  uploadMultipartVideoToS3,
  uploadSingleVideoToS3,
} from '../../pages/admin/uploadVideo.upload';

vi.mock('axios', () => ({
  default: {
    put: vi.fn(),
    isCancel: vi.fn((error: unknown) => Boolean(
      error && typeof error === 'object' && 'code' in error && (error as { code?: string }).code === 'ERR_CANCELED'
    )),
  },
}));

describe('uploadVideo upload helpers', () => {
  const mockedAxios = axios as unknown as {
    put: ReturnType<typeof vi.fn>;
    isCancel: ReturnType<typeof vi.fn>;
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('converts minutes to rounded seconds', () => {
    expect(convertDurationMinutesToSeconds(1.5)).toBe(90);
    expect(convertDurationMinutesToSeconds(undefined)).toBeUndefined();
  });

  it('uploads a single file and returns the ETag', async () => {
    mockedAxios.put.mockImplementation(async (_url, _body, config) => {
      config?.onUploadProgress?.({ loaded: 50, total: 100 } as never);
      config?.onUploadProgress?.({ loaded: 100, total: 100 } as never);
      return { headers: { etag: '"single-etag"' } } as never;
    });

    const file = new File([new Uint8Array([1, 2, 3])], 'lesson.mp4', { type: 'video/mp4' });
    const progressValues: number[] = [];
    const etag = await uploadSingleVideoToS3(file, {
      url: 'https://upload.example.com/single',
      headers: { 'Content-Type': 'video/mp4' },
    }, {
      signal: new AbortController().signal,
      onProgress: (value) => progressValues.push(value),
    });

    expect(etag).toBe('"single-etag"');
    expect(progressValues).toContain(50);
    expect(progressValues.at(-1)).toBe(100);
  });

  it('uploads multipart parts sequentially and reports aggregate progress', async () => {
    mockedAxios.put
      .mockImplementationOnce(async (_url, _body, config) => {
        config?.onUploadProgress?.({ loaded: 5, total: 5 } as never);
        return { headers: { etag: '"part-1"' } } as never;
      })
      .mockImplementationOnce(async (_url, _body, config) => {
        config?.onUploadProgress?.({ loaded: 5, total: 5 } as never);
        return { headers: { etag: '"part-2"' } } as never;
      });

    const file = new File([
      new Uint8Array([1, 2, 3, 4, 5]),
      new Uint8Array([6, 7, 8, 9, 10]),
    ], 'lesson-large.mp4', { type: 'video/mp4' });
    const progressValues: number[] = [];

    const parts = await uploadMultipartVideoToS3(file, [
      { part_number: 1, url: 'https://upload.example.com/part-1' },
      { part_number: 2, url: 'https://upload.example.com/part-2' },
    ], 5, {
      signal: new AbortController().signal,
      onProgress: (value) => progressValues.push(value),
    });

    expect(parts).toEqual([
      { part_number: 1, etag: '"part-1"' },
      { part_number: 2, etag: '"part-2"' },
    ]);
    expect(progressValues).toContain(50);
    expect(progressValues.at(-1)).toBe(100);
  });

  it('detects canceled uploads', () => {
    expect(isAbortError({ code: 'ERR_CANCELED' })).toBe(true);
    expect(isAbortError(new DOMException('Aborted', 'AbortError'))).toBe(true);
    expect(isAbortError(new Error('boom'))).toBe(false);
  });
});
