<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\CourseLesson;
use App\Models\CourseVideo;
use App\Models\Instructor;
use App\Models\Role as UserRole;
use App\Models\User;
use App\Models\UserAccount;
use App\Services\VideoUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class VideoUploadFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerUser;
    private User $otherUser;
    private Instructor $ownerInstructor;
    private Course $course;
    private CourseChapter $chapter;
    private CourseLesson $lesson;

    protected function setUp(): void
    {
        parent::setUp();

        UserRole::firstOrCreate(
            ['role_code' => 'admin'],
            ['role_id' => \App\Support\SeedData\DefaultRoles::ADMIN_ID, 'role_name' => 'Admin']
        );
        UserRole::firstOrCreate(
            ['role_code' => 'student'],
            ['role_id' => \App\Support\SeedData\DefaultRoles::STUDENT_ID, 'role_name' => 'Student']
        );
        UserRole::firstOrCreate(
            ['role_code' => 'instructor'],
            ['role_id' => \App\Support\SeedData\DefaultRoles::INSTRUCTOR_ID, 'role_name' => 'Instructor']
        );

        $this->ownerUser = User::create([
            'user_id' => Str::uuid(),
            'first_name' => 'Owner',
            'last_name' => 'Instructor',
            'role_id' => UserRole::where('role_code', 'instructor')->value('role_id'),
        ]);
        UserAccount::create([
            'user_id' => $this->ownerUser->user_id,
            'email' => 'owner-video@test.com',
            'password' => bcrypt('password'),
            'provider' => 'email',
        ]);

        $this->ownerInstructor = Instructor::create([
            'instructor_id' => Str::uuid(),
            'user_id' => $this->ownerUser->user_id,
            'biography' => 'Owner biography',
            'is_active' => true,
        ]);

        $this->otherUser = User::create([
            'user_id' => Str::uuid(),
            'first_name' => 'Other',
            'last_name' => 'Instructor',
            'role_id' => UserRole::where('role_code', 'instructor')->value('role_id'),
        ]);
        UserAccount::create([
            'user_id' => $this->otherUser->user_id,
            'email' => 'other-video@test.com',
            'password' => bcrypt('password'),
            'provider' => 'email',
        ]);

        Instructor::create([
            'instructor_id' => Str::uuid(),
            'user_id' => $this->otherUser->user_id,
            'biography' => 'Other biography',
            'is_active' => true,
        ]);

        $this->course = Course::create([
            'course_id' => Str::uuid(),
            'title' => 'Video Course',
            'description' => 'Test course',
            'price' => 100,
            'difficulty' => 'Beginner',
            'language' => 'English',
            'created_by' => $this->ownerInstructor->instructor_id,
        ]);

        $this->chapter = CourseChapter::create([
            'chapter_id' => Str::uuid(),
            'course_id' => $this->course->course_id,
            'title' => 'Video Chapter',
            'sort_order' => 0,
        ]);

        $this->lesson = CourseLesson::create([
            'lesson_id' => Str::uuid(),
            'course_id' => $this->course->course_id,
            'chapter_id' => $this->chapter->chapter_id,
            'title' => 'Video Lesson',
            'sort_order' => 0,
        ]);
    }

    public function test_owner_can_initiate_single_upload(): void
    {
        $service = $this->mockUploadService();
        $service->shouldReceive('maxFileSizeBytes')->andReturn(524288000);
        $service->shouldReceive('generateStorageKey')
            ->once()
            ->andReturn("videos/{$this->course->course_id}/{$this->lesson->lesson_id}/single.mp4");
        $service->shouldReceive('determineUploadMode')->once()->andReturn('single');
        $service->shouldReceive('disk')->once()->andReturn('s3');
        $service->shouldReceive('bucket')->once()->andReturn('course-bucket');
        $service->shouldReceive('createSingleUpload')
            ->once()
            ->andReturn([
                'url' => 'https://upload.example.com/single',
                'headers' => ['Content-Type' => 'video/mp4'],
            ]);

        $response = $this->withHeaders($this->authHeaders($this->ownerUser))
            ->postJson("/api/lessons/{$this->lesson->lesson_id}/videos/uploads/initiate", [
                'title' => 'Single upload',
                'filename' => 'lesson.mp4',
                'mime_type' => 'video/mp4',
                'file_size_bytes' => 1024,
                'duration' => 95,
                'sort_order' => 2,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.upload_mode', 'single')
            ->assertJsonPath('data.single_upload.url', 'https://upload.example.com/single');

        $this->assertDatabaseHas('course_videos', [
            'lesson_id' => $this->lesson->lesson_id,
            'title' => 'Single upload',
            'upload_status' => 'uploading',
            'storage_key' => "videos/{$this->course->course_id}/{$this->lesson->lesson_id}/single.mp4",
            'upload_id' => null,
        ]);
    }

    public function test_owner_can_initiate_multipart_upload(): void
    {
        $service = $this->mockUploadService();
        $service->shouldReceive('maxFileSizeBytes')->andReturn(524288000);
        $service->shouldReceive('generateStorageKey')
            ->once()
            ->andReturn("videos/{$this->course->course_id}/{$this->lesson->lesson_id}/multipart.mp4");
        $service->shouldReceive('determineUploadMode')->once()->andReturn('multipart');
        $service->shouldReceive('disk')->once()->andReturn('s3');
        $service->shouldReceive('bucket')->once()->andReturn('course-bucket');
        $service->shouldReceive('createMultipartUpload')
            ->once()
            ->andReturn([
                'upload_id' => 'upload-123',
                'part_size_bytes' => 10485760,
                'multipart_parts' => [
                    ['part_number' => 1, 'url' => 'https://upload.example.com/part-1', 'headers' => []],
                    ['part_number' => 2, 'url' => 'https://upload.example.com/part-2', 'headers' => []],
                ],
            ]);

        $response = $this->withHeaders($this->authHeaders($this->ownerUser))
            ->postJson("/api/lessons/{$this->lesson->lesson_id}/videos/uploads/initiate", [
                'title' => 'Multipart upload',
                'filename' => 'lesson-large.mp4',
                'mime_type' => 'video/mp4',
                'file_size_bytes' => 73400320,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.upload_mode', 'multipart')
            ->assertJsonPath('data.upload_id', 'upload-123')
            ->assertJsonCount(2, 'data.multipart_parts');

        $this->assertDatabaseHas('course_videos', [
            'lesson_id' => $this->lesson->lesson_id,
            'title' => 'Multipart upload',
            'upload_status' => 'uploading',
            'upload_id' => 'upload-123',
        ]);
    }

    public function test_non_owner_cannot_initiate_upload(): void
    {
        $response = $this->withHeaders($this->authHeaders($this->otherUser))
            ->postJson("/api/lessons/{$this->lesson->lesson_id}/videos/uploads/initiate", [
                'title' => 'Blocked upload',
                'filename' => 'lesson.mp4',
                'mime_type' => 'video/mp4',
                'file_size_bytes' => 1024,
            ]);

        $response->assertForbidden()
            ->assertJson(['success' => false]);
    }

    public function test_complete_single_upload_marks_video_ready_and_returns_signed_url(): void
    {
        $video = CourseVideo::create([
            'video_id' => Str::uuid(),
            'lesson_id' => $this->lesson->lesson_id,
            'title' => 'Pending single',
            'duration' => 120,
            'sort_order' => 0,
            'storage_disk' => 's3',
            'storage_bucket' => 'course-bucket',
            'storage_key' => "videos/{$this->course->course_id}/{$this->lesson->lesson_id}/pending-single.mp4",
            'mime_type' => 'video/mp4',
            'file_size_bytes' => 1024,
            'upload_status' => 'uploading',
            'original_filename' => 'pending-single.mp4',
        ]);

        $service = $this->mockUploadService();
        $service->shouldReceive('stableObjectUrl')
            ->once()
            ->with((string) $video->storage_key)
            ->andReturn('https://bucket.example.com/videos/pending-single.mp4');
        $service->shouldReceive('temporaryReadUrl')
            ->atLeast()
            ->once()
            ->andReturn('https://signed.example.com/videos/pending-single.mp4');

        $response = $this->withHeaders($this->authHeaders($this->ownerUser))
            ->postJson("/api/lessons/{$this->lesson->lesson_id}/videos/{$video->video_id}/uploads/complete", [
                'etag' => '"etag-single"',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.upload_status', 'ready')
            ->assertJsonPath('data.upload_id', null)
            ->assertJsonPath('data.url', 'https://signed.example.com/videos/pending-single.mp4');

        $this->assertDatabaseHas('course_videos', [
            'video_id' => $video->video_id,
            'upload_status' => 'ready',
            'upload_id' => null,
            'url' => 'https://bucket.example.com/videos/pending-single.mp4',
        ]);
    }

    public function test_complete_multipart_upload_marks_video_ready(): void
    {
        $video = CourseVideo::create([
            'video_id' => Str::uuid(),
            'lesson_id' => $this->lesson->lesson_id,
            'title' => 'Pending multipart',
            'duration' => 300,
            'sort_order' => 0,
            'storage_disk' => 's3',
            'storage_bucket' => 'course-bucket',
            'storage_key' => "videos/{$this->course->course_id}/{$this->lesson->lesson_id}/pending-multipart.mp4",
            'mime_type' => 'video/mp4',
            'file_size_bytes' => 73400320,
            'upload_status' => 'uploading',
            'upload_id' => 'upload-abc',
            'original_filename' => 'pending-multipart.mp4',
        ]);

        $service = $this->mockUploadService();
        $service->shouldReceive('completeMultipartUpload')
            ->once()
            ->with((string) $video->storage_key, 'upload-abc', Mockery::type('array'))
            ->andReturn(['ETag' => '"final-etag"']);
        $service->shouldReceive('stableObjectUrl')
            ->once()
            ->andReturn('https://bucket.example.com/videos/pending-multipart.mp4');
        $service->shouldReceive('temporaryReadUrl')
            ->atLeast()
            ->once()
            ->andReturn('https://signed.example.com/videos/pending-multipart.mp4');

        $response = $this->withHeaders($this->authHeaders($this->ownerUser))
            ->postJson("/api/lessons/{$this->lesson->lesson_id}/videos/{$video->video_id}/uploads/complete", [
                'upload_id' => 'upload-abc',
                'parts' => [
                    ['part_number' => 1, 'etag' => '"part-1"'],
                    ['part_number' => 2, 'etag' => '"part-2"'],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.upload_status', 'ready')
            ->assertJsonPath('data.url', 'https://signed.example.com/videos/pending-multipart.mp4');

        $this->assertDatabaseHas('course_videos', [
            'video_id' => $video->video_id,
            'upload_status' => 'ready',
            'upload_id' => null,
        ]);
    }

    public function test_abort_upload_removes_pending_row(): void
    {
        $video = CourseVideo::create([
            'video_id' => Str::uuid(),
            'lesson_id' => $this->lesson->lesson_id,
            'title' => 'Abort me',
            'duration' => 90,
            'sort_order' => 0,
            'storage_disk' => 's3',
            'storage_bucket' => 'course-bucket',
            'storage_key' => "videos/{$this->course->course_id}/{$this->lesson->lesson_id}/abort.mp4",
            'mime_type' => 'video/mp4',
            'file_size_bytes' => 73400320,
            'upload_status' => 'uploading',
            'upload_id' => 'upload-abort',
            'original_filename' => 'abort.mp4',
        ]);

        $service = $this->mockUploadService();
        $service->shouldReceive('abortMultipartUpload')
            ->once()
            ->with((string) $video->storage_key, 'upload-abort');

        $response = $this->withHeaders($this->authHeaders($this->ownerUser))
            ->postJson("/api/lessons/{$this->lesson->lesson_id}/videos/{$video->video_id}/uploads/abort", [
                'upload_id' => 'upload-abort',
            ]);

        $response->assertOk();
        $this->assertDatabaseMissing('course_videos', ['video_id' => $video->video_id]);
    }

    public function test_list_videos_excludes_uploading_rows_and_preserves_legacy_urls(): void
    {
        CourseVideo::create([
            'video_id' => Str::uuid(),
            'lesson_id' => $this->lesson->lesson_id,
            'title' => 'Legacy',
            'url' => 'https://legacy.example.com/video.mp4',
            'duration' => 50,
            'sort_order' => 1,
            'upload_status' => 'ready',
        ]);

        CourseVideo::create([
            'video_id' => Str::uuid(),
            'lesson_id' => $this->lesson->lesson_id,
            'title' => 'Ready S3',
            'duration' => 70,
            'sort_order' => 2,
            'storage_disk' => 's3',
            'storage_bucket' => 'course-bucket',
            'storage_key' => "videos/{$this->course->course_id}/{$this->lesson->lesson_id}/ready.mp4",
            'mime_type' => 'video/mp4',
            'file_size_bytes' => 2048,
            'upload_status' => 'ready',
            'url' => 'https://bucket.example.com/videos/ready.mp4',
        ]);

        CourseVideo::create([
            'video_id' => Str::uuid(),
            'lesson_id' => $this->lesson->lesson_id,
            'title' => 'Uploading',
            'duration' => 80,
            'sort_order' => 3,
            'storage_disk' => 's3',
            'storage_bucket' => 'course-bucket',
            'storage_key' => "videos/{$this->course->course_id}/{$this->lesson->lesson_id}/uploading.mp4",
            'mime_type' => 'video/mp4',
            'file_size_bytes' => 4096,
            'upload_status' => 'uploading',
        ]);

        $service = $this->mockUploadService();
        $service->shouldReceive('temporaryReadUrl')
            ->atLeast()
            ->once()
            ->andReturn('https://signed.example.com/videos/ready.mp4');

        $response = $this->withHeaders($this->authHeaders($this->ownerUser))
            ->getJson("/api/lessons/{$this->lesson->lesson_id}/videos");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['title' => 'Legacy', 'url' => 'https://legacy.example.com/video.mp4'])
            ->assertJsonFragment(['title' => 'Ready S3', 'url' => 'https://signed.example.com/videos/ready.mp4'])
            ->assertJsonMissing(['title' => 'Uploading']);
    }

    public function test_delete_video_removes_s3_object_and_soft_deletes_row(): void
    {
        $video = CourseVideo::create([
            'video_id' => Str::uuid(),
            'lesson_id' => $this->lesson->lesson_id,
            'title' => 'Delete me',
            'duration' => 60,
            'sort_order' => 1,
            'storage_disk' => 's3',
            'storage_bucket' => 'course-bucket',
            'storage_key' => "videos/{$this->course->course_id}/{$this->lesson->lesson_id}/delete.mp4",
            'mime_type' => 'video/mp4',
            'file_size_bytes' => 1024,
            'upload_status' => 'ready',
            'url' => 'https://bucket.example.com/videos/delete.mp4',
        ]);

        $service = $this->mockUploadService();
        $service->shouldReceive('deleteObject')
            ->once()
            ->with((string) $video->storage_key);

        $response = $this->withHeaders($this->authHeaders($this->ownerUser))
            ->deleteJson("/api/lessons/{$this->lesson->lesson_id}/videos/{$video->video_id}");

        $response->assertOk();
        $this->assertSoftDeleted('course_videos', ['video_id' => $video->video_id]);
    }

    public function test_delete_video_returns_error_when_remote_delete_fails(): void
    {
        $video = CourseVideo::create([
            'video_id' => Str::uuid(),
            'lesson_id' => $this->lesson->lesson_id,
            'title' => 'Delete fails',
            'duration' => 60,
            'sort_order' => 1,
            'storage_disk' => 's3',
            'storage_bucket' => 'course-bucket',
            'storage_key' => "videos/{$this->course->course_id}/{$this->lesson->lesson_id}/delete-fails.mp4",
            'mime_type' => 'video/mp4',
            'file_size_bytes' => 1024,
            'upload_status' => 'ready',
            'url' => 'https://bucket.example.com/videos/delete-fails.mp4',
        ]);

        $service = $this->mockUploadService();
        $service->shouldReceive('deleteObject')
            ->once()
            ->andThrow(new \RuntimeException('remote delete failed'));

        $response = $this->withHeaders($this->authHeaders($this->ownerUser))
            ->deleteJson("/api/lessons/{$this->lesson->lesson_id}/videos/{$video->video_id}");

        $response->assertStatus(500);
        $this->assertDatabaseHas('course_videos', [
            'video_id' => $video->video_id,
            'deleted_at' => null,
        ]);
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('test-token')->plainTextToken;
    }

    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $this->tokenFor($user)];
    }

    private function mockUploadService(): MockInterface
    {
        $mock = Mockery::mock(VideoUploadService::class);
        $this->app->instance(VideoUploadService::class, $mock);

        return $mock;
    }
}
