import { useQuery } from '@tanstack/react-query';
import { useParams, useNavigate } from 'react-router-dom';
import { instructorApi } from '../../services/api';
import CourseForm from './components/CourseForm';

interface Category {
  id: string;
  name?: string;
}

interface Course {
  title?: string;
  description?: string | null;
  price?: number;
  difficulty?: string;
  language?: string;
  categories?: Array<{ id: string }>;
  objectives?: Array<{ objective?: string }>;
  requirements?: Array<{ requirement?: string }>;
  chapters?: Chapter[];
}

interface Chapter {
  chapter_id: string | number;
  title?: string;
  description?: string;
  sort_order?: number;
  lessons?: Lesson[];
}

interface Lesson {
  lesson_id: string | number;
  title?: string;
  content?: string;
  sort_order?: number;
}

export default function EditCourse() {
  const { courseId } = useParams<{ courseId: string }>();
  const navigate = useNavigate();

  const { data: course, isLoading, error } = useQuery<Course | null>({
    queryKey: ['instructor', 'course', courseId],
    enabled: Boolean(courseId),
    queryFn: async () => {
      const response = await instructorApi.getCourse(courseId!);
      return response.data.success ? (response.data.data as Course) : null;
    },
  });

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  if (error || !course) {
    return (
      <div className="bg-red-50 text-red-600 p-4 rounded-lg">
        Failed to load course
      </div>
    );
  }

  return (
    <CourseForm
      mode="edit"
      courseId={courseId}
      initialData={course}
      onSuccess={() => navigate('/instructor/courses')}
      onCancel={() => navigate('/instructor/courses')}
    />
  );
}
