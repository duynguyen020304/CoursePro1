// Role API module with response validation
import api from './api';
import { z } from 'zod';
import { safeValidateResponse } from '../utils/apiValidator';
import {
  roleListResponseSchema,
  roleResponseSchema,
  rolePermissionsResponseSchema,
  type RoleListResponse,
  type RoleResponse,
  type RolePermissionsResponse,
} from '../schemas';

/**
 * Role API methods with Zod response validation
 */
export const roleApi = {
  /**
   * List all roles
   */
  list: async (): Promise<RoleListResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get('/admin/roles');
      const result = safeValidateResponse(roleListResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[roleApi.list] API call failed:', error);
      throw error;
    }
  },

  /**
   * Get a single role by ID
   */
  get: async (id: string): Promise<RoleResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get(`/admin/roles/${id}`);
      const result = safeValidateResponse(roleResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[roleApi.get] API call failed:', error);
      throw error;
    }
  },

  /**
   * Create a new role
   */
  create: async (data: { name: string; slug?: string; description?: string }): Promise<RoleResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.post('/admin/roles', data);
      const result = safeValidateResponse(roleResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[roleApi.create] API call failed:', error);
      throw error;
    }
  },

  /**
   * Update a role
   */
  update: async (id: string, data: { name?: string; slug?: string; description?: string }): Promise<RoleResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.put(`/admin/roles/${id}`, data);
      const result = safeValidateResponse(roleResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[roleApi.update] API call failed:', error);
      throw error;
    }
  },

  /**
   * Delete a role
   */
  delete: async (id: string): Promise<{ success: boolean; message?: string } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.delete(`/admin/roles/${id}`);
      return response.data;
    } catch (error) {
      console.error('[roleApi.delete] API call failed:', error);
      throw error;
    }
  },

  /**
   * Get permissions for a role
   */
  getPermissions: async (id: string): Promise<RolePermissionsResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get(`/admin/roles/${id}/permissions`);
      const result = safeValidateResponse(rolePermissionsResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[roleApi.getPermissions] API call failed:', error);
      throw error;
    }
  },

  /**
   * Assign permissions to a role (additive)
   */
  assignPermissions: async (id: string, permissions: string[]): Promise<RolePermissionsResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.post(`/admin/roles/${id}/permissions`, { permissions });
      const result = safeValidateResponse(rolePermissionsResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[roleApi.assignPermissions] API call failed:', error);
      throw error;
    }
  },

  /**
   * Sync permissions for a role (replace all)
   */
  syncPermissions: async (id: string, permissions: string[]): Promise<RolePermissionsResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.put(`/admin/roles/${id}/permissions`, { permissions });
      const result = safeValidateResponse(rolePermissionsResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[roleApi.syncPermissions] API call failed:', error);
      throw error;
    }
  },

  /**
   * Remove a specific permission from a role
   */
  removePermission: async (id: string, permissionId: string): Promise<{ success: boolean; message?: string } | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.delete(`/admin/roles/${id}/permissions/${permissionId}`);
      return response.data;
    } catch (error) {
      console.error('[roleApi.removePermission] API call failed:', error);
      throw error;
    }
  },
};

export default roleApi;
