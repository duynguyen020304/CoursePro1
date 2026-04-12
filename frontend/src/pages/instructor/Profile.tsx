import { useEffect, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useAuth } from '../../contexts/AuthContext';
import { instructorApi } from '../../services/api';

interface InstructorProfile {
  biography?: string | null;
}

export default function InstructorProfile() {
  const { user } = useAuth();
  const queryClient = useQueryClient();
  const [formData, setFormData] = useState({
    biography: '',
  });
  const [message, setMessage] = useState({ type: '', text: '' });
  const { data: profile } = useQuery<InstructorProfile | null>({
    queryKey: ['instructor', 'profile'],
    queryFn: async () => {
      const response = await instructorApi.getProfile();
      if (response.data.success && response.data.data) {
        return response.data.data as InstructorProfile;
      }
      return null;
    },
  });

  useEffect(() => {
    setFormData({
      biography: profile?.biography || '',
    });
  }, [profile]);

  const updateProfileMutation = useMutation({
    mutationFn: async (biography: string) => instructorApi.updateProfile(biography),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['instructor', 'profile'] });
      setMessage({ type: 'success', text: 'Profile updated successfully!' });
    },
    onError: (err) => {
      console.error('Failed to update profile:', err);
      setMessage({ type: 'error', text: 'Failed to update profile' });
    },
  });

  const handleChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      setMessage({ type: '', text: '' });
      await updateProfileMutation.mutateAsync(formData.biography);
    } catch {
      // handled by mutation callbacks
    }
  };

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <h1 className="text-2xl font-bold text-gray-800">Instructor Profile</h1>

      {/* User Info */}
      <div className="bg-white rounded-lg shadow-sm p-6">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Account Information</h2>
        <div className="flex items-center gap-4 mb-4">
          <div className="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center text-2xl text-indigo-600">
            {user?.first_name?.[0]}{user?.last_name?.[0]}
          </div>
          <div>
            <p className="font-medium text-gray-800">
              {user?.first_name} {user?.last_name}
            </p>
            <p className="text-sm text-gray-500">{user?.email}</p>
          </div>
        </div>
      </div>

      {/* Instructor Profile Form */}
      <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow-sm p-6">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Instructor Details</h2>

        {message.text && (
          <div
            className={`p-4 rounded-lg mb-4 ${
              message.type === 'success' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'
            }`}
          >
            {message.text}
          </div>
        )}

        <div className="space-y-4">
          <div>
            <label htmlFor="biography" className="block text-sm font-medium text-gray-700 mb-1">
              Biography
            </label>
            <textarea
              id="biography"
              name="biography"
              value={formData.biography}
              onChange={handleChange}
              rows={6}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="Tell students about yourself, your experience, and what they'll learn from your courses..."
            />
          </div>
        </div>

        <div className="mt-6 flex justify-end">
          <button
            type="submit"
            disabled={updateProfileMutation.isPending}
            className="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
          >
            {updateProfileMutation.isPending ? 'Saving...' : 'Save Profile'}
          </button>
        </div>
      </form>
    </div>
  );
}
