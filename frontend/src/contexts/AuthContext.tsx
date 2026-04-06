// Auth Context with TypeScript
// Provides authentication state and methods for the entire application

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from 'react';
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
  refreshAuth: () => Promise<{ success: boolean; user?: User | UserProfile }>;
  fetchUserPermissions: () => Promise<void>;
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
  const [user, setUser] = useState<User | UserProfile | null>(null);
  const [loading, setLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [userPermissions, setUserPermissions] = useState<string[]>([]);

  /**
   * Clears all authentication state
   */
  const clearAuthState = useCallback(() => {
    setUser(null);
    setIsAuthenticated(false);
    setUserPermissions([]);
  }, []);

  /**
   * Fetches user permissions from the API
   */
  const fetchUserPermissions = useCallback(async (): Promise<void> => {
    try {
      const response = await userApi.profile();
      // userApi.profile() returns { data: UserProfile } directly (flat structure)
      const userData = response.data as { role?: { permissions?: Array<{ name: string }> } };

      if (userData?.role?.permissions) {
        setUserPermissions(userData.role.permissions.map((permission) => permission.name));
      } else {
        setUserPermissions([]);
      }
    } catch (error) {
      console.error('Failed to fetch user permissions:', error);
      setUserPermissions([]);
    }
  }, []);

  /**
   * Refreshes authentication state by fetching current user
   */
  const refreshAuth = useCallback(async (): Promise<{ success: boolean; user?: User | UserProfile }> => {
    try {
      const response = await userApi.current();
      // userApi.current() returns { data: { success, message, data: userProfile } }
      // The validated() function returns the full parsed response in response.data
      // We need to extract the inner `data` field which is the userProfile
      const outerData = response.data as { data?: User | UserProfile };
      const userData = outerData?.data ?? outerData;

      if (!userData || !('user_id' in userData)) {
        clearAuthState();
        return { success: false };
      }

      setUser(userData);
      setIsAuthenticated(true);

      // Extract permissions from user data if role.permissions is present
      const userProfile = userData as UserProfile;
      if ('role_id' in userData && userData.role_id) {
        if (userProfile.role?.permissions) {
          setUserPermissions(userProfile.role.permissions.map((p) => p.name));
        } else {
          await fetchUserPermissions();
        }
      } else {
        setUserPermissions([]);
      }

      return { success: true, user: userData };
    } catch (error) {
      const errorResponse = error as { response?: { status?: number } };
      if (errorResponse.response?.status === 401) {
        clearAuthState();
        return { success: false };
      }

      throw error;
    }
  }, [clearAuthState, fetchUserPermissions]);

  // Initial auth check on mount
  useEffect(() => {
    refreshAuth().finally(() => {
      setLoading(false);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  /**
   * Logs in a user with email and password
   */
  const login = useCallback(
    async (email: string, password: string): Promise<{ success: boolean; user?: User | UserProfile; message?: string }> => {
      // authApi.login() returns { data: User } with user directly in data (unwrapped from { user } layer)
      const response = await authApi.login({ email, password });
      const userData = response.data as User | undefined;

      if (userData && 'user_id' in userData) {
        setUser(userData);
        setIsAuthenticated(true);

        if ('role_id' in userData) {
          await fetchUserPermissions();
        }

        return { success: true, user: userData };
      }

      return { success: false, message: 'Login failed' };
    },
    [fetchUserPermissions]
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
      // authApi.signup() returns { data: User } with user directly in data (unwrapped from { user } layer)
      const response = await authApi.signup(formData);
      const userData = response.data as User | undefined;

      if (userData && 'user_id' in userData) {
        setUser(userData);
        setIsAuthenticated(true);

        if ('role_id' in userData) {
          await fetchUserPermissions();
        }

        return { success: true, user: userData };
      }

      return { success: false, message: 'Signup failed' };
    },
    [fetchUserPermissions]
  );

  /**
   * Logs out the current user
   */
  const logout = useCallback(async (): Promise<void> => {
    try {
      await authApi.logout();
    } catch (error) {
      const errorResponse = error as { response?: { status?: number } };
      if (errorResponse.response?.status !== 401) {
        console.error('Failed to logout:', error);
      }
    } finally {
      clearAuthState();
    }
  }, [clearAuthState]);

  /**
   * Updates user profile data
   */
  const updateUser = useCallback(
    async (userData: Record<string, unknown>): Promise<{ success: boolean; user?: User | UserProfile; message?: string }> => {
      try {
        const { data: response }: { data: UpdateProfileResponse } = await userApi.updateProfile(userData);

        if (response.success && response.data) {
          setUser(response.data);
          return { success: true, user: response.data };
        }

        return { success: false, message: response.message || 'Update failed' };
      } catch (error) {
        const errorResponse = error as { response?: { data?: { message?: string } } };
        return { success: false, message: errorResponse.response?.data?.message || 'Update failed' };
      }
    },
    []
  );

  /**
   * Checks if user has a specific role
   */
  const hasRole = useCallback(
    (roleName: string): boolean => {
      if (!user) return false;
      return 'role_id' in user && user.role_id === roleName;
    },
    [user]
  );

  /**
   * Checks if user has a specific permission
   */
  const hasPermission = useCallback(
    (permissionName: string): boolean => {
      return userPermissions.includes(permissionName);
    },
    [userPermissions]
  );

  /**
   * Checks if user has any of the specified permissions
   */
  const hasAnyPermission = useCallback(
    (permissions: string[]): boolean => {
      return permissions.some((permission) => userPermissions.includes(permission));
    },
    [userPermissions]
  );

  /**
   * Checks if user has all of the specified permissions
   */
  const hasAllPermissions = useCallback(
    (permissions: string[]): boolean => {
      return permissions.every((permission) => userPermissions.includes(permission));
    },
    [userPermissions]
  );

  const value: AuthContextValue = {
    user,
    loading,
    isAuthenticated,
    userPermissions,
    login,
    signup,
    logout,
    updateUser,
    hasRole,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
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
