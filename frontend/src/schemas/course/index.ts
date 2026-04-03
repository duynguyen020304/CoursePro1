// Course Zod Schemas
// Contains validation schemas for course-related forms and API responses

// Form schemas
export * from './createCourse.schema';
export * from './uploadVideo.schema';

// API response schemas (includes Category, Lesson, Chapter types)
export * from './apiResponses.schema';

// Additional schemas - only export types that don't conflict with apiResponses.schema
// Category, Lesson, Chapter types are already exported from apiResponses.schema
export { categoryListResponseSchema, categoryDetailResponseSchema } from './category.schema';
export type { Category, CategoryListResponse, CategoryDetailResponse } from './category.schema';
export { lessonResponseSchema, lessonListResponseSchema, videoListResponseSchema, resourceListResponseSchema } from './lesson.schema';
export type { LessonResponse, LessonListResponse, VideoListResponse, ResourceListResponse } from './lesson.schema';
export { chapterListResponseSchema, chapterDetailResponseSchema, chapterResponseSchema, chapterWithLessonsSchema } from './chapter.schema';
export type { ChapterResponse, ChapterListResponse, ChapterDetailResponse, ChapterWithLessons } from './chapter.schema';
export { publicInstructorSchema, instructorListResponseSchema, instructorDetailResponseSchema } from './instructorPublic.schema';
export type { PublicInstructor, InstructorListResponse, InstructorDetailResponse } from './instructorPublic.schema';
