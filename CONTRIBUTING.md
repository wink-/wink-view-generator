# Contributing to Wink View Generator

Thank you for considering contributing to the Wink View Generator! This document outlines the guidelines and best practices for contributing to this project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [How to Contribute](#how-to-contribute)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Documentation](#documentation)
- [Pull Request Process](#pull-request-process)
- [Bug Reports](#bug-reports)
- [Feature Requests](#feature-requests)
- [Community](#community)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to team@winktools.dev.

### Our Standards

- Use welcoming and inclusive language
- Be respectful of differing viewpoints and experiences
- Gracefully accept constructive criticism
- Focus on what is best for the community
- Show empathy towards other community members

## Getting Started

### Prerequisites

- PHP 8.1 or higher
- Laravel 10.0 or 11.0+
- Composer 2.0+
- Git
- Node.js and npm (for front-end assets)

### Development Setup

1. **Fork the repository**
   ```bash
   git clone https://github.com/your-username/view-generator.git
   cd view-generator
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Set up testing environment**
   ```bash
   cp .env.example .env.testing
   php artisan key:generate --env=testing
   ```

4. **Run tests to ensure everything works**
   ```bash
   composer test
   ```

5. **Set up code formatting**
   ```bash
   composer format
   ```

## How to Contribute

### Types of Contributions

We welcome several types of contributions:

1. **Bug fixes** - Fix issues found in the codebase
2. **Feature development** - Add new functionality
3. **Documentation** - Improve or add documentation
4. **Templates** - Create new templates or improve existing ones
5. **Testing** - Add or improve test coverage
6. **Examples** - Create example projects or use cases

### Contribution Workflow

1. **Check existing issues** - Look for existing issues or feature requests
2. **Create an issue** - If one doesn't exist, create a new issue to discuss your idea
3. **Fork and branch** - Fork the repo and create a feature branch
4. **Develop** - Make your changes following our coding standards
5. **Test** - Ensure all tests pass and add new tests if needed
6. **Document** - Update documentation as necessary
7. **Submit PR** - Create a pull request with a clear description

## Coding Standards

### PHP Code Style

We follow PSR-12 coding standards with some additional rules:

```php
<?php

declare(strict_types=1);

namespace Wink\ViewGenerator\Example;

use Illuminate\Support\Str;

class ExampleClass
{
    private string $property;

    public function exampleMethod(string $parameter): string
    {
        return Str::studly($parameter);
    }
}
```

### Key Rules

- Use strict types declaration
- Use type hints for all parameters and return types
- Use meaningful variable and method names
- Follow PSR-12 formatting
- Use Laravel conventions for Eloquent models and controllers

### Code Formatting

We use Laravel Pint for code formatting:

```bash
# Format code
composer format

# Check formatting
composer format:check
```

### Static Analysis

We use PHPStan for static analysis:

```bash
# Run static analysis
composer analyse
```

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test:coverage

# Run specific test suite
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Feature
```

### Writing Tests

- Write tests for all new functionality
- Follow the AAA pattern (Arrange, Act, Assert)
- Use descriptive test method names
- Group related tests in test classes

#### Example Unit Test

```php
<?php

namespace Wink\ViewGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Wink\ViewGenerator\Generators\CrudViewGenerator;

class CrudViewGeneratorTest extends TestCase
{
    public function test_generates_index_view_with_correct_content(): void
    {
        // Arrange
        $generator = new CrudViewGenerator();
        $options = ['framework' => 'bootstrap'];

        // Act
        $result = $generator->generateIndexView('users', $options);

        // Assert
        $this->assertStringContainsString('@extends', $result);
        $this->assertStringContainsString('users', $result);
    }
}
```

#### Example Feature Test

```php
<?php

namespace Wink\ViewGenerator\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateCrudViewsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_crud_views_successfully(): void
    {
        // Arrange & Act
        $this->artisan('wink:views:crud users --framework=bootstrap')
            ->assertExitCode(0);

        // Assert
        $this->assertFileExists(resource_path('views/users/index.blade.php'));
        $this->assertFileExists(resource_path('views/users/create.blade.php'));
    }
}
```

### Test Coverage

- Aim for at least 80% code coverage
- Focus on testing public APIs and critical paths
- Include both positive and negative test cases
- Test edge cases and error conditions

## Documentation

### Types of Documentation

1. **Code Documentation** - PHPDoc comments for classes and methods
2. **User Documentation** - Guides and tutorials in the `docs/` directory
3. **API Documentation** - Generated from PHPDoc comments
4. **Examples** - Working examples in the `examples/` directory

### Writing Documentation

- Use clear, concise language
- Include code examples where appropriate
- Keep documentation up-to-date with code changes
- Follow Markdown best practices

#### PHPDoc Example

```php
/**
 * Generate CRUD views for a given table.
 *
 * @param string $tableName The name of the database table
 * @param array<string, mixed> $options Generation options
 * @return array<string, string> Generated view contents keyed by filename
 * 
 * @throws \InvalidArgumentException When table name is invalid
 * @throws \RuntimeException When generation fails
 */
public function generateCrudViews(string $tableName, array $options = []): array
{
    // Implementation
}
```

## Pull Request Process

### Before Submitting

1. **Ensure tests pass** - All existing tests must pass
2. **Add new tests** - Add tests for new functionality
3. **Update documentation** - Update relevant documentation
4. **Check code style** - Run code formatting and static analysis
5. **Update CHANGELOG** - Add entry to CHANGELOG.md

### PR Requirements

- **Clear title** - Descriptive title explaining the change
- **Detailed description** - Explain what, why, and how
- **Link issues** - Reference related issues using `Fixes #123`
- **Small focused changes** - Keep PRs focused on a single concern
- **Up-to-date branch** - Rebase on latest main branch

### PR Template

```markdown
## Description
Brief description of the changes.

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tests pass locally
- [ ] New tests added
- [ ] Manual testing completed

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
```

### Review Process

1. **Automated checks** - CI/CD pipeline runs tests and checks
2. **Code review** - Maintainers review the code
3. **Feedback** - Address any feedback or requested changes
4. **Approval** - PR is approved by maintainers
5. **Merge** - PR is merged into main branch

## Bug Reports

### Before Reporting

1. **Search existing issues** - Check if the bug has already been reported
2. **Update dependencies** - Ensure you're using the latest version
3. **Reproduce consistently** - Verify the bug can be reproduced

### Bug Report Template

```markdown
**Bug Description**
A clear and concise description of the bug.

**To Reproduce**
Steps to reproduce the behavior:
1. Run command '...'
2. With options '...'
3. See error

**Expected Behavior**
What you expected to happen.

**Actual Behavior**
What actually happened.

**Environment**
- PHP version: [e.g. 8.1.0]
- Laravel version: [e.g. 10.0.0]
- Package version: [e.g. 1.0.0]
- OS: [e.g. Ubuntu 20.04]

**Additional Context**
Any other context about the problem.
```

## Feature Requests

### Before Requesting

1. **Check existing requests** - Look for similar feature requests
2. **Consider scope** - Ensure the feature fits the project goals
3. **Think about implementation** - Consider how it might be implemented

### Feature Request Template

```markdown
**Feature Description**
A clear and concise description of the feature.

**Problem Statement**
What problem does this feature solve?

**Proposed Solution**
How should this feature work?

**Alternatives Considered**
Any alternative solutions you've considered.

**Additional Context**
Any other context or screenshots about the feature.
```

## Development Guidelines

### Architecture Principles

- **Single Responsibility** - Each class should have one reason to change
- **Open/Closed** - Open for extension, closed for modification
- **Dependency Injection** - Use Laravel's service container
- **Configuration over Convention** - Make behavior configurable

### Code Organization

```
src/
├── Commands/           # Artisan commands
├── Generators/         # View generators
├── Analyzers/         # Database and model analyzers
├── Templates/         # Template engines and loaders
├── Config/           # Configuration classes
└── Concerns/         # Shared traits and concerns
```

### Adding New Generators

1. **Extend AbstractViewGenerator** - Base class for all generators
2. **Implement required methods** - `generate()` and `getStubPath()`
3. **Add configuration** - Update config file if needed
4. **Create templates** - Add Blade stub templates
5. **Add tests** - Unit and feature tests
6. **Update documentation** - Add to command reference

### Adding New Commands

1. **Extend Command** - Laravel's base command class
2. **Define signature** - Command name and options
3. **Implement handle()** - Command logic
4. **Add validation** - Validate inputs and options
5. **Register command** - Add to service provider
6. **Add tests** - Command testing
7. **Update documentation** - Add to command reference

## Release Process

### Versioning

We use [Semantic Versioning](https://semver.org/):

- **MAJOR** - Breaking changes
- **MINOR** - New features (backward compatible)
- **PATCH** - Bug fixes (backward compatible)

### Release Checklist

1. **Update CHANGELOG.md** - Document all changes
2. **Update version** - In composer.json
3. **Run full test suite** - Ensure all tests pass
4. **Update documentation** - If needed
5. **Create release** - GitHub release with notes
6. **Announce** - Community channels

## Community

### Communication Channels

- **GitHub Issues** - Bug reports and feature requests
- **GitHub Discussions** - Questions and community discussion
- **Email** - team@winktools.dev for private matters

### Getting Help

- **Documentation** - Check the docs first
- **Search Issues** - Look for existing solutions
- **Ask Questions** - Use GitHub Discussions
- **Report Bugs** - Use GitHub Issues

### Recognition

Contributors are recognized in:

- **CONTRIBUTORS.md** - List of all contributors
- **Release Notes** - Major contributions mentioned
- **GitHub** - Contributor statistics and graphs

Thank you for contributing to Wink View Generator! Your contributions help make this project better for everyone.