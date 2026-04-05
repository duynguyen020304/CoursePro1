import { z } from 'zod';
import { paginationSchema } from '../common';

// User schema for nested User within Instructor
const userSchema = z.object({
  user_id: z.string(),
  role_id: z.string(),
  first_name: z.string().nullable().optional(),
  last_name: z.string().nullable().optional(),
  profile_image: z.string().nullable(),
  email: z.string().nullable().optional(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
});

// Instructor schema
const instructorSchema = z.object({
  instructor_id: z.string(),
  user_id: z.string(),
  biography: z.string().nullable(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  user: userSchema,
});

// Category schema
const categorySchema = z.object({
  id: z.number(),
  name: z.string(),
  slug: z.string().nullable().optional(),
  parent_id: z.number().nullable(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
});

// Image schema
const imageSchema = z.object({
  image_id: z.string(),
  course_id: z.string(),
  image_path: z.string(),
  caption: z.string().nullable().optional(),
  is_primary: z.number().optional().default(0),
  sort_order: z.number().optional().default(0),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
});

// Video schema
const videoSchema = z.object({
  video_id: z.string(),
  lesson_id: z.string(),
  url: z.string().nullable(),
  title: z.string().nullable().optional(),
  duration: z.number().nullable(),
  sort_order: z.number().optional().default(1),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
});

// Resource schema
const resourceSchema = z.object({
  resource_id: z.string(),
  lesson_id: z.string(),
  resource_path: z.string(),
  title: z.string().nullable().optional(),
  sort_order: z.number().optional().default(1),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
});

// Lesson schema
const lessonSchema = z.object({
  lesson_id: z.string(),
  course_id: z.string(),
  chapter_id: z.string(),
  title: z.string(),
  content: z.string().nullable().optional(),
  sort_order: z.number().optional().default(0),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  videos: z.array(videoSchema).optional(),
  resources: z.array(resourceSchema).optional(),
});

// Chapter schema
const chapterSchema = z.object({
  chapter_id: z.string(),
  course_id: z.string(),
  title: z.string(),
  description: z.string().nullable().optional(),
  sort_order: z.number().optional().default(0),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  lessons: z.array(lessonSchema).optional(),
});

// Review user schema
const reviewUserSchema = z.object({
  user_id: z.string(),
  role_id: z.string(),
  first_name: z.string().nullable().optional(),
  last_name: z.string().nullable().optional(),
  profile_image: z.string().nullable(),
  email: z.string().nullable().optional(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
});

// Review schema
const reviewSchema = z.object({
  review_id: z.string(),
  user_id: z.string(),
  course_id: z.string(),
  rating: z.number(),
  review_text: z.string().nullable().optional(),
  is_active: z.boolean().optional().default(true),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  deleted_at: z.string().nullable().optional(),
  user: reviewUserSchema.optional(),
});

// Objective schema
const objectiveSchema = z.object({
  objective_id: z.string(),
  course_id: z.string(),
  objective: z.string(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
});

// Requirement schema
const requirementSchema = z.object({
  requirement_id: z.string(),
  course_id: z.string(),
  requirement: z.string(),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
});

// Course schema (exported for use in other schemas) - matches backend response
export const courseSchema = z.object({
  course_id: z.string(),
  title: z.string(),
  description: z.string().optional(),
  price: z.number().optional(),
  difficulty: z.string().optional().default('Beginner'),
  language: z.string().optional().default('vi'),
  is_active: z.boolean().optional().default(true),
  deleted_at: z.string().nullable().optional(),
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),
  created_by: z.string().nullable().optional(),
  thumbnail_url: z.string().nullable().optional(),
  instructor: instructorSchema.optional(),
  categories: z.array(categorySchema).optional(),
  images: z.array(imageSchema).optional(),
  objectives: z.array(objectiveSchema).optional(),
  requirements: z.array(requirementSchema).optional(),
  chapters: z.array(chapterSchema).optional(),
  reviews: z.array(reviewSchema).optional(),
});

// API Response Schemas

/**
 * CourseListResponse - Paginated list of courses (backend wraps in data.data)
 */
export const courseListResponseSchema = z.object({
  data: z.object({
    current_page: z.number(),
    data: z.array(courseSchema),
    first_page_url: z.string().nullable(),
    from: z.number().nullable(),
    last_page: z.number(),
    last_page_url: z.string().nullable(),
    links: z.array(z.object({
      url: z.string().nullable(),
      label: z.string(),
      page: z.number().nullable(),
      active: z.boolean(),
    })),
    next_page_url: z.string().nullable(),
    path: z.string(),
    per_page: z.number(),
    prev_page_url: z.string().nullable(),
    to: z.number().nullable(),
    total: z.number(),
  }),
});

/**
 * CourseDetailResponse - Single course with instructors, chapters, nested in data.data
 */
export const courseDetailResponseSchema = z.object({
  data: z.object({
    course: courseSchema,
    average_rating: z.number().optional(),
    total_reviews: z.number().optional(),
  }),
});

// Type inference helpers
export type Course = z.infer<typeof courseSchema>;
export type Chapter = z.infer<typeof chapterSchema>;
export type Lesson = z.infer<typeof lessonSchema>;
export type Instructor = z.infer<typeof instructorSchema>;
export type Category = z.infer<typeof categorySchema>;
export type CourseListResponse = z.infer<typeof courseListResponseSchema>;
export type CourseDetailResponse = z.infer<typeof courseDetailResponseSchema>;
