/* eslint-disable react-refresh/only-export-components */
import { createContext, useContext, useEffect, useState } from 'react';
import { authApi, userApi } from '../services/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [userPermissions, setUserPermissions] = useState([]);

  const clearAuthState = () => {
    setUser(null);
    setIsAuthenticated(false);
    setUserPermissions([]);
  };

  const fetchUserPermissions = async () => {
    try {
      const response = await userApi.profile();
      const userData = response.data.data;

      if (userData.role?.permissions) {
        setUserPermissions(userData.role.permissions.map((permission) => permission.name));
      } else {
        setUserPermissions([]);
      }
    } catch (error) {
      console.error('Failed to fetch user permissions:', error);
      setUserPermissions([]);
    }
  };

  const refreshAuth = async () => {
    try {
      const response = await userApi.current();
      const userData = response.data?.data?.user;

      if (!userData) {
        clearAuthState();
        return { success: false };
      }

      setUser(userData);
      setIsAuthenticated(true);

      if (userData.role_id) {
        await fetchUserPermissions();
      } else {
        setUserPermissions([]);
      }

      return { success: true, user: userData };
    } catch (error) {
      if (error.response?.status === 401) {
        clearAuthState();
        return { success: false };
      }

      throw error;
    }
  };

  useEffect(() => {
    refreshAuth().finally(() => {
      setLoading(false);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const login = async (email, password) => {
    const response = await authApi.login({ email, password });
    const { data } = response;

    if (data.success) {
      const userData = data.data.user;
      setUser(userData);
      setIsAuthenticated(true);

      if (userData.role_id) {
        await fetchUserPermissions();
      }

      return { success: true, user: userData };
    }

    return { success: false, message: data.message || 'Login failed' };
  };

  const signup = async (formData) => {
    const response = await authApi.signup(formData);
    const { data } = response;

    if (data.success) {
      const userData = data.data.user;
      setUser(userData);
      setIsAuthenticated(true);

      if (userData.role_id) {
        await fetchUserPermissions();
      }

      return { success: true, user: userData };
    }

    return { success: false, message: data.message || 'Signup failed' };
  };

  const logout = async () => {
    try {
      await authApi.logout();
    } catch (error) {
      if (error.response?.status !== 401) {
        console.error('Failed to logout:', error);
      }
    } finally {
      clearAuthState();
    }
  };

  const updateUser = async (userData) => {
    try {
      const response = await userApi.updateProfile(userData);
      const { data } = response;

      if (data.success) {
        setUser(data.data);
        return { success: true, user: data.data };
      }

      return { success: false, message: data.message || 'Update failed' };
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Update failed' };
    }
  };

  const hasRole = (roleName) => {
    return user && user.role_id === roleName;
  };

  const hasPermission = (permissionName) => {
    return userPermissions.includes(permissionName);
  };

  const hasAnyPermission = (permissions) => {
    return permissions.some((permission) => userPermissions.includes(permission));
  };

  const hasAllPermissions = (permissions) => {
    return permissions.every((permission) => userPermissions.includes(permission));
  };

  const value = {
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

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
