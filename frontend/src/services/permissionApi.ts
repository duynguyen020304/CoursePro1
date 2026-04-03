// Permission API module with response validation
import api from './api';
import { z } from 'zod';
import { safeValidateResponse } from '../utils/apiValidator';
import {
  permissionListResponseSchema,
  type PermissionListResponse,
} from '../schemas';

/**
 * Permission API methods with Zod response validation
 */
export const permissionApi = {
  /**
   * List all permissions
   */
  list: async (): Promise<PermissionListResponse | { success: false; error: string; _raw: unknown }> => {
    try {
      const response = await api.get('/admin/permissions');
      const result = safeValidateResponse(permissionListResponseSchema, response.data);
      
      if (!result.success) {
        return { success: false, error: result.error, _raw: response.data };
      }
      
      return result.data;
    } catch (error) {
      console.error('[permissionApi.list] API call failed:', error);
      throw error;
    }
  },
};

export default permissionApi;
