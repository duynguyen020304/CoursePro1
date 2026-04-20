import { useNavigate } from 'react-router-dom';
import CourseForm from './components/CourseForm';

export default function CreateCourse() {
  const navigate = useNavigate();

  return (
    <CourseForm
      mode="create"
      onSuccess={() => navigate('/instructor/courses')}
      onCancel={() => navigate('/instructor/courses')}
    />
  );
}
