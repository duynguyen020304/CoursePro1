import { z } from 'zod';

const uuidPattern = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

const requiredUuid = (message: string) => z.string().min(1, message).regex(uuidPattern, message);

/**
 * UploadVideo form schema using Zod
 * Validates video upload form data including file type validation
 */
export const uploadVideoSchema = z.object({
  title: z.string().min(1, 'Title is required'),
  course_id: requiredUuid('Course is required'),
  chapter_id: requiredUuid('Chapter is required'),
  lesson_id: requiredUuid('Lesson is required'),
  video_file: z
    .instanceof(File, { message: 'Video file is required' })
    .refine(
      (file) => {
        const validTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
        return validTypes.includes(file.type);
      },
      { message: 'Only MP4, WebM, OGV, or MOV video files are allowed' }
    )
    .refine((file) => file.size <= 500 * 1024 * 1024, {
      message: 'Video file must be less than 500MB',
    }),
  duration: z.number().optional(),
});

/**
 * Type inference from schema
 */
export type UploadVideoFormData = z.infer<typeof uploadVideoSchema>;

/**
 * Validate upload video form data
 * Returns the parsed data on success, throws on failure
 */
export function validateUploadVideoForm(data: unknown) {
  return uploadVideoSchema.parse(data);
}

/**
 * Safe validate upload video form data
 * Returns result object with success flag
 */
export function safeValidateUploadVideoForm(data: unknown) {
  return uploadVideoSchema.safeParse(data);
}
