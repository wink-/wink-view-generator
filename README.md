# Wink View Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wink/view-generator.svg?style=flat-square)](https://packagist.org/packages/wink/view-generator)
[![License](https://img.shields.io/packagist/l/wink/view-generator.svg?style=flat-square)](https://packagist.org/packages/wink/view-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/wink/view-generator.svg?style=flat-square)](https://packagist.org/packages/wink/view-generator)
[![Laravel](https://img.shields.io/badge/Laravel-10%2B%7C11%2B-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)

A powerful Laravel package for generating production-ready Blade templates and UI components from database schemas and controller definitions. Create complete CRUD interfaces, admin dashboards, and reusable components with a single command.

## Features

✨ **Complete CRUD Generation**: Generate index, show, create, edit views with full functionality  
🎨 **Multi-Framework Support**: Bootstrap 5, Tailwind CSS, and custom CSS frameworks  
🧩 **Reusable Components**: Generate Blade components for forms, tables, modals, and more  
🚀 **AJAX & SPA Ready**: Interactive forms and tables with AJAX functionality  
📱 **Mobile Responsive**: All generated views are mobile-first and responsive  
♿ **Accessibility First**: WCAG 2.1 AA compliant templates with proper ARIA labels  
🎯 **Smart Analysis**: Automatically detects field types, relationships, and validation rules  
⚡ **Bulk Generation**: Generate views for multiple tables at once with parallel processing  
🔍 **Advanced Search**: Built-in search, filtering, sorting, and pagination  
📊 **Data Export**: CSV, PDF, and Excel export functionality  
🔐 **Security Ready**: CSRF protection, authorization checks, and input validation  
🌙 **Dark Mode**: Optional dark mode support for modern UIs  
🔧 **Highly Customizable**: Extensive configuration options and template overrides  
📈 **Performance Optimized**: Lazy loading, asset optimization, and caching support  

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Commands Overview](#commands-overview)
- [Configuration](#configuration)
- [Generated File Structure](#generated-file-structure)
- [Framework Support](#framework-support)
- [Examples](#examples)
- [Documentation](#documentation)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or 11.0+
- Composer 2.0+

## Installation

```bash
composer require wink/view-generator
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=wink-views-config
```

Optionally publish templates for customization:

```bash
php artisan vendor:publish --tag=wink-views-templates
```

## Quick Start

Generate complete CRUD views for a table:

```bash
php artisan wink:views:crud users --framework=bootstrap --ajax --search --sorting
```

Generate all views for your application:

```bash
php artisan wink:views:generate-all --framework=tailwind --components --auth
```

## Available Commands

### Main Commands

#### `wink:generate-views`
Generate complete view system for a table with all features.

```bash
php artisan wink:generate-views users --framework=bootstrap --components --ajax --auth
```

**Options:**
- `--framework=bootstrap|tailwind|custom` - UI framework to use
- `--layout=layouts.app` - Master layout template  
- `--components` - Generate reusable components
- `--ajax` - Include AJAX functionality
- `--auth` - Include authentication views
- `--force` - Overwrite existing files
- `--dry-run` - Preview without creating files
- `--interactive` - Interactive mode with prompts

### Specialized Commands

#### `wink:views:crud`
Generate CRUD views (index, show, create, edit) for a specific table.

```bash
php artisan wink:views:crud posts --framework=tailwind --ajax --search --sorting --export
```

**Options:**
- `--search` - Include search functionality
- `--sorting` - Include column sorting  
- `--filtering` - Include data filtering
- `--bulk-actions` - Include bulk action capabilities
- `--export` - Include export functionality (CSV, PDF)
- `--pagination=15` - Records per page

#### `wink:views:components`
Generate reusable Blade components for forms, tables, modals, and UI elements.

```bash
php artisan wink:views:components --framework=bootstrap --all
```

**Options:**
- `--form-inputs` - Generate form input components
- `--data-tables` - Generate data table components  
- `--modals` - Generate modal components
- `--search` - Generate search components
- `--alerts` - Generate alert/notification components
- `--pagination` - Generate pagination components
- `--all` - Generate all component types
- `--namespace=components` - Component namespace

#### `wink:views:forms`
Generate form views and components for create/edit operations.

```bash
php artisan wink:views:forms products --rich-text --file-upload --ajax --validation=inline
```

**Options:**
- `--rich-text` - Include rich text editor fields
- `--file-upload` - Include file upload handling
- `--date-picker` - Include date picker components
- `--ajax` - Enable AJAX form submission
- `--validation=inline|summary|both` - Validation style
- `--components` - Use blade components for form fields
- `--separate-forms` - Generate separate create/edit forms

#### `wink:views:tables`
Generate data table views with sorting, filtering, and pagination.

```bash
php artisan wink:views:tables orders --sorting --filtering --search --bulk-actions --export --responsive
```

**Options:**
- `--sorting` - Include column sorting
- `--filtering` - Include data filtering
- `--search` - Include search functionality
- `--bulk-actions` - Include bulk action capabilities
- `--export` - Include export functionality (CSV, PDF)
- `--ajax` - Enable AJAX table features
- `--responsive` - Make table responsive/mobile-friendly
- `--component` - Generate as reusable component

#### `wink:views:layouts`
Generate layout templates, navigation, and page structures.

```bash
php artisan wink:views:layouts --framework=bootstrap --auth --admin --navigation --sidebar --breadcrumbs
```

**Options:**
- `--auth` - Include authentication layouts
- `--admin` - Include admin/dashboard layouts
- `--error` - Include error page layouts
- `--email` - Include email layouts
- `--navigation` - Include navigation components
- `--sidebar` - Include sidebar navigation
- `--breadcrumbs` - Include breadcrumb navigation
- `--footer` - Include footer component
- `--all` - Generate all layout types

#### `wink:views:generate-all`
Generate complete view system for all tables or specified tables.

```bash
php artisan wink:views:generate-all --framework=tailwind --ajax --components --auth --admin
```

**Options:**
- `--tables=users,posts,products` - Comma-separated list of tables
- `--exclude=migrations,sessions` - Tables to exclude
- `--ajax` - Include AJAX functionality
- `--components` - Generate reusable components
- `--auth` - Include authentication views
- `--admin` - Include admin layouts
- `--export` - Include export functionality
- `--parallel` - Generate tables in parallel (faster)

## Configuration

The package configuration file is located at `config/wink-views.php`. Here you can customize:

### Framework Settings
```php
'framework' => env('WINK_VIEWS_FRAMEWORK', 'bootstrap'),
```

### Layout Configuration
```php
'layout' => [
    'master' => 'layouts.app',
    'admin' => 'layouts.admin',
    'auth' => 'layouts.auth',
],
```

### Feature Toggles
```php
'features' => [
    'pagination' => true,
    'search' => true,
    'filtering' => true,
    'sorting' => true,
    'bulk_actions' => false,
    'export' => false,
    'ajax_forms' => true,
],
```

### Form Configuration
```php
'forms' => [
    'validation_style' => 'inline',
    'rich_text_editor' => 'tinymce',
    'date_picker' => 'flatpickr',
    'file_upload' => 'dropzone',
],
```

## Usage Examples

### Basic CRUD Generation

```bash
# Generate basic CRUD views
php artisan wink:views:crud users

# Generate with Bootstrap framework and AJAX
php artisan wink:views:crud users --framework=bootstrap --ajax

# Generate with all features
php artisan wink:views:crud products --ajax --search --sorting --filtering --bulk-actions --export
```

### Component Generation

```bash
# Generate all components
php artisan wink:views:components --all

# Generate specific component types
php artisan wink:views:components --form-inputs --data-tables --modals

# Generate components for a specific table
php artisan wink:views:components users --form-inputs
```

### Layout Generation

```bash
# Generate basic app layout
php artisan wink:views:layouts

# Generate authentication layouts
php artisan wink:views:layouts --auth

# Generate admin dashboard layouts
php artisan wink:views:layouts --admin --sidebar --breadcrumbs

# Generate all layouts and navigation
php artisan wink:views:layouts --all
```

### Bulk Generation

```bash
# Generate for all tables
php artisan wink:views:generate-all

# Generate for specific tables with features
php artisan wink:views:generate-all --tables=users,posts,products --ajax --components

# Generate everything with Tailwind
php artisan wink:views:generate-all --framework=tailwind --auth --admin --export
```

## Generated File Structure

When you run the commands, files are generated in the following structure:

```
resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── admin.blade.php
│   └── auth.blade.php
├── components/
│   ├── form/
│   │   ├── input.blade.php
│   │   ├── select.blade.php
│   │   └── textarea.blade.php
│   ├── table/
│   │   ├── data-table.blade.php
│   │   └── sortable-header.blade.php
│   └── modal/
│       ├── base.blade.php
│       └── confirm.blade.php
├── users/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── partials/
│       ├── form.blade.php
│       ├── table.blade.php
│       └── search-form.blade.php
└── errors/
    ├── 404.blade.php
    ├── 500.blade.php
    └── 503.blade.php
```

## Integration with Controllers

After generating views, create corresponding controllers:

```bash
php artisan make:controller UserController --resource
```

Add routes to `web.php`:

```php
Route::resource('users', UserController::class);
```

Example controller structure:

```php
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Sorting
        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'asc');
            $query->orderBy($request->sort, $direction);
        }
        
        $users = $query->paginate(15);
        
        return view('users.index', compact('users'));
    }
    
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }
    
    public function create()
    {
        return view('users.create');
    }
    
    public function store(StoreUserRequest $request)
    {
        User::create($request->validated());
        
        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }
    
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }
    
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        
        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }
    
    public function destroy(User $user)
    {
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
```

## Framework-Specific Setup

### Bootstrap 5
Include Bootstrap CSS and JS in your layout:

```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
```

### Tailwind CSS
Configure Tailwind to include the generated view paths:

```javascript
// tailwind.config.js
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
  ],
  // ...
}
```

### AJAX Features
Include CSRF token in your layout's `<head>`:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

## Advanced Features

### Export Functionality
Install required packages for export features:

```bash
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
```

### Rich Text Editing
Include TinyMCE or CKEditor for rich text fields:

```html
<script src="https://cdn.tiny.cloud/1/your-api-key/tinymce/6/tinymce.min.js"></script>
```

### File Uploads
Include Dropzone.js for enhanced file uploads:

```html
<script src="https://unpkg.com/dropzone@6/dist/min/dropzone.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@6/dist/min/dropzone.min.css">
```

## Commands Overview

### Core Commands

| Command | Description | Usage |
|---------|-------------|-------|
| `wink:generate-views` | Generate complete view system | `php artisan wink:generate-views {table}` |
| `wink:views:crud` | Generate CRUD views | `php artisan wink:views:crud {table}` |
| `wink:views:components` | Generate reusable components | `php artisan wink:views:components` |
| `wink:views:forms` | Generate form views | `php artisan wink:views:forms {table}` |
| `wink:views:tables` | Generate data tables | `php artisan wink:views:tables {table}` |
| `wink:views:layouts` | Generate layout templates | `php artisan wink:views:layouts` |
| `wink:views:generate-all` | Generate for all tables | `php artisan wink:views:generate-all` |

### Common Options

| Option | Description | Example |
|--------|-------------|---------|
| `--framework` | UI framework (bootstrap\|tailwind\|custom) | `--framework=bootstrap` |
| `--ajax` | Include AJAX functionality | `--ajax` |
| `--components` | Generate reusable components | `--components` |
| `--search` | Include search functionality | `--search` |
| `--sorting` | Include column sorting | `--sorting` |
| `--filtering` | Include data filtering | `--filtering` |
| `--export` | Include export features | `--export` |
| `--force` | Overwrite existing files | `--force` |
| `--dry-run` | Preview without creating files | `--dry-run` |

## Framework Support

### Bootstrap 5
Complete support with responsive design, animations, and all Bootstrap components.

```bash
php artisan wink:views:crud users --framework=bootstrap
```

**Features:**
- Bootstrap Icons integration
- Responsive breakpoints
- Dark mode support
- Animation classes
- Form validation styling

### Tailwind CSS
Modern utility-first framework with Heroicons integration.

```bash
php artisan wink:views:crud users --framework=tailwind
```

**Features:**
- Heroicons integration
- JIT compilation ready
- Dark mode variants
- Custom color schemes
- Component composition

### Custom Framework
Framework-agnostic HTML with semantic classes for your own CSS framework.

```bash
php artisan wink:views:crud users --framework=custom
```

**Features:**
- Semantic HTML structure
- BEM-style class naming
- No framework dependencies
- Easy to customize

## Examples

### 1. Basic CRUD Generation

Generate a complete CRUD interface for a `users` table:

```bash
php artisan wink:views:crud users --framework=bootstrap --ajax --search --sorting
```

**Generated files:**
- `resources/views/users/index.blade.php` - Data table with search and sorting
- `resources/views/users/show.blade.php` - Detail view
- `resources/views/users/create.blade.php` - Create form
- `resources/views/users/edit.blade.php` - Edit form

### 2. Admin Dashboard Generation

Generate a complete admin dashboard:

```bash
php artisan wink:views:layouts --admin --sidebar --breadcrumbs
php artisan wink:views:generate-all --framework=bootstrap --components --ajax
```

### 3. Component-Based Architecture

Generate reusable components:

```bash
php artisan wink:views:components --all --framework=tailwind
```

**Generated components:**
- Form input components
- Data table components
- Modal dialogs
- Search forms
- Alert notifications

### 4. API Integration

Generate views with AJAX for SPA-like experience:

```bash
php artisan wink:views:crud products --ajax --framework=tailwind --export
```

## Documentation

### Complete Documentation

- **[Installation Guide](docs/installation.md)** - Detailed installation and setup
- **[Getting Started](docs/getting-started.md)** - Quick start tutorial
- **[Commands Reference](docs/commands.md)** - Complete command documentation
- **[Configuration](docs/configuration.md)** - Configuration options guide
- **[Customization](docs/customization.md)** - Template and stub customization
- **[Framework Support](docs/frameworks.md)** - Multi-framework support guide
- **[Advanced Features](docs/advanced.md)** - Advanced features and patterns

### Developer Documentation

- **[Architecture](docs/architecture.md)** - Package architecture and design patterns
- **[Extending](docs/extending.md)** - Guide for extending generators and analyzers
- **[Testing](docs/testing.md)** - Testing guide and best practices
- **[Deployment](docs/deployment.md)** - Production deployment considerations

### Examples

- **[Basic CRUD](examples/basic-crud/)** - Simple CRUD example
- **[Admin Panel](examples/admin-panel/)** - Complete admin panel example
- **[Custom Framework](examples/custom-framework/)** - Custom CSS framework example
- **[Laravel Integration](examples/integration/)** - Integration with existing Laravel apps

## Security

This package follows Laravel security best practices:

- **CSRF Protection**: All forms include CSRF tokens
- **Authorization**: Generated views include policy checks
- **Input Validation**: Client and server-side validation
- **XSS Prevention**: Proper output escaping
- **SQL Injection**: Uses Eloquent ORM for database queries

If you discover a security vulnerability, please send an e-mail to security@winktools.dev.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Support

- **Documentation**: [Full documentation](https://docs.winktools.dev/view-generator)
- **Issues**: [GitHub Issues](https://github.com/wink/view-generator/issues)
- **Discussions**: [GitHub Discussions](https://github.com/wink/view-generator/discussions)

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

---

**Generated with ❤️ by Wink Tools**