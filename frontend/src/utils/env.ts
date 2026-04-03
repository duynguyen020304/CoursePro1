// Environment Variable Validation Utility
// Validates VITE_* environment variables at runtime and shows toast error if invalid

import { z } from 'zod';
import toast from 'react-hot-toast';
import { envSchema, type EnvVars } from '../schemas/common/env.schema';

/**
 * Reads environment variables from import.meta.env
 * Only includes VITE_* prefixed variables
 */
function getEnvVars(): Record<string, string> {
  const env: Record<string, string> = {};
  
  for (const key of Object.keys(import.meta.env)) {
    if (key.startsWith('VITE_')) {
      const value = import.meta.env[key];
      // Only include string values (Vite serializes env vars)
      if (typeof value === 'string') {
        env[key] = value;
      }
    }
  }
  
  return env;
}

/**
 * Validation result type
 */
interface EnvValidationResult {
  success: boolean;
  env?: EnvVars;
  errors?: string[];
}

/**
 * Validates environment variables against the schema
 * @returns Validation result with either valid env or list of errors
 */
function validateEnvVars(): EnvValidationResult {
  const envVars = getEnvVars();
  const result = envSchema.safeParse(envVars);
  
  if (result.success) {
    return {
      success: true,
      env: result.data,
    };
  }
  
  // Collect all error messages
  const errors: string[] = [];
  for (const issue of result.error.issues) {
    errors.push(`${issue.path.join('.')}: ${issue.message}`);
  }
  
  return {
    success: false,
    errors,
  };
}

/**
 * Displays validation errors as a toast message
 * @param errors Array of error messages
 */
function showEnvValidationToast(errors: string[]): void {
  const message = errors.length === 1 
    ? `Environment Error: ${errors[0]}`
    : `Environment Errors:\n${errors.map(e => `• ${e}`).join('\n')}`;
  
  toast.error(message, {
    duration: Infinity, // Don't auto-dismiss
    style: {
      maxWidth: '500px',
      whiteSpace: 'pre-wrap',
    },
  });
}

/**
 * Validates environment variables and shows toast if invalid
 * Call this early in app startup (e.g., in main.tsx before rendering)
 * @returns The validated environment variables object, or undefined if validation failed
 */
export function validateEnvironment(): EnvVars | undefined {
  const result = validateEnvVars();
  
  if (!result.success && result.errors) {
    showEnvValidationToast(result.errors);
    return undefined;
  }
  
  return result.env;
}

/**
 * The validated environment variables object
 * Use this instead of directly accessing import.meta.env
 * 
 * @example
 * import { env } from '@/utils/env';
 * const apiUrl = env.VITE_API_URL;
 */
export const env: EnvVars = (() => {
  const result = validateEnvVars();
  
  if (!result.success && result.errors) {
    // During initial load, show toast (this runs synchronously)
    // The app will still render but may not function correctly
    if (typeof window !== 'undefined') {
      showEnvValidationToast(result.errors);
    }
  }
  
  // Return the parsed env or empty object (to avoid crashes)
  // The toast error has already been shown
  return result.env ?? {
    VITE_API_URL: '',
    VITE_GOOGLE_CLIENT_ID: '',
  };
})();

/**
 * Check if environment is valid (all required vars present)
 */
export function isEnvValid(): boolean {
  const result = validateEnvVars();
  return result.success;
}

export default env;
