import { describe, it, expect } from 'vitest'
import { z } from 'zod'

// These tests verify the common Zod schemas once they are created in Task 4
// The schemas should include: emailSchema, passwordSchema, uuidSchema, paginationSchema, apiResponseSchema

describe('Common Zod Schemas', () => {
  describe('emailSchema', () => {
    // Dynamic import to handle case where schema might not exist yet
    it('should validate correct email addresses', async () => {
      const { emailSchema } = await import('../../schemas/common/email.schema')
      
      const validEmails = [
        'test@example.com',
        'user.name@domain.org',
        'admin+tag@company.co.uk',
      ]
      
      for (const email of validEmails) {
        const result = emailSchema.safeParse(email)
        expect(result.success).toBe(true)
        if (result.success) {
          expect(result.data).toBe(email)
        }
      }
    })
    
    it('should reject invalid email addresses', async () => {
      const { emailSchema } = await import('../../schemas/common/email.schema')
      
      const invalidEmails = [
        'notanemail',
        'missing@domain',
        '@nodomain.com',
        'spaces in@email.com',
      ]
      
      for (const email of invalidEmails) {
        const result = emailSchema.safeParse(email)
        expect(result.success).toBe(false)
      }
    })
  })
  
  describe('passwordSchema', () => {
    it('should validate passwords meeting minimum requirements', async () => {
      const { passwordSchema } = await import('../../schemas/common/password.schema')
      
      const validPasswords = ['password123', 'secureP@ss1', 'abcdefgh']
      
      for (const password of validPasswords) {
        const result = passwordSchema.safeParse(password)
        expect(result.success).toBe(true)
      }
    })
    
    it('should reject passwords that are too short', async () => {
      const { passwordSchema } = await import('../../schemas/common/password.schema')
      
      const shortPasswords = ['12345', 'abc', '']
      
      for (const password of shortPasswords) {
        const result = passwordSchema.safeParse(password)
        expect(result.success).toBe(false)
      }
    })
  })
  
  describe('uuidSchema', () => {
    it('should validate correct UUID format', async () => {
      const { uuidSchema } = await import('../../schemas/common/uuid.schema')
      
      const validUuids = [
        '550e8400-e29b-41d4-a716-446655440000',
        '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
        'f47ac10b-58cc-4372-a567-0e02b2c3d479',
      ]
      
      for (const uuid of validUuids) {
        const result = uuidSchema.safeParse(uuid)
        expect(result.success).toBe(true)
      }
    })
    
    it('should reject invalid UUID formats', async () => {
      const { uuidSchema } = await import('../../schemas/common/uuid.schema')
      
      const invalidUuids = [
        'not-a-uuid',
        '123456789',
        '550e8400-e29b-41d4-a716', // incomplete
        'gggggggg-gggg-gggg-gggg-gggggggggggg', // invalid chars
      ]
      
      for (const uuid of invalidUuids) {
        const result = uuidSchema.safeParse(uuid)
        expect(result.success).toBe(false)
      }
    })
  })
  
  describe('paginationSchema', () => {
    it('should validate correctly structured pagination response', async () => {
      const { paginationSchema } = await import('../../schemas/common/pagination.schema')
      
      // Flat pagination contract from backend paginated() helper
      const validPagination = {
        hasNextPage: true,
        hasPreviousPage: false,
        totalPage: 10,
        totalItem: 100,
      }
      
      const result = paginationSchema.safeParse(validPagination)
      expect(result.success).toBe(true)
    })
    
    it('should reject pagination without required fields', async () => {
      const { paginationSchema } = await import('../../schemas/common/pagination.schema')
      
      const invalidPagination = {
        hasNextPage: true,
        // missing hasPreviousPage, totalPage, totalItem
      }
      
      const result = paginationSchema.safeParse(invalidPagination)
      expect(result.success).toBe(false)
    })
  })
  
  describe('apiResponseSchema', () => {
    it('should validate success response', async () => {
      const { apiResponseSchema } = await import('../../schemas/common/apiResponse.schema')
      
      const successResponse = {
        success: true,
        data: { id: 1, name: 'Test' },
        message: 'Operation successful',
      }
      
      const result = apiResponseSchema.safeParse(successResponse)
      expect(result.success).toBe(true)
    })
    
    it('should validate error response', async () => {
      const { apiResponseSchema } = await import('../../schemas/common/apiResponse.schema')
      
      // Standard error envelope: success=false, message, data: null
      const errorResponse = {
        success: false,
        message: 'Something went wrong',
        data: null,
      }
      
      const result = apiResponseSchema.safeParse(errorResponse)
      expect(result.success).toBe(true)
    })
    
    it('should reject response without success flag', async () => {
      const { apiResponseSchema } = await import('../../schemas/common/apiResponse.schema')
      
      const invalidResponse = {
        data: {},
        message: 'No success flag',
      }
      
      const result = apiResponseSchema.safeParse(invalidResponse)
      expect(result.success).toBe(false)
    })

    it('should reject response without message', async () => {
      const { apiResponseSchema } = await import('../../schemas/common/apiResponse.schema')
      
      const invalidResponse = {
        success: true,
        data: { id: 1 },
      }
      
      const result = apiResponseSchema.safeParse(invalidResponse)
      expect(result.success).toBe(false)
    })

    it('should reject response without data', async () => {
      const { apiResponseSchema } = await import('../../schemas/common/apiResponse.schema')
      
      const invalidResponse = {
        success: true,
        message: 'Missing data field',
      }
      
      const result = apiResponseSchema.safeParse(invalidResponse)
      expect(result.success).toBe(false)
    })
  })
})
