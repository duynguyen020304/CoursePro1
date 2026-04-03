// API Validation Utility
// Provides reusable validation functions for API responses using Zod

import { z } from 'zod';

/**
 * Result type for safe validation - returns success/data/error tuple
 */
export interface SafeValidateResult<T> {
  success: true;
  data: T;
  error?: undefined;
}

export interface SafeValidateError {
  success: false;
  data?: undefined;
  error: string;
}

/**
 * Validates data against a Zod schema and returns typed data.
 * Throws a ZodError if validation fails.
 * 
 * @param schema - The Zod schema to validate against
 * @param data - The data to validate
 * @returns The validated data with proper typing
 * @throws ZodError if validation fails
 */
export function validateResponse<S extends z.ZodTypeAny>(
  schema: S,
  data: unknown
): z.infer<S> {
  return schema.parse(data);
}

/**
 * Safely validates data against a Zod schema without throwing.
 * Returns a result object indicating success or failure.
 * 
 * @param schema - The Zod schema to validate against
 * @param data - The data to validate
 * @returns Object with success flag, typed data on success, or error message on failure
 */
export function safeValidateResponse<S extends z.ZodTypeAny>(
  schema: S,
  data: unknown
): SafeValidateResult<z.infer<S>> | SafeValidateError {
  const result = schema.safeParse(data);
  
  if (result.success) {
    return {
      success: true,
      data: result.data,
    };
  }
  
  // Format error using Zod's built-in prettifyError for Zod v4
  const errorMessage = z.prettifyError(result.error);
  
  return {
    success: false,
    error: errorMessage,
  };
}

/**
 * Maps API method names to their response schemas
 */
export type SchemaMap = Record<string, z.ZodTypeAny>;

/**
 * Wrapped API method that validates response against a schema
 */
export interface ValidatedApiMethod<T = unknown> {
  (): Promise<T>;
}

/**
 * Wrapped API object with validated methods
 */
export type ValidatedApi<T extends SchemaMap> = {
  [K in keyof T]: ValidatedApiMethod<z.infer<T[K]>>;
};

/**
 * Creates a validated API wrapper that automatically validates
 * responses from API methods against their corresponding schemas.
 * 
 * @param api - The original API object with methods
 * @param schemaMap - A map of method names to their response schemas
 * @returns A new API object where each method validates its response
 * 
 * @example
 * ```typescript
 * const validatedAuthApi = createValidatedApi(authApi, {
 *   login: loginResponseSchema,
 *   signup: signupResponseSchema,
 *   logout: z.object({ success: z.literal(true) }),
 * });
 * 
 * // Now login() returns typed data and logs errors without throwing
 * const result = await validatedAuthApi.login();
 * if (result.success) {
 *   console.log(result.data.user);
 * } else {
 *   console.error(result.error);
 * }
 * ```
 */
export function createValidatedApi<
  Api extends object,
  S extends SchemaMap
>(
  api: Api,
  schemaMap: S
): ValidatedApi<S> {
  const validatedApi = {} as ValidatedApi<S>;
  
  for (const methodName of Object.keys(schemaMap) as (keyof S)[]) {
    const originalMethod = (api as any)[methodName];
    const schema = schemaMap[methodName];
    
    if (typeof originalMethod === 'function') {
      (validatedApi as any)[methodName] = async () => {
        try {
          const response = await (originalMethod as () => Promise<unknown>)();
          const result = safeValidateResponse(schema, response);
          
          if (!result.success) {
            // Return a structure that indicates failure but doesn't break existing code
            return { 
              success: false, 
              error: result.error,
              _raw: response 
            } as unknown as z.infer<S[typeof methodName]>;
          }
          
          return result.data;
        } catch (error) {
          // Log original error but re-throw to preserve existing error handling
          console.error(`[apiValidator] API call failed for ${String(methodName)}:`, error);
          throw error;
        }
      };
    }
  }
  
  return validatedApi;
}

/**
 * Utility to create a schema map from individual schema entries.
 * Useful for building schema maps inline.
 */
export function createSchemaMap<T extends Record<string, z.ZodTypeAny>>(
  schemas: T
): T {
  return schemas;
}
