<!-- Parent: ../AGENTS.md -->
<!-- Generated: 2026-04-20 | Updated: 2026-04-20 -->

# Contracts

## Purpose
Interface definitions for service layer contracts. Provides contracts for dependency injection and ensures consistent implementations across services.

## Key Files
| File | Description |
|------|-------------|
| `ISeedDataService.php` | Interface for idempotent database seeding operations |

## For AI Agents

### Working In This Directory
- All interfaces define public methods only
- Use PHP 8.3+ constructor property promotion in implementations
- Interfaces use return type declarations (`void`)

### Common Patterns
- Interface naming: `I` prefix (e.g., `ISeedDataService`)
- Methods grouped by functionality (seed operations in dependency order)
- PHPDoc for all public methods

## Dependencies

### Internal
- `App\Support\SeedData\*` - Seed data implementations
- `App\Services\SeedDataService` - Service implementation

<!-- MANUAL: -->
