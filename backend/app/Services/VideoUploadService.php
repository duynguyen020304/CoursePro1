<?php

namespace App\Services;

use App\Models\CourseLesson;
use App\Models\CourseVideo;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class VideoUploadService
{
    public function disk(): string
    {
        return (string) config('video_uploads.disk', 's3');
    }

    public function bucket(): ?string
    {
        return config("filesystems.disks.{$this->disk()}.bucket");
    }

    public function multipartThresholdBytes(): int
    {
        return max(5_242_880, (int) config('video_uploads.multipart_threshold_bytes', 52_428_800));
    }

    public function partSizeBytes(): int
    {
        return max(5_242_880, (int) config('video_uploads.part_size_bytes', 10_485_760));
    }

    public function maxFileSizeBytes(): int
    {
        return (int) config('video_uploads.max_file_size_bytes', 524_288_000);
    }

    public function signedUrlTtlSeconds(): int
    {
        return max(60, (int) config('video_uploads.signed_url_ttl_seconds', 900));
    }

    public function determineUploadMode(int $fileSizeBytes): string
    {
        return $fileSizeBytes > $this->multipartThresholdBytes() ? 'multipart' : 'single';
    }

    public function generateStorageKey(CourseLesson $lesson, string $filename): string
    {
        $prefix = trim((string) config('video_uploads.prefix', 'videos'), '/');
        $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
        $baseName = (string) pathinfo($filename, PATHINFO_FILENAME);
        $sanitized = Str::slug($baseName);
        $sanitized = $sanitized !== '' ? $sanitized : 'video';
        $suffix = Str::uuid();

        $fileName = $extension !== ''
            ? "{$suffix}-{$sanitized}.{$extension}"
            : "{$suffix}-{$sanitized}";

        return "{$prefix}/{$lesson->course_id}/{$lesson->lesson_id}/{$fileName}";
    }

    /**
     * @return array{url: string, headers: array<string, string>}
     */
    public function createSingleUpload(string $storageKey, string $mimeType): array
    {
        $upload = Storage::disk($this->disk())->temporaryUploadUrl(
            $storageKey,
            now()->addSeconds($this->signedUrlTtlSeconds()),
            ['ContentType' => $mimeType]
        );

        $headers = $upload['headers'] ?? [];
        if (! array_key_exists('Content-Type', $headers)) {
            $headers['Content-Type'] = $mimeType;
        }

        return [
            'url' => $upload['url'],
            'headers' => $headers,
        ];
    }

    /**
     * @return array{
     *     upload_id: string,
     *     part_size_bytes: int,
     *     multipart_parts: array<int, array{part_number: int, url: string, headers: array<string, string>}>
     * }
     */
    public function createMultipartUpload(string $storageKey, string $mimeType, int $fileSizeBytes): array
    {
        $bucket = $this->bucket();
        if (! $bucket) {
            throw new RuntimeException('S3 bucket is not configured');
        }

        $partSize = $this->partSizeBytes();
        $partCount = (int) ceil($fileSizeBytes / $partSize);

        if ($partCount > 10_000) {
            throw new RuntimeException('File requires more than the allowed 10,000 multipart upload parts');
        }

        $client = $this->s3Client();
        $result = $client->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $storageKey,
            'ContentType' => $mimeType,
        ]);

        $uploadId = (string) $result->get('UploadId');
        $parts = [];
        $expiresAt = now()->addSeconds($this->signedUrlTtlSeconds());

        for ($partNumber = 1; $partNumber <= $partCount; $partNumber++) {
            $command = $client->getCommand('UploadPart', [
                'Bucket' => $bucket,
                'Key' => $storageKey,
                'UploadId' => $uploadId,
                'PartNumber' => $partNumber,
            ]);

            $request = $client->createPresignedRequest($command, $expiresAt);
            $parts[] = [
                'part_number' => $partNumber,
                'url' => (string) $request->getUri(),
                'headers' => [],
            ];
        }

        return [
            'upload_id' => $uploadId,
            'part_size_bytes' => $partSize,
            'multipart_parts' => $parts,
        ];
    }

    /**
     * @param  array<int, array{part_number:int, etag:string}>  $parts
     * @return array<string, mixed>
     */
    public function completeMultipartUpload(string $storageKey, string $uploadId, array $parts): array
    {
        $bucket = $this->bucket();
        if (! $bucket) {
            throw new RuntimeException('S3 bucket is not configured');
        }

        $completedParts = collect($parts)
            ->sortBy('part_number')
            ->map(fn (array $part) => [
                'PartNumber' => (int) $part['part_number'],
                'ETag' => (string) $part['etag'],
            ])
            ->values()
            ->all();

        return $this->s3Client()->completeMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $storageKey,
            'UploadId' => $uploadId,
            'MultipartUpload' => [
                'Parts' => $completedParts,
            ],
        ])->toArray();
    }

    public function abortMultipartUpload(string $storageKey, string $uploadId): void
    {
        $bucket = $this->bucket();
        if (! $bucket || $uploadId === '') {
            return;
        }

        try {
            $this->s3Client()->abortMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $storageKey,
                'UploadId' => $uploadId,
            ]);
        } catch (AwsException $exception) {
            if ($exception->getAwsErrorCode() !== 'NoSuchUpload') {
                throw $exception;
            }
        }
    }

    public function deleteObject(string $storageKey): void
    {
        if ($storageKey === '') {
            return;
        }

        $deleted = Storage::disk($this->disk())->delete($storageKey);
        if ($deleted === false) {
            throw new RuntimeException('Failed to delete the video from S3');
        }
    }

    public function stableObjectUrl(string $storageKey): ?string
    {
        if ($storageKey === '') {
            return null;
        }

        try {
            return Storage::disk($this->disk())->url($storageKey);
        } catch (\Throwable) {
            return null;
        }
    }

    public function temporaryReadUrl(CourseVideo|string $video): ?string
    {
        $storageKey = $video instanceof CourseVideo ? (string) ($video->storage_key ?? '') : (string) $video;
        if ($storageKey === '') {
            return null;
        }

        return Storage::disk($this->disk())->temporaryUrl(
            $storageKey,
            now()->addSeconds($this->signedUrlTtlSeconds())
        );
    }

    private function s3Client(): S3Client
    {
        $diskConfig = config("filesystems.disks.{$this->disk()}", []);
        $config = [
            'version' => 'latest',
            'region' => $diskConfig['region'] ?? env('AWS_DEFAULT_REGION', 'us-east-1'),
        ];

        if (! empty($diskConfig['key']) && ! empty($diskConfig['secret'])) {
            $config['credentials'] = [
                'key' => $diskConfig['key'],
                'secret' => $diskConfig['secret'],
            ];
        }

        if (! empty($diskConfig['endpoint'])) {
            $config['endpoint'] = $diskConfig['endpoint'];
        }

        if (array_key_exists('use_path_style_endpoint', $diskConfig)) {
            $config['use_path_style_endpoint'] = (bool) $diskConfig['use_path_style_endpoint'];
        }

        return new S3Client($config);
    }
}
