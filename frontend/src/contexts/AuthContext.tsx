// Auth Context with TypeScript
// Provides authentication state and methods for the entire application

import {
  createContext,
  useCallback,
  useContext,
  type ReactNode,
} from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { authApi, userApi } from '../services/api';
import type { UserProfile, UpdateProfileResponse } from '../schemas/user/apiResponses.schema';
import type { User } from '../schemas/auth/apiResponses.schema';

/**
 * Auth context state shape
 */
interface AuthState {
  user: User | UserProfile | null;
  loading: boolean;
  isAuthenticated: boolean;
  userPermissions: string[];
}

/**
 * Auth context value shape with all methods
 */
interface AuthContextValue extends AuthState {
  login: (email: string, password: string) => Promise<{ success: boolean; user?: User | UserProfile; message?: string }>;
  signup: (formData: {
    first_name: string;
    last_name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) => Promise<{ success: boolean; user?: User | UserProfile; message?: string }>;
  logout: () => Promise<void>;
  updateUser: (userData: Record<string, unknown>) => Promise<{ success: boolean; user?: User | UserProfile; message?: string }>;
  hasRole: (roleName: string) => boolean;
  hasPermission: (permissionName: string) => boolean;
  hasAnyPermission: (permissions: string[]) => boolean;
  hasAllPermissions: (permissions: string[]) => boolean;
  isEmailVerified: () => boolean;
  refreshAuth: () => Promise<{ success: boolean; user?: User | UserProfile }>;
  fetchUserPermissions: () => Promise<void>;
}

interface AuthSnapshot {
  user: User | UserProfile | null;
  userPermissions: string[];
  isAuthenticated: boolean;
}

const AUTH_QUERY_KEY = ['auth', 'current'] as const;
const CART_QUERY_KEY = ['cart'] as const;

function extractPermissionNames(userData: User | UserProfile | null | undefined): string[] {
  if (!userData) {
    return [];
  }

  const permissions = ('role' in userData ? userData.role?.permissions : undefined) ?? [];
  return permissions.map((permission) => permission.name);
}

function guestSnapshot(): AuthSnapshot {
  return {
    user: null,
    userPermissions: [],
    isAuthenticated: false,
  };
}

async function buildAuthSnapshotFromUser(userData: User | UserProfile): Promise<AuthSnapshot> {
  let resolvedUser: User | UserProfile = userData;
  let userPermissions = extractPermissionNames(userData);

  if ('role_id' in userData && userData.role_id && userPermissions.length === 0) {
    try {
      const profileResponse = await userApi.profile();
      const profileData = profileResponse.data.data as UserProfile;

      if (profileData && 'user_id' in profileData) {
        resolvedUser = profileData;
        userPermissions = extractPermissionNames(profileData);
      }
    } catch (error) {
      console.error('Failed to fetch user permissions:', error);
    }
  }

  return {
    user: resolvedUser,
    userPermissions,
    isAuthenticated: true,
  };
}

async function fetchAuthSnapshot(): Promise<AuthSnapshot> {
  try {
    const response = await userApi.current();
    const outerData = response.data as { data?: User | UserProfile };
    const userData = outerData?.data ?? outerData;

    if (!userData || !('user_id' in userData)) {
      return guestSnapshot();
    }

    return buildAuthSnapshotFromUser(userData);
  } catch (error) {
    const errorResponse = error as { response?: { status?: number } };
    if (errorResponse.response?.status === 401) {
      return guestSnapshot();
    }

    throw error;
  }
}

/**
 * Auth context with nullable initial value
 */
const AuthContext = createContext<AuthContextValue | null>(null);

/**
 * Props for AuthProvider
 */
interface AuthProviderProps {
  children: ReactNode;
}

/**
 * Auth Provider component
 * Manages authentication state including user, loading, and permissions
 */
export function AuthProvider({ children }: AuthProviderProps) {
  const queryClient = useQueryClient();
  const authQuery = useQuery({
    queryKey: AUTH_QUERY_KEY,
    queryFn: fetchAuthSnapshot,
    retry: false,
    refetchOnWindowFocus: false,
  });
  const authState = authQuery.data ?? guestSnapshot();
  const currentUser = authState.user;

  /**
   * Clears all authentication state
   */
  const clearAuthState = useCallback(() => {
    queryClient.setQueryData(AUTH_QUERY_KEY, guestSnapshot());
    queryClient.removeQueries({ queryKey: CART_QUERY_KEY });
  }, [queryClient]);

  /**
   * Refreshes authentication state by fetching current user
   */
  const refreshAuth = useCallback(async (): Promise<{ success: boolean; user?: User | UserProfile }> => {
    const snapshot = await queryClient.fetchQuery({
      queryKey: AUTH_QUERY_KEY,
      queryFn: fetchAuthSnapshot,
    });

    if (!snapshot.isAuthenticated || !snapshot.user) {
      return { success: false };
    }

    return { success: true, user: snapshot.user };
  }, [queryClient]);

  const fetchUserPermissions = useCallback(async (): Promise<void> => {
    await refreshAuth();
  }, [refreshAuth]);

  const loginMutation = useMutation({
    mutationFn: async (credentials: { email: string; password: string }) => {
      const response = await authApi.login(credentials);
      const userData = response.data as User | undefined;

      if (!userData || !('user_id' in userData)) {
        throw new Error('Login failed');
      }

      return buildAuthSnapshotFromUser(userData);
    },
    onSuccess: (snapshot) => {
      queryClient.setQueryData(AUTH_QUERY_KEY, snapshot);
    },
  });

  const signupMutation = useMutation({
    mutationFn: async (formData: {
      first_name: string;
      last_name: string;
      email: string;
      password: string;
      password_confirmation: string;
    }) => {
      const response = await authApi.signup(formData);
      const userData = response.data as User | undefined;

      if (!userData || !('user_id' in userData)) {
        throw new Error('Signup failed');
      }

      return buildAuthSnapshotFromUser(userData);
    },
    onSuccess: (snapshot) => {
      queryClient.setQueryData(AUTH_QUERY_KEY, snapshot);
    },
  });

  const logoutMutation = useMutation({
    mutationFn: async () => {
      try {
        await authApi.logout();
      } catch (error) {
        const errorResponse = error as { response?: { status?: number } };
        if (errorResponse.response?.status !== 401) {
          throw error;
        }
      }
    },
    onSettled: () => {
      clearAuthState();
    },
  });

  const updateUserMutation = useMutation({
    mutationFn: async (userData: Record<string, unknown>) => {
      const { data: response }: { data: UpdateProfileResponse } = await userApi.updateProfile(userData);

      if (!response.success || !response.data) {
        throw new Error(response.message || 'Update failed');
      }

      return response.data;
    },
    onSuccess: (updatedUser) => {
      queryClient.setQueryData<AuthSnapshot>(AUTH_QUERY_KEY, (current) => {
        const currentSnapshot = current ?? guestSnapshot();
        const nextPermissions = extractPermissionNames(updatedUser);

        return {
          user: updatedUser,
          userPermissions: nextPermissions.length > 0 ? nextPermissions : currentSnapshot.userPermissions,
          isAuthenticated: true,
        };
      });
    },
  });

  /**
   * Logs in a user with email and password
   */
  const login = useCallback(
    async (email: string, password: string): Promise<{ success: boolean; user?: User | UserProfile; message?: string }> => {
      try {
        const snapshot = await loginMutation.mutateAsync({ email, password });
        return snapshot.user
          ? { success: true, user: snapshot.user }
          : { success: false, message: 'Login failed' };
      } catch (error) {
        const errorResponse = error as { response?: { data?: { message?: string } }; message?: string };
        return {
          success: false,
          message: errorResponse.response?.data?.message || errorResponse.message || 'Login failed',
        };
      }
    },
    [loginMutation]
  );

  /**
   * Registers a new user
   */
  const signup = useCallback(
    async (formData: {
      first_name: string;
      last_name: string;
      email: string;
      password: string;
      password_confirmation: string;
    }): Promise<{ success: boolean; user?: User | UserProfile; message?: string }> => {
      try {
        const snapshot = await signupMutation.mutateAsync(formData);
        return snapshot.user
          ? { success: true, user: snapshot.user }
          : { success: false, message: 'Signup failed' };
      } catch (error) {
        const errorResponse = error as { response?: { data?: { message?: string } }; message?: string };
        return {
          success: false,
          message: errorResponse.response?.data?.message || errorResponse.message || 'Signup failed',
        };
      }
    },
    [signupMutation]
  );

  /**
   * Logs out the current user
   */
  const logout = useCallback(async (): Promise<void> => {
    try {
      await logoutMutation.mutateAsync();
    } catch (error) {
      console.error('Failed to logout:', error);
    }
  }, [logoutMutation]);

  /**
   * Updates user profile data
   */
  const updateUser = useCallback(
    async (userData: Record<string, unknown>): Promise<{ success: boolean; user?: User | UserProfile; message?: string }> => {
      try {
        const updatedUser = await updateUserMutation.mutateAsync(userData);
        return { success: true, user: updatedUser };
      } catch (error) {
        const errorResponse = error as { response?: { data?: { message?: string } }; message?: string };
        return {
          success: false,
          message: errorResponse.response?.data?.message || errorResponse.message || 'Update failed',
        };
      }
    },
    [updateUserMutation]
  );

  /**
   * Checks if user has a specific role
   * Compares roleName against user.role_id (which stores the role name string like 'admin')
   * Also checks nested user.role.role_id as fallback
   */
  const hasRole = useCallback(
    (roleName: string): boolean => {
      if (!currentUser) return false;
      // Check flat role_id field first (e.g., user.role_id === 'admin')
      if ('role_id' in currentUser && currentUser.role_id === roleName) return true;
      // Fallback to nested role fields
      const userWithRole = currentUser as { role?: { role_id?: string; role_name?: string } };
      if (userWithRole.role?.role_id === roleName) return true;
      if (userWithRole.role?.role_name?.toLowerCase() === roleName.toLowerCase()) return true;
      return false;
    },
    [currentUser]
  );

  /**
   * Checks if user has a specific permission
   */
  const hasPermission = useCallback(
    (permissionName: string): boolean => {
      return authState.userPermissions.includes(permissionName);
    },
    [authState.userPermissions]
  );

  /**
   * Checks if user has any of the specified permissions
   */
  const hasAnyPermission = useCallback(
    (permissions: string[]): boolean => {
      return permissions.some((permission) => authState.userPermissions.includes(permission));
    },
    [authState.userPermissions]
  );

  /**
   * Checks if user has all of the specified permissions
   */
  const hasAllPermissions = useCallback(
    (permissions: string[]): boolean => {
      return permissions.every((permission) => authState.userPermissions.includes(permission));
    },
    [authState.userPermissions]
  );

  const isEmailVerified = useCallback((): boolean => {
    if (!currentUser) {
      return false;
    }

    if ('is_verified' in currentUser && typeof currentUser.is_verified === 'boolean') {
      return currentUser.is_verified;
    }

    if ('email_verified_at' in currentUser) {
      return Boolean(currentUser.email_verified_at);
    }

    return false;
  }, [currentUser]);

  const value: AuthContextValue = {
    user: currentUser,
    loading: authQuery.isPending,
    isAuthenticated: authState.isAuthenticated,
    userPermissions: authState.userPermissions,
    login,
    signup,
    logout,
    updateUser,
    hasRole,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    isEmailVerified,
    refreshAuth,
    fetchUserPermissions,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

/**
 * Hook to access auth context
 * @throws Error if used outside of AuthProvider
 */
export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
