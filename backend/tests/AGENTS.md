<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-20 | Updated: 2026-04-20 -->

# Tests

## Purpose
Backend test suite using PHPUnit 12.5+. Includes feature tests for API endpoints and unit tests for individual components.

## Key Files
| File | Description |
|------|-------------|
| `TestCase.php` | Base test class with test setup |
| `phpunit.xml` | PHPUnit configuration |

## Subdirectories
| Directory | Purpose |
|-----------|---------|
| `Feature/` | API endpoint and integration tests |
| `Unit/` | Unit tests for individual classes |

## Test Files

### Feature Tests
| File | Description |
|------|-------------|
| `AuthSignupTest.php` | User registration with email verification |
| `GoogleOAuthAccountLinkingTest.php` | OAuth account linking logic |
| `CourseOwnershipTest.php` | Permission-based course access |
| `VideoUploadFlowTest.php` | Direct-to-S3 video upload |
| `OrderPaymentFlowTest.php` | Order creation and payment |
| `ApiResponseEnvelopeTest.php` | API response format consistency |

### Unit Tests
| File | Description |
|------|-------------|
| `RbacPermissionMapTest.php` | RBAC permission mapping |

## For AI Agents

### Working In This Directory
- Use `php artisan make:test` to create new tests
- Feature tests extend `Tests\TestCase`
- Use `RefreshDatabase` trait for database tests
- Use `actingAs()` for authenticated requests

### Common Patterns
```php
// API test pattern
$response = $this->actingAs($user)
    ->postJson('/api/endpoint', $data);
$response->assertStatus(200)
    ->assertJson(['success' => true]);

// Database test pattern
use RefreshDatabase;
$this->assertDatabaseHas('table', ['key' => 'value']);
```

### Testing Requirements
- Run tests before committing
- Ensure >80% coverage for critical paths
- Test both success and failure cases

## Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite=Feature

# Run specific test
./vendor/bin/phpunit tests/Feature/ExampleTest.php

# Run with coverage
./vendor/bin/phpunit --coverage
```

## Dependencies

### Internal
- `app/` - Application code being tested
- `database/` - Test factories and seeders

### External
- `phpunit/phpunit` v12.5+ - Testing framework
- `mockery/mockery` v1.6 - Mock framework
- `fakerphp/faker` v1.23 - Test data generation

<!-- MANUAL: -->
