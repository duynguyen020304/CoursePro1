import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Toaster } from 'react-hot-toast';
import toast from 'react-hot-toast';
import { instructorApi, categoryApi } from '../../services/api';
import { createCourseSchema, type CreateCourseFormData } from '../../schemas/course/createCourse.schema';

interface Category {
  id: number;
  name: string;
}

export default function CreateCourse() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState<Category[]>([]);
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    price: '',
    difficulty: 'All Level',
    language: 'Vietnamese',
    category_ids: [] as number[],
    objectives: [''],
    requirements: [''],
  });
  const [errors, setErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    fetchCategories();
  }, []);

  const fetchCategories = async () => {
    try {
      const response = await categoryApi.list();
      if (response.data.success) {
        setCategories(response.data.data);
      }
    } catch (err) {
      console.error('Failed to fetch categories:', err);
    }
  };

  const {
    register,
    handleSubmit,
    formState: { errors: zodErrors },
    setValue,
    watch,
  } = useForm<CreateCourseFormData>({
    resolver: zodResolver(createCourseSchema),
    mode: 'onBlur',
    defaultValues: {
      title: '',
      description: '',
      price: '',
      category_id: '' as unknown as undefined,
      thumbnail: undefined,
    },
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    
    // Sync with local formData for API payload
    setFormData((prev) => ({ ...prev, [name]: value }));
    
    // Clear error when user types
    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: '' }));
    }
  };

  const handleCategoryChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const options = e.target.options;
    const selected: number[] = [];
    for (let i = 0; i < options.length; i++) {
      if (options[i].selected) {
        selected.push(parseInt(options[i].value, 10));
      }
    }
    setFormData((prev) => ({ ...prev, category_ids: selected }));
  };

  const handleArrayChange = (field: 'objectives' | 'requirements', index: number, value: string) => {
    const newArray = [...formData[field]];
    newArray[index] = value;
    setFormData((prev) => ({ ...prev, [field]: newArray }));
  };

  const addArrayItem = (field: 'objectives' | 'requirements') => {
    setFormData((prev) => ({ ...prev, [field]: [...prev[field], ''] }));
  };

  const removeArrayItem = (field: 'objectives' | 'requirements', index: number) => {
    if (formData[field].length > 1) {
      const newArray = formData[field].filter((_, i) => i !== index);
      setFormData((prev) => ({ ...prev, [field]: newArray }));
    }
  };

  const onSubmit = async (data: CreateCourseFormData) => {
    setErrors({});

    try {
      setLoading(true);
      const payload = {
        title: formData.title,
        description: formData.description,
        price: parseFloat(formData.price),
        difficulty: formData.difficulty,
        language: formData.language,
        category_ids: formData.category_ids,
        objectives: formData.objectives.filter((o) => o.trim()),
        requirements: formData.requirements.filter((r) => r.trim()),
      };

      const response = await instructorApi.createCourse(payload);
      if (response.data.success) {
        navigate('/instructor/courses');
      }
    } catch (err: unknown) {
      const errorObj = err as { response?: { data?: { message?: string } } };
      const errorMessage = errorObj.response?.data?.message || 'Failed to create course';
      setErrors({ submit: errorMessage });
      toast.error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const onInvalid = (submitError: unknown) => {
    // zodResolver will populate formState.errors via react-hook-form
    // Additional safety net for any Zod errors not caught by resolver
    if (submitError && typeof submitError === 'object' && !Array.isArray(submitError)) {
      const errorValues = Object.values(submitError as Record<string, unknown>);
      if (errorValues.length > 0) {
        const firstError = errorValues[0];
        if (typeof firstError === 'object' && firstError !== null && 'message' in firstError) {
          toast.error((firstError as { message: string }).message);
        } else {
          toast.error('Please fix the validation errors before submitting.');
        }
      }
    }
  };

  return (
    <div className="max-w-4xl mx-auto">
      <Toaster position="top-right" />
      <h1 className="text-2xl font-bold text-gray-800 mb-6">Create New Course</h1>

      <form onSubmit={handleSubmit(onSubmit, onInvalid)} className="space-y-6">
        {errors.submit && (
          <div className="bg-red-50 text-red-600 p-4 rounded-lg">{errors.submit}</div>
        )}

        {/* Basic Info */}
        <div className="bg-white rounded-lg shadow-sm p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>

          <div className="space-y-4">
            <div>
              <label htmlFor="title" className="block text-sm font-medium text-gray-700 mb-1">
                Course Title *
              </label>
              <input
                id="title"
                type="text"
                {...register('title')}
                name="title"
                value={formData.title}
                onChange={handleChange}
                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 ${
                  zodErrors.title || errors.title ? 'border-red-500' : 'border-gray-300'
                }`}
                placeholder="e.g., Complete Web Development Bootcamp"
              />
              {zodErrors.title && (
                <p className="mt-1 text-sm text-red-500">{zodErrors.title.message}</p>
              )}
              {errors.title && !zodErrors.title && (
                <p className="mt-1 text-sm text-red-500">{errors.title}</p>
              )}
            </div>

            <div>
              <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-1">
                Description
              </label>
              <textarea
                id="description"
                {...register('description')}
                name="description"
                value={formData.description}
                onChange={handleChange}
                rows={4}
                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 ${
                  zodErrors.description ? 'border-red-500' : 'border-gray-300'
                }`}
                placeholder="Describe what students will learn in this course..."
              />
              {zodErrors.description && (
                <p className="mt-1 text-sm text-red-500">{zodErrors.description.message}</p>
              )}
            </div>

            <div className="grid grid-cols-3 gap-4">
              <div>
                <label htmlFor="price" className="block text-sm font-medium text-gray-700 mb-1">
                  Price ($) *
                </label>
                <input
                  id="price"
                  type="number"
                  {...register('price')}
                  name="price"
                  value={formData.price}
                  onChange={handleChange}
                  min="0"
                  step="0.01"
                  className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 ${
                    zodErrors.price || errors.price ? 'border-red-500' : 'border-gray-300'
                  }`}
                  placeholder="99.99"
                />
                {zodErrors.price && (
                  <p className="mt-1 text-sm text-red-500">{zodErrors.price.message}</p>
                )}
                {errors.price && !zodErrors.price && (
                  <p className="mt-1 text-sm text-red-500">{errors.price}</p>
                )}
              </div>

              <div>
                <label htmlFor="difficulty" className="block text-sm font-medium text-gray-700 mb-1">
                  Difficulty
                </label>
                <select
                  id="difficulty"
                  name="difficulty"
                  value={formData.difficulty}
                  onChange={handleChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="All Level">All Levels</option>
                  <option value="Beginner">Beginner</option>
                  <option value="Intermediate">Intermediate</option>
                  <option value="Expert">Expert</option>
                </select>
              </div>

              <div>
                <label htmlFor="language" className="block text-sm font-medium text-gray-700 mb-1">
                  Language
                </label>
                <select
                  id="language"
                  name="language"
                  value={formData.language}
                  onChange={handleChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="Vietnamese">Vietnamese</option>
                  <option value="English">English</option>
                  <option value="Japanese">Japanese</option>
                  <option value="Korean">Korean</option>
                  <option value="Chinese">Chinese</option>
                </select>
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Categories (hold Ctrl/Cmd to select multiple)
              </label>
              <select
                multiple
                value={formData.category_ids}
                onChange={handleCategoryChange}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 h-32"
              >
                {categories.map((cat) => (
                  <option key={cat.id} value={cat.id}>
                    {cat.name}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>

        {/* Objectives */}
        <div className="bg-white rounded-lg shadow-sm p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">
            What will students learn?
          </h2>
          {formData.objectives.map((objective, index) => (
            <div key={index} className="flex gap-2 mb-2">
              <input
                type="text"
                value={objective}
                onChange={(e) => handleArrayChange('objectives', index, e.target.value)}
                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="e.g., Build real-world web applications"
              />
              <button
                type="button"
                onClick={() => removeArrayItem('objectives', index)}
                className="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg"
              >
                ✕
              </button>
            </div>
          ))}
          <button
            type="button"
            onClick={() => addArrayItem('objectives')}
            className="mt-2 text-indigo-600 hover:text-indigo-700 text-sm font-medium"
          >
            + Add Learning Objective
          </button>
        </div>

        {/* Requirements */}
        <div className="bg-white rounded-lg shadow-sm p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">
            Requirements
          </h2>
          {formData.requirements.map((requirement, index) => (
            <div key={index} className="flex gap-2 mb-2">
              <input
                type="text"
                value={requirement}
                onChange={(e) => handleArrayChange('requirements', index, e.target.value)}
                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="e.g., Basic understanding of HTML and CSS"
              />
              <button
                type="button"
                onClick={() => removeArrayItem('requirements', index)}
                className="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg"
              >
                ✕
              </button>
            </div>
          ))}
          <button
            type="button"
            onClick={() => addArrayItem('requirements')}
            className="mt-2 text-indigo-600 hover:text-indigo-700 text-sm font-medium"
          >
            + Add Requirement
          </button>
        </div>

        {/* Submit */}
        <div className="flex justify-end gap-4">
          <button
            type="button"
            onClick={() => navigate('/instructor/courses')}
            className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={loading}
            className="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
          >
            {loading ? 'Creating...' : 'Create Course'}
          </button>
        </div>
      </form>
    </div>
  );
}
