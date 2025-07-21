# Wink View Generator - Test Suite

This comprehensive test suite ensures the reliability, performance, and quality of the Wink View Generator package. It covers all aspects of the codebase with unit tests, feature tests, integration tests, and quality assurance checks.

## 📁 Test Structure

```
tests/
├── TestCase.php                     # Base test case with Orchestra Testbench
├── TestRunner.php                   # Comprehensive test runner and reporter
├── README.md                        # This documentation
├── 
├── Unit/                            # Unit tests for individual classes
│   ├── Analyzers/
│   │   ├── ModelAnalyzerTest.php    # Database schema analysis
│   │   ├── FieldAnalyzerTest.php    # Field type detection
│   │   ├── ControllerAnalyzerTest.php # Controller analysis
│   │   └── RouteAnalyzerTest.php    # Route analysis
│   ├── Generators/
│   │   ├── AbstractViewGeneratorTest.php # Base generator functionality
│   │   ├── CrudViewGeneratorTest.php     # CRUD view generation
│   │   └── ComponentGeneratorTest.php    # Component generation
│   └── Config/
│       └── ViewConfigTest.php       # Configuration management
├── 
├── Feature/                         # End-to-end feature tests
│   └── Commands/
│       ├── GenerateCrudViewsCommandTest.php # CRUD command testing
│       ├── GenerateComponentsCommandTest.php # Component command testing
│       └── GenerateAllCommandTest.php       # Comprehensive generation
├── 
├── Integration/                     # Integration and system tests
│   └── ViewGeneratorServiceProviderTest.php # Service provider testing
├── 
├── Fixtures/                        # Test data and mock objects
│   ├── migrations/                  # Test database migrations
│   ├── models/                      # Sample model classes
│   ├── controllers/                 # Sample controller classes
│   └── stubs/                       # Template stubs for testing
├── 
└── Utilities/                       # Test helper classes
    ├── TestDataGenerator.php       # Sample data generation
    ├── ViewTestHelpers.php         # View validation helpers
    └── MockDataProvider.php        # Mock object management
```

## 🚀 Quick Start

### Prerequisites

Ensure you have the required testing dependencies:

```bash
composer require --dev phpunit/phpunit
composer require --dev orchestra/testbench
composer require --dev mockery/mockery
```

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test categories
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Feature
vendor/bin/phpunit tests/Integration

# Run with coverage report
vendor/bin/phpunit --coverage-html coverage

# Run specific test class
vendor/bin/phpunit tests/Unit/Analyzers/ModelAnalyzerTest.php

# Run specific test method
vendor/bin/phpunit --filter test_method_name
```

## 📊 Test Coverage

Our test suite maintains high coverage standards:

- **Overall Coverage Target**: >90%
- **Critical Components**: >95%
- **Command Classes**: >85%
- **Configuration Classes**: >95%

### Coverage Areas

✅ **Fully Covered Components:**
- Model and field analysis
- View generation logic
- Configuration management
- Service provider registration
- Command execution flows

✅ **Quality Assurance Checks:**
- Accessibility compliance (WCAG AA)
- Responsive design validation
- Framework convention adherence
- Security best practices
- Performance optimization

## 🧪 Test Categories

### Unit Tests (`tests/Unit/`)

Test individual classes and methods in isolation:

- **Analyzer Classes**: Validate database schema analysis, field type detection, and relationship mapping
- **Generator Classes**: Test view generation logic, template processing, and file operations
- **Configuration Classes**: Verify configuration validation, default values, and framework support

### Feature Tests (`tests/Feature/`)

Test complete workflows and user interactions:

- **Artisan Commands**: End-to-end command execution with various options
- **View Generation**: Complete CRUD and component generation workflows
- **Error Handling**: Validation, dry-run, and force-overwrite scenarios

### Integration Tests (`tests/Integration/`)

Test system integration and service registration:

- **Service Provider**: Dependency injection, command registration, and configuration publishing
- **Laravel Integration**: Testbench integration, database connections, and file system operations

## 🛠 Test Utilities

### TestDataGenerator

Generates realistic test data for various scenarios:

```php
// Generate model data
$modelData = TestDataGenerator::generateModelData('users');

// Generate field data
$fieldData = TestDataGenerator::generateFieldData('posts');

// Generate view options
$options = TestDataGenerator::generateViewOptions(['framework' => 'tailwind']);
```

### ViewTestHelpers

Validates generated view quality:

```php
// Validate Blade template syntax
$issues = ViewTestHelpers::assertValidBladeTemplate($content);

// Check accessibility compliance
$issues = ViewTestHelpers::assertAccessibilityCompliant($content);

