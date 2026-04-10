import axios from 'axios';

export interface SingleUploadContract {
  url: string;
  headers?: Record<string, string>;
}

export interface MultipartUploadPart {
  part_number: number;
  url: string;
  headers?: Record<string, string>;
}

export interface UploadedMultipartPart {
  part_number: number;
  etag: string;
}

interface UploadOptions {
  signal: AbortSignal;
  onProgress: (progressPercent: number) => void;
}

export function convertDurationMinutesToSeconds(duration?: number): number | undefined {
  if (typeof duration !== 'number' || Number.isNaN(duration)) {
    return undefined;
  }

  return Math.round(duration * 60);
}

export function isAbortError(error: unknown): boolean {
  if (axios.isCancel(error)) {
    return true;
  }

  if (error instanceof DOMException && error.name === 'AbortError') {
    return true;
  }

  return Boolean(
    error
    && typeof error === 'object'
    && 'code' in error
    && (error as { code?: string }).code === 'ERR_CANCELED'
  );
}

export async function uploadSingleVideoToS3(
  file: File,
  upload: SingleUploadContract,
  options: UploadOptions
): Promise<string> {
  const response = await axios.put(upload.url, file, {
    headers: upload.headers ?? {},
    signal: options.signal,
    onUploadProgress: (event) => {
      if (!event.total) {
        return;
      }

      options.onProgress(Math.round((event.loaded / event.total) * 100));
    },
  });

  const etag = readHeader(response.headers, 'etag');
  if (!etag) {
    throw new Error('S3 upload did not return an ETag header');
  }

  options.onProgress(100);

  return etag;
}

export async function uploadMultipartVideoToS3(
  file: File,
  parts: MultipartUploadPart[],
  partSizeBytes: number,
  options: UploadOptions
): Promise<UploadedMultipartPart[]> {
  const totalBytes = file.size;
  const loadedByPart = new Map<number, number>();
  const completedParts: UploadedMultipartPart[] = [];

  for (const part of parts) {
    const start = (part.part_number - 1) * partSizeBytes;
    const end = Math.min(start + partSizeBytes, totalBytes);
    const chunk = file.slice(start, end);

    const response = await axios.put(part.url, chunk, {
      headers: part.headers ?? {},
      signal: options.signal,
      onUploadProgress: (event) => {
        loadedByPart.set(part.part_number, event.loaded);
        const uploadedBytes = [...loadedByPart.values()].reduce((sum, value) => sum + value, 0);
        options.onProgress(Math.min(100, Math.round((uploadedBytes / totalBytes) * 100)));
      },
    });

    const etag = readHeader(response.headers, 'etag');
    if (!etag) {
      throw new Error(`S3 multipart upload part ${part.part_number} did not return an ETag header`);
    }

    loadedByPart.set(part.part_number, chunk.size);
    completedParts.push({
      part_number: part.part_number,
      etag,
    });
  }

  options.onProgress(100);

  return completedParts.sort((left, right) => left.part_number - right.part_number);
}

function readHeader(headers: Record<string, unknown> | undefined, key: string): string | undefined {
  if (!headers) {
    return undefined;
  }

  const directValue = headers[key] ?? headers[key.toLowerCase()] ?? headers[key.toUpperCase()];
  return typeof directValue === 'string' ? directValue : undefined;
}
