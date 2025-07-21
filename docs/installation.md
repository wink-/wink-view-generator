# Installation Guide

This guide provides detailed instructions for installing and setting up the Wink View Generator package in your Laravel application.

## Table of Contents

- [System Requirements](#system-requirements)
- [Installation Methods](#installation-methods)
- [Configuration](#configuration)
- [Framework Setup](#framework-setup)
- [Asset Publishing](#asset-publishing)
- [Verification](#verification)
- [Troubleshooting](#troubleshooting)
- [Next Steps](#next-steps)

## System Requirements

### Minimum Requirements

- **PHP**: 8.1 or higher
- **Laravel**: 10.0 or 11.0+
- **Composer**: 2.0+
- **Node.js**: 16.0+ (for asset compilation)
- **npm/yarn**: Latest stable version

### Recommended Environment

- **PHP**: 8.2 or higher
- **Laravel**: 11.0+
- **MySQL**: 8.0+ or PostgreSQL: 13.0+
- **Redis**: 6.0+ (for caching and sessions)
- **Git**: For version control

### PHP Extensions

The following PHP extensions are required:

```bash
# Check if extensions are installed
php -m | grep -E "(mbstring|json|openssl|pdo|tokenizer|xml|ctype|fileinfo|bcmath)"
```

Required extensions:
- `mbstring` - Multi-byte string support
- `json` - JSON processing
- `openssl` - Encryption and security
- `pdo` - Database abstraction
- `tokenizer` - PHP tokenization
- `xml` - XML processing
- `ctype` - Character type checking
- `fileinfo` - File information
- `bcmath` - Arbitrary precision mathematics

## Installation Methods

### Method 1: Composer (Recommended)

Install the package via Composer:

```bash
composer require wink/view-generator
```

The package will be automatically discovered by Laravel's package auto-discovery feature.

### Method 2: Manual Installation

If you need to install manually or want more control:

1. **Add to composer.json**:
   ```json
   {
       "require": {
           "wink/view-generator": "^1.0"
       }
   }
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Register service provider** (Laravel 10.x and below):
   ```php
   // config/app.php
   'providers' => [
       // Other providers...
       Wink\ViewGenerator\ViewGeneratorServiceProvider::class,
   ],
   ```

### Method 3: Development Installation

For development or testing:

```bash
# Install with development dependencies
composer require wink/view-generator --dev

# Or clone from repository
git clone https://github.com/wink/view-generator.git
cd view-generator
composer install
```

## Configuration

### 1. Publish Configuration

Publish the configuration file to customize package behavior:

```bash
php artisan vendor:publish --provider="Wink\ViewGenerator\ViewGeneratorServiceProvider" --tag="config"
```

This creates `config/wink-views.php` with all available options.

### 2. Environment Variables

Add environment variables to your `.env` file:

```env
# Framework Selection
WINK_VIEWS_FRAMEWORK=bootstrap

# Layout Configuration
WINK_VIEWS_MASTER_LAYOUT=layouts.app
WINK_VIEWS_ADMIN_LAYOUT=layouts.admin

# Feature Toggles
WINK_VIEWS_PAGINATION=true
WINK_VIEWS_SEARCH=true
WINK_VIEWS_AJAX_FORMS=true
WINK_VIEWS_DARK_MODE=true

# Component Settings
WINK_VIEWS_USE_COMPONENTS=true
WINK_VIEWS_COMPONENT_NAMESPACE=components

# Asset Management
WINK_VIEWS_CSS_FRAMEWORK=cdn
WINK_VIEWS_JS_FRAMEWORK=cdn
```

### 3. Configuration Options

Key configuration sections in `config/wink-views.php`:

```php
return [
    // UI Framework (bootstrap|tailwind|custom)
    'framework' => env('WINK_VIEWS_FRAMEWORK', 'bootstrap'),
    
    // Layout templates
    'layout' => [
        'master' => env('WINK_VIEWS_MASTER_LAYOUT', 'layouts.app'),
        'admin' => env('WINK_VIEWS_ADMIN_LAYOUT', 'layouts.admin'),
    ],
    
    // Feature configuration
    'features' => [
        'pagination' => env('WINK_VIEWS_PAGINATION', true),
        'search' => env('WINK_VIEWS_SEARCH', true),
        'ajax_forms' => env('WINK_VIEWS_AJAX_FORMS', true),
    ],
    
    // Component settings
    'components' => [
        'use_components' => env('WINK_VIEWS_USE_COMPONENTS', true),
        'component_namespace' => env('WINK_VIEWS_COMPONENT_NAMESPACE', 'components'),
    ],
];
```

## Framework Setup

### Bootstrap 5 Setup

1. **Install Bootstrap**:
   ```bash
   npm install bootstrap@5.3.0 bootstrap-icons
   ```

2. **Include in your layout**:
   ```html
   <!-- CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
   
   <!-- JavaScript -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   ```

3. **Or compile locally**:
   ```scss
   // resources/sass/app.scss
   @import '~bootstrap/scss/bootstrap';
   ```

### Tailwind CSS Setup

1. **Install Tailwind**:
   ```bash
   npm install -D tailwindcss @tailwindcss/forms @tailwindcss/typography
   npm install @heroicons/react
   ```

2. **Initialize Tailwind**:
   ```bash
   npx tailwindcss init -p
   ```

3. **Configure tailwind.config.js**:
   ```javascript
   module.exports = {
     content: [
       './resources/**/*.blade.php',
       './resources/**/*.js',
       './resources/**/*.vue',
       './vendor/wink/view-generator/resources/**/*.php',
     ],
     theme: {
       extend: {},
     },
     plugins: [
       require('@tailwindcss/forms'),
       require('@tailwindcss/typography'),
     ],
   }
   ```

4. **Add to CSS**:
   ```css
   /* resources/css/app.css */
   @tailwind base;
   @tailwind components;
   @tailwind utilities;
   ```

### Custom Framework Setup

For custom CSS frameworks, no additional setup is required. The package generates semantic HTML that you can style with your own CSS.

## Asset Publishing

### 1. Publish Templates (Optional)

Publish templates for customization:

```bash
php artisan vendor:publish --provider="Wink\ViewGenerator\ViewGeneratorServiceProvider" --tag="templates"
```

This creates `resources/stubs/wink-views/` with all template files.

### 2. Publish Assets (Optional)

Publish CSS and JavaScript assets:

```bash
php artisan vendor:publish --provider="Wink\ViewGenerator\ViewGeneratorServiceProvider" --tag="assets"
```

This creates `public/vendor/wink-views/` with package assets.

### 3. Publish Everything

Publish all package files:

```bash
php artisan vendor:publish --provider="Wink\ViewGenerator\ViewGeneratorServiceProvider"
```

## Database Setup

### 1. Run Migrations

Ensure your database is set up:

```bash
php artisan migrate
```

### 2. Seed Test Data (Optional)

Create test data for development:

```bash
php artisan db:seed
```

### 3. Create Models

Ensure you have Eloquent models for your tables:

```bash
php artisan make:model User
php artisan make:model Post
```

## Verification

### 1. Check Installation

Verify the package is installed correctly:

```bash
# List available commands
php artisan list wink

# Check package version
composer show wink/view-generator
```

### 2. Test Basic Generation

Generate a simple view to test installation:

```bash
php artisan wink:views:crud users --dry-run
```

### 3. Check Configuration

Verify configuration is loaded:

```bash
php artisan config:show wink-views
```

### 4. Test Dependencies

Check that all dependencies are working:

```bash
# Run tests (if in development)
vendor/bin/phpunit

# Check code style
vendor/bin/pint --test
```

## Troubleshooting

### Common Issues

#### 1. Command Not Found

**Problem**: `Command "wink:views:crud" is not defined`

**Solutions**:
```bash
# Clear config cache
php artisan config:clear

# Clear package cache
php artisan package:discover

# Republish service provider
php artisan vendor:publish --provider="Wink\ViewGenerator\ViewGeneratorServiceProvider"
```

#### 2. Template Not Found

**Problem**: Template files not found during generation

**Solutions**:
```bash
# Publish templates
php artisan vendor:publish --tag="wink-views-templates"

# Check template path in config
php artisan config:show wink-views.paths.stubs
```

#### 3. Permission Errors

**Problem**: Cannot write to views directory

**Solutions**:
```bash
# Fix permissions
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data resources/views
sudo chmod -R 775 storage
sudo chmod -R 775 resources/views
```

#### 4. Asset Loading Issues

**Problem**: CSS/JS assets not loading

**Solutions**:
```bash
# Publish assets
php artisan vendor:publish --tag="wink-views-assets"

# Link storage
php artisan storage:link

# Clear cache
php artisan view:clear
php artisan cache:clear
```

#### 5. Database Connection Errors

**Problem**: Cannot analyze database schema

**Solutions**:
```bash
# Test database connection
php artisan migrate:status

# Check database configuration
php artisan config:show database
```

### Debug Mode

Enable debug mode for troubleshooting:

```env
# .env
APP_DEBUG=true
WINK_VIEWS_DEBUG=true
```

### Logging

Check Laravel logs for detailed error information:

```bash
tail -f storage/logs/laravel.log
```

## Performance Optimization

### 1. Config Caching

Cache configuration for production:

```bash
php artisan config:cache
```

### 2. Route Caching

Cache routes for better performance:

```bash
php artisan route:cache
```

### 3. View Caching

Cache compiled views:

```bash
php artisan view:cache
```

### 4. Asset Optimization

Optimize assets for production:

```bash
npm run production
```

## Next Steps

After successful installation:

1. **Read the [Getting Started Guide](getting-started.md)** - Learn basic usage
2. **Explore [Commands Reference](commands.md)** - Understand all available commands
3. **Review [Configuration Guide](configuration.md)** - Customize package behavior
4. **Check [Framework Support](frameworks.md)** - Framework-specific setup
5. **Browse [Examples](../examples/)** - See real-world usage examples

## Support

If you encounter issues during installation:

- **Check the [FAQ](#troubleshooting)** section above
- **Search [GitHub Issues](https://github.com/wink/view-generator/issues)**
- **Create a new issue** with detailed error information
- **Join [GitHub Discussions](https://github.com/wink/view-generator/discussions)**

## Updating

To update the package:

```bash
# Update to latest version
composer update wink/view-generator

# Republish config if needed
php artisan vendor:publish --provider="Wink\ViewGenerator\ViewGeneratorServiceProvider" --tag="config" --force

# Check changelog for breaking changes
cat vendor/wink/view-generator/CHANGELOG.md
```