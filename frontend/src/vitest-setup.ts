import { cleanup } from '@testing-library/react'
import { afterEach } from 'vitest'

// Cleanup after each test to prevent memory leaks and state pollution
afterEach(() => {
  cleanup()
})

// Global test utilities can be added here
// Example: global test helpers, custom matchers, etc.

// Re-export testing library utilities for convenience
export * from '@testing-library/react'
