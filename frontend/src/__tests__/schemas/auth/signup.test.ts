import { describe, it, expect } from 'vitest'
import { signupSchema } from '../../../schemas/auth/signup.schema'

describe('Auth Zod Schemas', () => {
  describe('signupSchema', () => {
    it('should validate correct signup data', async () => {
      const validData = {
        first_name: 'John',
        last_name: 'Doe',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      }

      const result = signupSchema.safeParse(validData)
      expect(result.success).toBe(true)
      if (result.success) {
        expect(result.data).toEqual(validData)
      }
    })

    it('should reject when first_name is empty', async () => {
      const invalidData = {
        first_name: '',
        last_name: 'Doe',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      }

      const result = signupSchema.safeParse(invalidData)
      expect(result.success).toBe(false)
    })

    it('should reject when last_name is empty', async () => {
      const invalidData = {
        first_name: 'John',
        last_name: '',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      }

      const result = signupSchema.safeParse(invalidData)
      expect(result.success).toBe(false)
    })

    it('should reject invalid email addresses', async () => {
      const invalidEmails = [
        'notanemail',
        'missing@domain',
        '@nodomain.com',
        'spaces in@email.com',
      ]

      for (const email of invalidEmails) {
        const result = signupSchema.safeParse({
          first_name: 'John',
          last_name: 'Doe',
          email,
          password: 'password123',
          password_confirmation: 'password123',
        })
        expect(result.success).toBe(false)
      }
    })

    it('should reject passwords shorter than 6 characters', async () => {
      const result = signupSchema.safeParse({
        first_name: 'John',
        last_name: 'Doe',
        email: 'test@example.com',
        password: '12345',
        password_confirmation: '12345',
      })
      expect(result.success).toBe(false)
    })

    it('should reject when password_confirmation does not match password', async () => {
      const result = signupSchema.safeParse({
        first_name: 'John',
        last_name: 'Doe',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'different456',
      })
      expect(result.success).toBe(false)
      if (!result.success) {
        // The error should be on password_confirmation path
        const issues = result.error.issues
        expect(issues.some(issue => issue.path.includes('password_confirmation'))).toBe(true)
      }
    })

    it('should reject when password_confirmation is empty', async () => {
      const result = signupSchema.safeParse({
        first_name: 'John',
        last_name: 'Doe',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: '',
      })
      expect(result.success).toBe(false)
    })

    it('should accept valid email addresses', async () => {
      const validEmails = [
        'test@example.com',
        'user.name@domain.org',
        'admin+tag@company.co.uk',
      ]

      for (const email of validEmails) {
        const result = signupSchema.safeParse({
          first_name: 'John',
          last_name: 'Doe',
          email,
          password: 'password123',
          password_confirmation: 'password123',
        })
        expect(result.success).toBe(true)
      }
    })

    it('should accept password of exactly 6 characters', async () => {
      const result = signupSchema.safeParse({
        first_name: 'John',
        last_name: 'Doe',
        email: 'test@example.com',
        password: '123456',
        password_confirmation: '123456',
      })
      expect(result.success).toBe(true)
    })

    it('should reject when email is empty', async () => {
      const result = signupSchema.safeParse({
        first_name: 'John',
        last_name: 'Doe',
        email: '',
        password: 'password123',
        password_confirmation: 'password123',
      })
      expect(result.success).toBe(false)
    })
  })
})
