<?php

namespace App\Http\Controllers\Traits;

use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\CourseImage;
use App\Models\CourseLesson;
use App\Models\CourseObjective;
use App\Models\CourseRequirement;
use App\Models\CourseVideo;
use App\Models\CourseResource;
use Illuminate\Http\JsonResponse;

/**
 * Reusable ownership guard for shared-write endpoints.
 *
 * Rules:
 *  - Admins always pass through.
 *  - Instructors must own the course (course.created_by === instructor_id).
 *  - Everyone else receives 403.
 */
trait EnsuresCourseOwnership
{
    /**
     * Authorize that the authenticated user owns the given course.
     *
     * @param  Course  $course  The course to check ownership of.
     * @return JsonResponse|null  Null when authorised; a 403 response when not.
     */
    protected function authorizeCourseOwner(Course $course): ?JsonResponse
    {
        $user = request()->user();

        // Admins always have access
        if ($user->hasRole('admin')) {
            return null;
        }

        // Instructors must own the course
        if ($user->hasRole('instructor') && $user->instructor) {
            if ($course->created_by === $user->instructor->instructor_id) {
                return null;
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to modify this course',
            'data' => null,
        ], 403);
    }

    /**
     * Load a course by ID and authorize ownership in one call.
     *
     * @param  string  $courseId
     * @return array{0: Course|null, 1: JsonResponse|null}  [course, error-response]
     */
    protected function loadAndAuthorizeCourse(string $courseId): array
    {
        $course = Course::where('course_id', $courseId)->first();

        if (!$course) {
            return [null, response()->json([
                'success' => false,
                'message' => 'Course not found',
                'data' => null,
            ], 404)];
        }

        $error = $this->authorizeCourseOwner($course);
        return [$course, $error];
    }

    /**
     * Authorize that the authenticated user owns the course that a chapter belongs to.
     */
    protected function authorizeChapterOwner(CourseChapter $chapter): ?JsonResponse
    {
        $course = Course::where('course_id', $chapter->course_id)->first();
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
                'data' => null,
            ], 404);
        }
        return $this->authorizeCourseOwner($course);
    }

    /**
     * Authorize that the authenticated user owns the course that a lesson belongs to.
     */
    protected function authorizeLessonOwner(CourseLesson $lesson): ?JsonResponse
    {
        $course = Course::where('course_id', $lesson->course_id)->first();
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
                'data' => null,
            ], 404);
        }
        return $this->authorizeCourseOwner($course);
    }

    /**
     * Authorize via the course that a video's lesson belongs to.
     */
    protected function authorizeVideoOwner(CourseVideo $video): ?JsonResponse
    {
        $lesson = CourseLesson::where('lesson_id', $video->lesson_id)->first();
        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson not found',
                'data' => null,
            ], 404);
        }
        return $this->authorizeLessonOwner($lesson);
    }

    /**
     * Authorize via the course that a resource's lesson belongs to.
     */
    protected function authorizeResourceOwner(CourseResource $resource): ?JsonResponse
    {
        $lesson = CourseLesson::where('lesson_id', $resource->lesson_id)->first();
        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson not found',
                'data' => null,
            ], 404);
        }
        return $this->authorizeLessonOwner($lesson);
    }

    /**
     * Authorize via the course that an image belongs to.
     */
    protected function authorizeImageOwner(CourseImage $image): ?JsonResponse
    {
        $course = Course::where('course_id', $image->course_id)->first();
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
                'data' => null,
            ], 404);
        }
        return $this->authorizeCourseOwner($course);
    }

    /**
     * Authorize via the course that an objective belongs to.
     */
    protected function authorizeObjectiveOwner(CourseObjective $objective): ?JsonResponse
    {
        $course = Course::where('course_id', $objective->course_id)->first();
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
                'data' => null,
            ], 404);
        }
        return $this->authorizeCourseOwner($course);
    }

    /**
     * Authorize via the course that a requirement belongs to.
     */
    protected function authorizeRequirementOwner(CourseRequirement $requirement): ?JsonResponse
    {
        $course = Course::where('course_id', $requirement->course_id)->first();
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
                'data' => null,
            ], 404);
        }
        return $this->authorizeCourseOwner($course);
    }
}
