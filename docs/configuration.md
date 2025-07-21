# Configuration Guide

This guide covers all configuration options available in the Wink View Generator package, how to customize them, and best practices for different environments.

## Table of Contents

- [Configuration File](#configuration-file)
- [Environment Variables](#environment-variables)
- [Framework Configuration](#framework-configuration)
- [Layout Configuration](#layout-configuration)
- [Component Configuration](#component-configuration)
- [Feature Configuration](#feature-configuration)
- [Styling Configuration](#styling-configuration)
- [Form Configuration](#form-configuration)
- [Table Configuration](#table-configuration)
- [Asset Configuration](#asset-configuration)
- [Accessibility Configuration](#accessibility-configuration)
- [Performance Configuration](#performance-configuration)
- [Advanced Configuration](#advanced-configuration)
- [Environment-Specific Settings](#environment-specific-settings)
- [Validation and Testing](#validation-and-testing)

## Configuration File

### Publishing Configuration

First, publish the configuration file:

```bash
php artisan vendor:publish --provider="Wink\ViewGenerator\ViewGeneratorServiceProvider" --tag="config"
```

This creates `config/wink-views.php` with all available options.

### Configuration Structure

The configuration file is organized into logical sections:

```php
<?php

return [
    'framework' => 'bootstrap',
    'layout' => [...],
    'components' => [...],
    'features' => [...],
    'styling' => [...],
    'forms' => [...],
    'tables' => [...],
    'assets' => [...],
    'accessibility' => [...],
    'performance' => [...],
    'paths' => [...],
    'field_mappings' => [...],
    'relationship_mappings' => [...],
    'validation' => [...],
    'seo' => [...],
    'customization' => [...],
];
```

## Environment Variables

### Core Environment Variables

Add these to your `.env` file for easy configuration:

```env
# Framework Selection
WINK_VIEWS_FRAMEWORK=bootstrap

# Layout Configuration
WINK_VIEWS_MASTER_LAYOUT=layouts.app
WINK_VIEWS_ADMIN_LAYOUT=layouts.admin
WINK_VIEWS_AUTH_LAYOUT=layouts.auth

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

### Complete Environment Variables List

```env
# Framework and Layout
WINK_VIEWS_FRAMEWORK=bootstrap
WINK_VIEWS_MASTER_LAYOUT=layouts.app
WINK_VIEWS_ADMIN_LAYOUT=layouts.admin
WINK_VIEWS_AUTH_LAYOUT=layouts.auth
WINK_VIEWS_DASHBOARD_LAYOUT=layouts.dashboard
WINK_VIEWS_ERROR_LAYOUT=layouts.error

# Components
WINK_VIEWS_USE_COMPONENTS=true
WINK_VIEWS_COMPONENT_NAMESPACE=components
WINK_VIEWS_LIVEWIRE=false
WINK_VIEWS_ALPINE=false
WINK_VIEWS_BLADE_COMPONENTS=true

# Features
WINK_VIEWS_PAGINATION=true
WINK_VIEWS_SEARCH=true
WINK_VIEWS_FILTERING=true
WINK_VIEWS_SORTING=true
WINK_VIEWS_BULK_ACTIONS=true
WINK_VIEWS_EXPORT=true
WINK_VIEWS_AJAX_FORMS=true
WINK_VIEWS_MODALS=true
WINK_VIEWS_BREADCRUMBS=true
WINK_VIEWS_FLASH_MESSAGES=true

# Styling
WINK_VIEWS_DARK_MODE=true
WINK_VIEWS_ANIMATIONS=true
WINK_VIEWS_ICONS=bootstrap-icons
WINK_VIEWS_COLOR_SCHEME=blue
WINK_VIEWS_BORDER_RADIUS=md
WINK_VIEWS_SHADOWS=true

# Forms
WINK_VIEWS_VALIDATION_STYLE=inline
WINK_VIEWS_RICH_TEXT_EDITOR=tinymce
WINK_VIEWS_DATE_PICKER=flatpickr
WINK_VIEWS_FILE_UPLOAD=dropzone
WINK_VIEWS_CLIENT_VALIDATION=true
WINK_VIEWS_PROGRESSIVE_ENHANCEMENT=true
WINK_VIEWS_CSRF_PROTECTION=true

# Tables
WINK_VIEWS_PAGINATION_SIZE=15
WINK_VIEWS_RESPONSIVE_BREAKPOINT=md
WINK_VIEWS_STICKY_HEADERS=false
WINK_VIEWS_ROW_ACTIONS=dropdown
WINK_VIEWS_EMPTY_STATE=true
WINK_VIEWS_LOADING_STATES=true

# Assets
WINK_VIEWS_CSS_FRAMEWORK=cdn
WINK_VIEWS_JS_FRAMEWORK=cdn
WINK_VIEWS_GENERATE_ASSETS=true
WINK_VIEWS_MINIFY_ASSETS=false
WINK_VIEWS_VERSION_ASSETS=false

# Accessibility
WINK_VIEWS_WCAG_COMPLIANCE=AA
WINK_VIEWS_ARIA_LABELS=true
WINK_VIEWS_KEYBOARD_NAV=true
WINK_VIEWS_SCREEN_READER=true
WINK_VIEWS_HIGH_CONTRAST=false
WINK_VIEWS_FOCUS_INDICATORS=true

# Performance
WINK_VIEWS_LAZY_LOADING=true
WINK_VIEWS_IMAGE_OPTIMIZATION=true
WINK_VIEWS_CRITICAL_CSS=false
WINK_VIEWS_PRELOAD_ASSETS=false

# Validation
WINK_VIEWS_SHOW_REQUIRED=true
WINK_VIEWS_SHOW_HINTS=true
WINK_VIEWS_SHOW_CHAR_COUNT=false
WINK_VIEWS_REAL_TIME_VALIDATION=false
WINK_VIEWS_VALIDATION_DELAY=500

# SEO
WINK_VIEWS_META_TAGS=true
WINK_VIEWS_STRUCTURED_DATA=false
WINK_VIEWS_OPEN_GRAPH=false
WINK_VIEWS_TWITTER_CARDS=false

# Customization
WINK_VIEWS_ALLOW_STUB_OVERRIDE=true
WINK_VIEWS_CUSTOM_HELPERS=true
WINK_VIEWS_GENERATE_COMMENTS=true
WINK_VIEWS_PRESERVE_CUSTOM_CODE=false
WINK_VIEWS_BACKUP_FILES=true

# Paths
WINK_VIEWS_STUBS_PATH=resources/stubs/wink-views
WINK_VIEWS_OUTPUT_PATH=resources/views
WINK_VIEWS_COMPONENTS_PATH=resources/views/components
WINK_VIEWS_LAYOUTS_PATH=resources/views/layouts
WINK_VIEWS_ASSETS_PATH=public/assets
```

## Framework Configuration

### Bootstrap Configuration

```php
'framework' => env('WINK_VIEWS_FRAMEWORK', 'bootstrap'),

'styling' => [
    'icons' => 'bootstrap-icons',
    'color_scheme' => 'blue',
    'border_radius' => 'md',
    'shadows' => true,
],

'assets' => [
    'css_framework' => 'cdn',
    'js_framework' => 'cdn',
],
```

**Environment setup:**
```env
WINK_VIEWS_FRAMEWORK=bootstrap
WINK_VIEWS_ICONS=bootstrap-icons
WINK_VIEWS_COLOR_SCHEME=blue
```

### Tailwind Configuration

```php
'framework' => env('WINK_VIEWS_FRAMEWORK', 'tailwind'),

'styling' => [
    'icons' => 'heroicons',
    'color_scheme' => 'blue',
    'dark_mode' => true,
],

'assets' => [
    'css_framework' => 'npm',
    'js_framework' => 'local',
],
```

**Environment setup:**
```env
WINK_VIEWS_FRAMEWORK=tailwind
WINK_VIEWS_ICONS=heroicons
WINK_VIEWS_DARK_MODE=true
WINK_VIEWS_CSS_FRAMEWORK=npm
```

### Custom Framework Configuration

```php
'framework' => env('WINK_VIEWS_FRAMEWORK', 'custom'),

'styling' => [
    'icons' => 'none',
    'color_scheme' => 'default',
    'shadows' => false,
],

'assets' => [
    'css_framework' => 'local',
    'js_framework' => 'local',
    'generate_assets' => false,
],
```

## Layout Configuration

### Layout Templates

```php
'layout' => [
    'master' => env('WINK_VIEWS_MASTER_LAYOUT', 'layouts.app'),
    'admin' => env('WINK_VIEWS_ADMIN_LAYOUT', 'layouts.admin'),
    'auth' => env('WINK_VIEWS_AUTH_LAYOUT', 'layouts.auth'),
    'dashboard' => env('WINK_VIEWS_DASHBOARD_LAYOUT', 'layouts.dashboard'),
    'error' => env('WINK_VIEWS_ERROR_LAYOUT', 'layouts.error'),
],
```

### Usage Examples

**Different environments:**
```env
# Development
WINK_VIEWS_MASTER_LAYOUT=layouts.dev
WINK_VIEWS_ADMIN_LAYOUT=layouts.admin-dev

# Production
WINK_VIEWS_MASTER_LAYOUT=layouts.app
WINK_VIEWS_ADMIN_LAYOUT=layouts.admin
```

**Multi-tenant:**
```env
WINK_VIEWS_MASTER_LAYOUT=tenant.layouts.app
WINK_VIEWS_ADMIN_LAYOUT=tenant.layouts.admin
```

## Component Configuration

### Component Settings

```php
'components' => [
    'use_components' => env('WINK_VIEWS_USE_COMPONENTS', true),
    'component_namespace' => env('WINK_VIEWS_COMPONENT_NAMESPACE', 'components'),
    'livewire_integration' => env('WINK_VIEWS_LIVEWIRE', false),
    'alpine_integration' => env('WINK_VIEWS_ALPINE', false),
    'blade_components' => env('WINK_VIEWS_BLADE_COMPONENTS', true),
],
```

### Component Scenarios

**Blade Components Only:**
```env
WINK_VIEWS_USE_COMPONENTS=true
WINK_VIEWS_BLADE_COMPONENTS=true
WINK_VIEWS_LIVEWIRE=false
WINK_VIEWS_ALPINE=false
```

**Livewire Integration:**
```env
WINK_VIEWS_USE_COMPONENTS=true
WINK_VIEWS_LIVEWIRE=true
WINK_VIEWS_ALPINE=true
WINK_VIEWS_COMPONENT_NAMESPACE=livewire
```

**Custom Namespace:**
```env
WINK_VIEWS_COMPONENT_NAMESPACE=ui.components
```

## Feature Configuration

### Core Features

```php
'features' => [
    'pagination' => env('WINK_VIEWS_PAGINATION', true),
    'search' => env('WINK_VIEWS_SEARCH', true),
    'filtering' => env('WINK_VIEWS_FILTERING', true),
    'sorting' => env('WINK_VIEWS_SORTING', true),
    'bulk_actions' => env('WINK_VIEWS_BULK_ACTIONS', true),
    'export' => env('WINK_VIEWS_EXPORT', true),
    'ajax_forms' => env('WINK_VIEWS_AJAX_FORMS', true),
    'modal_dialogs' => env('WINK_VIEWS_MODALS', true),
    'breadcrumbs' => env('WINK_VIEWS_BREADCRUMBS', true),
    'flash_messages' => env('WINK_VIEWS_FLASH_MESSAGES', true),
],
```

### Feature Combinations

**Minimal Features:**
```env
WINK_VIEWS_PAGINATION=true
WINK_VIEWS_SEARCH=false
WINK_VIEWS_AJAX_FORMS=false
WINK_VIEWS_BULK_ACTIONS=false
WINK_VIEWS_EXPORT=false
```

**Full Features:**
```env
WINK_VIEWS_PAGINATION=true
WINK_VIEWS_SEARCH=true
WINK_VIEWS_FILTERING=true
WINK_VIEWS_SORTING=true
WINK_VIEWS_BULK_ACTIONS=true
WINK_VIEWS_EXPORT=true
WINK_VIEWS_AJAX_FORMS=true
WINK_VIEWS_MODALS=true
```

**API-First:**
```env
WINK_VIEWS_AJAX_FORMS=true
WINK_VIEWS_PAGINATION=false
WINK_VIEWS_SEARCH=true
WINK_VIEWS_EXPORT=true
```

## Styling Configuration

### Visual Styling

```php
'styling' => [
    'dark_mode' => env('WINK_VIEWS_DARK_MODE', true),
    'animations' => env('WINK_VIEWS_ANIMATIONS', true),
    'icons' => env('WINK_VIEWS_ICONS', 'bootstrap-icons'),
    'color_scheme' => env('WINK_VIEWS_COLOR_SCHEME', 'blue'),
    'border_radius' => env('WINK_VIEWS_BORDER_RADIUS', 'md'),
    'shadows' => env('WINK_VIEWS_SHADOWS', true),
],
```

### Icon Options

| Value | Description | Framework |
|-------|-------------|-----------|
| `bootstrap-icons` | Bootstrap Icons | Bootstrap |
| `heroicons` | Heroicons | Tailwind |
| `feather` | Feather Icons | Any |
| `lucide` | Lucide Icons | Any |
| `none` | No icons | Custom |

### Color Schemes

| Scheme | Primary Color | Use Case |
|--------|---------------|----------|
| `blue` | Blue tones | Default, professional |
| `green` | Green tones | Health, environment |
| `purple` | Purple tones | Creative, luxury |
| `red` | Red tones | Emergency, alerts |
| `gray` | Gray tones | Minimal, clean |

### Border Radius Options

| Value | CSS Value | Visual Style |
|-------|-----------|--------------|
| `none` | 0 | Sharp corners |
| `sm` | 0.125rem | Subtle rounding |
| `md` | 0.375rem | Moderate rounding |
| `lg` | 0.5rem | Pronounced rounding |
| `xl` | 0.75rem | Very rounded |

## Form Configuration

### Form Behavior

```php
'forms' => [
    'validation_style' => env('WINK_VIEWS_VALIDATION_STYLE', 'inline'),
    'rich_text_editor' => env('WINK_VIEWS_RICH_TEXT_EDITOR', 'tinymce'),
    'date_picker' => env('WINK_VIEWS_DATE_PICKER', 'flatpickr'),
    'file_upload' => env('WINK_VIEWS_FILE_UPLOAD', 'dropzone'),
    'auto_save' => env('WINK_VIEWS_AUTO_SAVE', false),
    'client_validation' => env('WINK_VIEWS_CLIENT_VALIDATION', true),
    'progressive_enhancement' => env('WINK_VIEWS_PROGRESSIVE_ENHANCEMENT', true),
    'csrf_protection' => env('WINK_VIEWS_CSRF_PROTECTION', true),
],
```

### Validation Styles

| Style | Description | When to Use |
|-------|-------------|-------------|
| `inline` | Errors below each field | Real-time feedback |
| `summary` | All errors at top | Simple forms |
| `both` | Inline + summary | Complex forms |

### Rich Text Editors

| Editor | Features | License |
|--------|----------|---------|
| `tinymce` | Full featured | Free/Commercial |
| `ckeditor` | Professional | Free/Commercial |
| `quill` | Modern, lightweight | Open source |
| `none` | Plain textarea | Always free |

### Date Pickers

| Picker | Features | Framework |
|--------|----------|-----------|
| `flatpickr` | Lightweight, flexible | Any |
| `pikaday` | Simple, clean | Any |
| `none` | Native HTML5 | Any |

### File Upload Libraries

| Library | Features | Use Case |
|---------|----------|----------|
| `dropzone` | Drag & drop, preview | Rich uploads |
| `filepond` | Modern, flexible | Advanced features |
| `none` | Native file input | Simple uploads |

## Table Configuration

### Table Behavior

```php
'tables' => [
    'pagination_size' => env('WINK_VIEWS_PAGINATION_SIZE', 15),
    'responsive_breakpoint' => env('WINK_VIEWS_RESPONSIVE_BREAKPOINT', 'md'),
    'sticky_headers' => env('WINK_VIEWS_STICKY_HEADERS', false),
    'row_actions' => env('WINK_VIEWS_ROW_ACTIONS', 'dropdown'),
    'empty_state' => env('WINK_VIEWS_EMPTY_STATE', true),
    'loading_states' => env('WINK_VIEWS_LOADING_STATES', true),
    'column_visibility' => env('WINK_VIEWS_COLUMN_VISIBILITY', false),
],
```

### Pagination Sizes

| Size | Use Case | Performance |
|------|----------|-------------|
| `10` | Mobile-first | Fast |
| `15` | Default | Balanced |
| `25` | Desktop | Good |
| `50` | Power users | Slower |
| `100` | Data analysis | Slowest |

### Row Actions

| Style | Description | Space Usage |
|-------|-------------|-------------|
| `buttons` | Individual buttons | More space |
| `dropdown` | Dropdown menu | Less space |
| `both` | Primary + dropdown | Balanced |

## Asset Configuration

### Asset Management

```php
'assets' => [
    'css_framework' => env('WINK_VIEWS_CSS_FRAMEWORK', 'cdn'),
    'js_framework' => env('WINK_VIEWS_JS_FRAMEWORK', 'cdn'),
    'generate_assets' => env('WINK_VIEWS_GENERATE_ASSETS', true),
    'minify_assets' => env('WINK_VIEWS_MINIFY_ASSETS', false),
    'version_assets' => env('WINK_VIEWS_VERSION_ASSETS', false),
    'combine_assets' => env('WINK_VIEWS_COMBINE_ASSETS', false),
],
```

### Asset Loading Strategies

| Strategy | Description | Pros | Cons |
|----------|-------------|------|------|
| `cdn` | Load from CDN | Fast, cached | External dependency |
| `local` | Local files | Full control | Larger app size |
| `npm` | Build process | Optimized | Build complexity |

### Production Asset Settings

```env
# Production optimizations
WINK_VIEWS_MINIFY_ASSETS=true
WINK_VIEWS_VERSION_ASSETS=true
WINK_VIEWS_COMBINE_ASSETS=true
WINK_VIEWS_CRITICAL_CSS=true
```

## Accessibility Configuration

### WCAG Compliance

```php
'accessibility' => [
    'wcag_compliance' => env('WINK_VIEWS_WCAG_COMPLIANCE', 'AA'),
    'aria_labels' => env('WINK_VIEWS_ARIA_LABELS', true),
    'keyboard_navigation' => env('WINK_VIEWS_KEYBOARD_NAV', true),
    'screen_reader_support' => env('WINK_VIEWS_SCREEN_READER', true),
    'high_contrast_mode' => env('WINK_VIEWS_HIGH_CONTRAST', false),
    'focus_indicators' => env('WINK_VIEWS_FOCUS_INDICATORS', true),
],
```

### Compliance Levels

| Level | Description | Requirements |
|-------|-------------|--------------|
| `A` | Basic accessibility | Essential features |
| `AA` | Standard compliance | Recommended level |
| `AAA` | Enhanced accessibility | Maximum compliance |

### Accessibility Features

| Feature | Purpose | Impact |
|---------|---------|--------|
| ARIA labels | Screen reader support | High |
| Keyboard navigation | Non-mouse users | High |
| Focus indicators | Visual navigation | Medium |
| High contrast | Vision impaired | Medium |

## Performance Configuration

### Performance Optimization

```php
'performance' => [
    'lazy_loading' => env('WINK_VIEWS_LAZY_LOADING', true),
    'image_optimization' => env('WINK_VIEWS_IMAGE_OPTIMIZATION', true),
    'critical_css' => env('WINK_VIEWS_CRITICAL_CSS', false),
    'preload_assets' => env('WINK_VIEWS_PRELOAD_ASSETS', false),
    'progressive_enhancement' => env('WINK_VIEWS_PROGRESSIVE_ENHANCEMENT', true),
],
```

### Performance Strategies

**Development:**
```env
WINK_VIEWS_LAZY_LOADING=false
WINK_VIEWS_CRITICAL_CSS=false
WINK_VIEWS_PRELOAD_ASSETS=false
```

**Production:**
```env
WINK_VIEWS_LAZY_LOADING=true
WINK_VIEWS_IMAGE_OPTIMIZATION=true
WINK_VIEWS_CRITICAL_CSS=true
WINK_VIEWS_PRELOAD_ASSETS=true
```

## Advanced Configuration

### Field Type Mappings

```php
'field_mappings' => [
    'string' => 'text',
    'text' => 'textarea',
    'integer' => 'number',
    'decimal' => 'number',
    'boolean' => 'checkbox',
    'date' => 'date',
    'datetime' => 'datetime-local',
    'email' => 'email',
    'password' => 'password',
    'url' => 'url',
    'uuid' => 'text',
    'enum' => 'select',
    'json' => 'textarea',
],
```

### Relationship Mappings

```php
'relationship_mappings' => [
    'belongsTo' => 'select',
    'hasOne' => 'select',
    'hasMany' => 'multiselect',
    'belongsToMany' => 'multiselect',
    'morphTo' => 'select',
    'morphOne' => 'select',
    'morphMany' => 'multiselect',
],
```

### Custom Field Types

Add custom field mappings:

```php
'field_mappings' => [
    // Default mappings...
    'phone' => 'tel',
    'color' => 'color',
    'range' => 'range',
    'location' => 'text', // Custom handling in templates
],
```

### Path Configuration

```php
'paths' => [
    'stubs' => env('WINK_VIEWS_STUBS_PATH', resource_path('stubs/wink-views')),
    'views' => env('WINK_VIEWS_OUTPUT_PATH', resource_path('views')),
    'components' => env('WINK_VIEWS_COMPONENTS_PATH', resource_path('views/components')),
    'layouts' => env('WINK_VIEWS_LAYOUTS_PATH', resource_path('views/layouts')),
    'assets' => env('WINK_VIEWS_ASSETS_PATH', public_path('assets')),
],
```

## Environment-Specific Settings

### Development Environment

```env
# .env.local
APP_ENV=local
WINK_VIEWS_FRAMEWORK=bootstrap
WINK_VIEWS_AJAX_FORMS=true
WINK_VIEWS_SEARCH=true
WINK_VIEWS_SORTING=true
WINK_VIEWS_EXPORT=true
WINK_VIEWS_MINIFY_ASSETS=false
WINK_VIEWS_GENERATE_COMMENTS=true
WINK_VIEWS_BACKUP_FILES=true
```

### Testing Environment

```env
# .env.testing
APP_ENV=testing
WINK_VIEWS_FRAMEWORK=bootstrap
WINK_VIEWS_CSS_FRAMEWORK=local
WINK_VIEWS_JS_FRAMEWORK=local
WINK_VIEWS_GENERATE_ASSETS=false
WINK_VIEWS_AJAX_FORMS=false
```

### Production Environment

```env
# .env.production
APP_ENV=production
WINK_VIEWS_FRAMEWORK=bootstrap
WINK_VIEWS_CSS_FRAMEWORK=cdn
WINK_VIEWS_MINIFY_ASSETS=true
WINK_VIEWS_VERSION_ASSETS=true
WINK_VIEWS_CRITICAL_CSS=true
WINK_VIEWS_LAZY_LOADING=true
WINK_VIEWS_GENERATE_COMMENTS=false
WINK_VIEWS_BACKUP_FILES=false
```

### Staging Environment

```env
# .env.staging
APP_ENV=staging
WINK_VIEWS_FRAMEWORK=bootstrap
WINK_VIEWS_CSS_FRAMEWORK=cdn
WINK_VIEWS_MINIFY_ASSETS=true
WINK_VIEWS_GENERATE_COMMENTS=false
WINK_VIEWS_BACKUP_FILES=true
```

## Validation and Testing

### Configuration Validation

Check configuration validity:

```bash
# View current configuration
php artisan config:show wink-views

# Test configuration
php artisan wink:views:crud users --dry-run

# Validate paths
php artisan tinker
>>> config('wink-views.paths.stubs')
>>> is_dir(config('wink-views.paths.stubs'))
```

### Testing Configuration Changes

```bash
# Clear configuration cache
php artisan config:clear

# Test with new configuration
php artisan wink:views:crud test_table --dry-run

# Cache configuration for production
php artisan config:cache
```

### Debugging Configuration

Enable debug mode:

```env
APP_DEBUG=true
WINK_VIEWS_DEBUG=true
```

Check configuration loading:

```php
// In a controller or tinker
dd(config('wink-views'));

// Check specific setting
dd(config('wink-views.framework'));

// Check environment variable
dd(env('WINK_VIEWS_FRAMEWORK'));
```

## Best Practices

### 1. Environment Consistency

Use environment variables for all environments:

```bash
# Set default values in config
'framework' => env('WINK_VIEWS_FRAMEWORK', 'bootstrap'),

# Override in .env files
WINK_VIEWS_FRAMEWORK=tailwind
```

### 2. Feature Toggles

Use feature flags for gradual rollouts:

```env
# Enable new features gradually
WINK_VIEWS_BULK_ACTIONS=true
WINK_VIEWS_EXPORT=false  # Enable later
```

### 3. Performance Optimization

Optimize for production:

```env
# Production settings
WINK_VIEWS_MINIFY_ASSETS=true
WINK_VIEWS_VERSION_ASSETS=true
WINK_VIEWS_LAZY_LOADING=true
```

### 4. Accessibility First

Enable accessibility by default:

```env
WINK_VIEWS_WCAG_COMPLIANCE=AA
WINK_VIEWS_ARIA_LABELS=true
WINK_VIEWS_KEYBOARD_NAV=true
```

### 5. Documentation

Document custom configurations:

```php
// config/wink-views.php
return [
    // Custom field mapping for our business domain
    'field_mappings' => [
        'phone' => 'tel',
        'ssn' => 'password', // Sensitive data
    ],
];
```

## Next Steps

- **[Customization Guide](customization.md)** - Modify templates and stubs
- **[Framework Support](frameworks.md)** - Framework-specific configuration
- **[Advanced Features](advanced.md)** - Complex configuration scenarios
- **[Deployment Guide](deployment.md)** - Production configuration

For configuration support:
- **[GitHub Issues](https://github.com/wink/view-generator/issues)** - Report configuration bugs
- **[GitHub Discussions](https://github.com/wink/view-generator/discussions)** - Ask configuration questions