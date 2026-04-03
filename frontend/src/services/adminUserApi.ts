// Admin User API module with response validation
import api from './api';
import { z } from 'zod';
import { safeValidateResponse } from '../utils/apiValidator';
import {
  adminUserListResponseSchema,
  adminUserResponseSchema,
  createAdminUserResponseSchema,
  assignRoleResponseSchema,
  type AdminUserListResponse,
  type AdminUserResponse,
  type CreateAdminUserResponse,
  type AssignRoleResponse,
} from '../schemas';

/**
 * Admin User API methods with Zod response validation
 */
export const adminUserApi = {
  /**
   * List all users with optional pagination
   */
  list: async (params?: { page?: number; per_page?: number; search?: string; role?: string }): Promise<AdminUserListResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get('/admin/users', { params });
      const result = safeValidateResponse(adminUserListResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[adminUserApi.list] API call failed:', error);
      throw error;
    }
  },

  /**
   * Get a single user by ID
   */
  get: async (id: string): Promise<AdminUserResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get(`/admin/users/${id}`);
      const result = safeValidateResponse(adminUserResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[adminUserApi.get] API call failed:', error);
      throw error;
    }
  },

  /**
   * Create a new user
   */
  create: async (data: { email: string; first_name: string; last_name: string; password?: string; role_id?: string }): Promise<CreateAdminUserResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.post('/admin/users', data);
      const result = safeValidateResponse(createAdminUserResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[adminUserApi.create] API call failed:', error);
      throw error;
    }
  },

  /**
   * Update a user
   */
  update: async (id: string, data: { email?: string; first_name?: string; last_name?: string; password?: string }): Promise<AdminUserResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.put(`/admin/users/${id}`, data);
      const result = safeValidateResponse(adminUserResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[adminUserApi.update] API call failed:', error);
      throw error;
    }
  },

  /**
   * Delete a user
   */
  delete: async (id: string): Promise<{ success: boolean; message?: string } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.delete(`/admin/users/${id}`);
      return response.data;
    } catch (error) {
      console.error('[adminUserApi.delete] API call failed:', error);
      throw error;
    }
  },

  /**
   * Assign a role to a user
   */
  assignRole: async (id: string, role_id: string): Promise<AssignRoleResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.put(`/admin/users/${id}/role`, { role_id });
      const result = safeValidateResponse(assignRoleResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[adminUserApi.assignRole] API call failed:', error);
      throw error;
    }
  },
};

export default adminUserApi;
