import { describe, it, expect } from 'vitest';

describe('SignIn Zod Schema', () => {
  describe('email validation', () => {
    it('should accept valid email addresses', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const validEmails = [
        'test@example.com',
        'user.name@domain.org',
        'admin+tag@company.co.uk',
        'user123@subdomain.domain.com',
      ];

      for (const email of validEmails) {
        const result = signinSchema.safeParse({ email, password: 'password123' });
        expect(result.success).toBe(true);
      }
    });

    it('should reject invalid email addresses', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const invalidEmails = [
        'notanemail',
        'missing@domain',
        '@nodomain.com',
        'spaces in@email.com',
        '',
      ];

      for (const email of invalidEmails) {
        const result = signinSchema.safeParse({ email, password: 'password123' });
        expect(result.success).toBe(false);
      }
    });
  });

  describe('password validation', () => {
    it('should accept passwords with 6 or more characters', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const validPasswords = ['password123', 'secureP@ss1', 'abcdefgh', '123456'];

      for (const password of validPasswords) {
        const result = signinSchema.safeParse({ email: 'test@test.com', password });
        expect(result.success).toBe(true);
      }
    });

    it('should reject passwords shorter than 6 characters', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const shortPasswords = ['12345', 'abc', 'pass', 'a'];

      for (const password of shortPasswords) {
        const result = signinSchema.safeParse({ email: 'test@test.com', password });
        expect(result.success).toBe(false);
      }
    });

    it('should reject empty password', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const result = signinSchema.safeParse({ email: 'test@test.com', password: '' });
      expect(result.success).toBe(false);
    });
  });

  describe('complete form validation', () => {
    it('should validate correct signin data', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const validData = { email: 'user@example.com', password: 'password123' };
      const result = signinSchema.safeParse(validData);

      expect(result.success).toBe(true);
      if (result.success) {
        expect(result.data.email).toBe('user@example.com');
        expect(result.data.password).toBe('password123');
      }
    });

    it('should fail when email is missing', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const result = signinSchema.safeParse({ password: 'password123' });
      expect(result.success).toBe(false);
    });

    it('should fail when password is missing', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const result = signinSchema.safeParse({ email: 'test@test.com' });
      expect(result.success).toBe(false);
    });

    it('should fail when both email and password are missing', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const result = signinSchema.safeParse({});
      expect(result.success).toBe(false);
    });
  });

  describe('error messages', () => {
    it('should include error message for invalid email', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const result = signinSchema.safeParse({ email: 'invalid', password: 'password123' });

      expect(result.success).toBe(false);
      if (!result.success) {
        const emailError = result.error.issues.find((e) => e.path.includes('email'));
        expect(emailError).toBeDefined();
        expect(emailError?.message).toBe('Please enter a valid email address');
      }
    });

    it('should include error message for too short password', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const result = signinSchema.safeParse({ email: 'test@test.com', password: '12345' });

      expect(result.success).toBe(false);
      if (!result.success) {
        const passwordError = result.error.issues.find((e) => e.path.includes('password'));
        expect(passwordError).toBeDefined();
        expect(passwordError?.message).toBe('Password must be at least 6 characters');
      }
    });

    it('should include error message for empty password', async () => {
      const { signinSchema } = await import('../../../schemas/auth/signin.schema');
      
      const result = signinSchema.safeParse({ email: 'test@test.com', password: '' });

      expect(result.success).toBe(false);
      if (!result.success) {
        const passwordError = result.error.issues.find((e) => e.path.includes('password'));
        expect(passwordError).toBeDefined();
        expect(passwordError?.message).toBe('Password is required');
      }
    });
  });
});
