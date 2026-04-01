import { createContext, useContext, useState, useEffect } from 'react';
import { authApi, userApi } from '../services/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [userPermissions, setUserPermissions] = useState([]);

  useEffect(() => {
    // Check if user is logged in on mount
    const token = localStorage.getItem('token');
    const storedUser = localStorage.getItem('user');

    if (token && storedUser) {
      try {
        const parsedUser = JSON.parse(storedUser);
        setUser(parsedUser);
        setIsAuthenticated(true);
        // Fetch permissions if user has role
        if (parsedUser.role_id) {
          fetchUserPermissions();
        }
      } catch (e) {
        console.error('Failed to parse stored user:', e);
        localStorage.removeItem('user');
      }
    }
    setLoading(false);
  }, []);

  const fetchUserPermissions = async () => {
    try {
      const response = await userApi.profile();
      const userData = response.data.data;
      if (userData.role && userData.role.permissions) {
        setUserPermissions(userData.role.permissions.map(p => p.name));
      }
    } catch (error) {
      console.error('Failed to fetch user permissions:', error);
    }
  };

  const login = async (email, password) => {
    const response = await authApi.login({ email, password });
    const { data } = response;

    if (data.success) {
      const { token, user: userData } = data.data;
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(userData));
      setUser(userData);
      setIsAuthenticated(true);
      // Fetch permissions after login
      if (userData.role_id) {
        fetchUserPermissions();
      }
      return { success: true, user: userData };
    }

    return { success: false, message: data.message || 'Login failed' };
  };

  const signup = async (formData) => {
    const response = await authApi.signup(formData);
    const { data } = response;

    if (data.success) {
      const { token, user: userData } = data;
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(userData));
      setUser(userData);
      setIsAuthenticated(true);
      return { success: true, user: userData };
    }

    return { success: false, message: data.message || 'Signup failed' };
  };

  const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setUser(null);
    setIsAuthenticated(false);
    setUserPermissions([]);
  };

  const updateUser = async (userData) => {
    try {
      const response = await userApi.updateProfile(userData);
      const { data } = response;

      if (data.success) {
        localStorage.setItem('user', JSON.stringify(data.data));
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
    return permissions.some(permission => userPermissions.includes(permission));
  };

  const hasAllPermissions = (permissions) => {
    return permissions.every(permission => userPermissions.includes(permission));
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