// Verify responsive design
$issues = ViewTestHelpers::assertResponsiveDesign($content);

// Validate framework conventions
$issues = ViewTestHelpers::assertFrameworkConventions($content, 'bootstrap');
```

### MockDataProvider

Creates mock objects for testing:

```php
// Mock filesystem operations
$mockFs = MockDataProvider::mockFilesystem();

// Mock model analyzer
$mockAnalyzer = MockDataProvider::mockModelAnalyzer('users');

// Mock view generator
$mockGenerator = MockDataProvider::mockViewGenerator(true);
```

## 🎯 Testing Best Practices

### Writing Tests

1. **Use descriptive test names**: `it_generates_crud_views_with_bootstrap_framework()`
2. **Follow AAA pattern**: Arrange, Act, Assert
3. **Test edge cases**: Empty data, invalid input, missing files
4. **Mock external dependencies**: Database, filesystem, API calls
5. **Use data providers**: Test multiple scenarios efficiently

### Example Test Structure

```php
/** @test */
public function it_generates_valid_crud_views_for_users_table()
{
    // Arrange
    $table = 'users';
    $modelData = TestDataGenerator::generateModelData($table);
    $options = ['framework' => 'bootstrap'];
    
    // Act
    $result = $this->generator->generate($table, $modelData, $options);
    
    // Assert
    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    foreach ($result as $viewResult) {
        $this->assertTrue($viewResult['success']);
        $this->assertStringEndsWith('.blade.php', $viewResult['file']);
    }
}
```

### Test Database

Tests use an in-memory SQLite database for fast, isolated testing:

```php
protected function defineEnvironment($app): void
{
    $app['config']->set('database.default', 'testing');
    $app['config']->set('database.connections.testing', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);
}
```

## 🔍 Quality Assurance

### Accessibility Testing

Validates generated views meet WCAG AA standards:

- Alt text for images
- Label associations for form inputs
- Proper heading hierarchy
- ARIA landmarks and roles
- Keyboard navigation support

### Responsive Design Testing

Ensures views work across all screen sizes:

- Responsive grid classes
- Mobile-first approach
- Flexible layouts
- Appropriate breakpoints

### Framework Compliance

Validates adherence to framework conventions:

- **Bootstrap**: Container usage, grid system, component classes
- **Tailwind**: Utility-first approach, consistent spacing
- **Custom**: Naming conventions, consistent patterns

### Security Testing

Checks for security best practices:

- CSRF protection in forms
- XSS prevention in templates
- Input validation and sanitization
- Secure file operations

## 📈 Performance Testing

### Metrics Tracked

- Test execution time
- Memory usage patterns
- File generation speed
- Database query efficiency

### Performance Targets

- Average test execution: <0.5s
- Peak memory usage: <50MB
- View generation: <100ms per view
- Command execution: <2s total

## 🚨 Continuous Integration

### GitHub Actions Workflow

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: [8.1, 8.2, 8.3]
        laravel: [10.x, 11.x]
        
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: vendor/bin/phpunit --coverage-clover coverage.xml
      - name: Upload coverage
        uses: codecov/codecov-action@v3
```

### Quality Gates

- All tests must pass
- Coverage must be >90%
- No security vulnerabilities
- Performance regressions checked

## 🐛 Debugging Tests

### Common Issues

1. **Database not found**: Ensure test database is configured
2. **File permissions**: Check temp directory write permissions
3. **Missing dependencies**: Run `composer install --dev`
4. **Memory limits**: Increase PHP memory limit for complex tests

### Debug Commands

```bash
# Run tests with verbose output
vendor/bin/phpunit --verbose

# Run single test with debug
vendor/bin/phpunit --filter test_name --debug

# Generate detailed coverage report
vendor/bin/phpunit --coverage-html coverage --coverage-text
```

## 📚 Additional Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Orchestra Testbench](https://packages.tools/testbench)
- [Mockery Documentation](http://docs.mockery.io/)
- [Laravel Testing](https://laravel.com/docs/testing)

## 🤝 Contributing

When adding new features:

1. Write tests first (TDD approach)
2. Ensure >90% coverage for new code
3. Include integration tests for commands
4. Add accessibility and security checks
5. Update test documentation

### Test Checklist

- [ ] Unit tests for new classes/methods
- [ ] Feature tests for user workflows
- [ ] Integration tests for system components
- [ ] Accessibility validation
- [ ] Security checks
- [ ] Performance validation
- [ ] Documentation updates

---

This test suite ensures the Wink View Generator delivers reliable, high-quality, accessible, and performant view generation for Laravel applications. 🚀