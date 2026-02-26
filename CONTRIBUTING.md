# Contributing to Station Example

Thank you for your interest in contributing to the Station Example application! This document provides guidelines and instructions for contributing.

## Requirements

- **PHP 8.3+** (8.4 recommended)
- **Laravel 12.x**
- **Docker** (for development environment)

## Development Environment Setup

### 1. Clone and Install

```bash
git clone https://github.com/ojbaeza/station-example.git
cd station-example
composer install
```

### 2. Start Docker Services

```bash
docker compose up -d
```

### 3. Verify Setup

```bash
docker exec station_example sh -c "XDEBUG_MODE=off php artisan test"
```

## Code Style

This project follows **PER-CS 3.0** coding standards.

### Fixing Code Style

```bash
composer cs-fix
```

**Note:** Do not use Laravel Pint. Use `composer cs-fix` (PHP-CS-Fixer) instead.

## Static Analysis

Station Example uses **PHPStan at level 8** with Larastan for Laravel-specific rules.

```bash
docker exec station_example sh -c "XDEBUG_MODE=off php vendor/bin/phpstan analyse --memory-limit=512M"
```

## Testing

### Running Tests

```bash
# All tests
docker exec station_example sh -c "XDEBUG_MODE=off php artisan test"

# Specific test
docker exec station_example sh -c "XDEBUG_MODE=off php artisan test --filter TestClassName"
```

### Test Naming Convention

Use camelCase pattern: `test{Feature}{Scenario}{ExpectedResult}`

```php
public function testDispatchWithValidJobReturnsJobId(): void
public function testRecoveryWithStuckJobResumesSuccessfully(): void
```

## Pull Request Process

### Before Submitting

1. **Create a feature branch** from `main`
2. **Write tests** for your changes
3. **Ensure all checks pass:**
   ```bash
   composer cs-fix
   docker exec station_example sh -c "XDEBUG_MODE=off php vendor/bin/phpstan analyse --memory-limit=512M"
   docker exec station_example sh -c "XDEBUG_MODE=off php artisan test"
   ```

### Submitting

1. Push your branch to your fork
2. Open a Pull Request against `main`
3. Wait for CI checks to pass

### PR Guidelines

- Keep PRs focused and reasonably sized
- One feature or fix per PR
- Write clear commit messages
- Reference related issues

## Getting Help

- **Questions:** Open a GitHub Discussion
- **Bug Reports:** Open a GitHub Issue with reproduction steps
- **Station Package:** See [ojbaeza/station](https://github.com/ojbaeza/station)

## Code of Conduct

This project follows our [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to uphold this code.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
