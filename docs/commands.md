# Commands Reference

This document provides complete reference for all Wink View Generator commands, their options, and usage examples.

## Table of Contents

- [Command Overview](#command-overview)
- [Core Commands](#core-commands)
- [Specialized Commands](#specialized-commands)
- [Global Options](#global-options)
- [Usage Examples](#usage-examples)
- [Command Combinations](#command-combinations)
- [Advanced Usage](#advanced-usage)
- [Troubleshooting](#troubleshooting)

## Command Overview

### Command List

| Command | Purpose | Generated Files |
|---------|---------|-----------------|
| `wink:generate-views` | Complete view system | All view types |
| `wink:views:crud` | CRUD views | Index, show, create, edit |
| `wink:views:components` | Reusable components | Component files |
| `wink:views:forms` | Form views | Create, edit forms |
| `wink:views:tables` | Data tables | Index with table |
| `wink:views:layouts` | Layout templates | Layout files |
| `wink:views:generate-all` | Bulk generation | All tables |

### Command Syntax

```bash
php artisan {command} [arguments] [options]
```

## Core Commands

### `wink:generate-views`

Generate a complete view system for a table with all features.

**Signature:**
```bash
php artisan wink:generate-views {table?} [options]
```

**Arguments:**
- `table` - The database table name (optional)

**Options:**
- `--framework=` - UI framework (bootstrap|tailwind|custom)
- `--layout=` - Master layout template
- `--components` - Generate reusable components
- `--ajax` - Include AJAX functionality
- `--auth` - Include authentication views
- `--force` - Overwrite existing files
- `--dry-run` - Preview without creating files
- `--interactive` - Interactive mode with prompts

**Examples:**
```bash
# Basic generation
php artisan wink:generate-views users

# With all features
php artisan wink:generate-views users --framework=bootstrap --components --ajax --auth

# Interactive mode
php artisan wink:generate-views --interactive

# Preview only
php artisan wink:generate-views users --dry-run
```

**Generated Files:**
```
resources/views/users/
├── index.blade.php
├── show.blade.php
├── create.blade.php
├── edit.blade.php
└── partials/
    ├── form.blade.php
    └── table.blade.php
```

---

### `wink:views:crud`

Generate CRUD views (index, show, create, edit) for a specific table.

**Signature:**
```bash
php artisan wink:views:crud {table} [options]
```

**Arguments:**
- `table` - The database table name (required)

**Options:**
- `--framework=` - UI framework (bootstrap|tailwind|custom)
- `--components` - Generate reusable components
- `--ajax` - Include AJAX functionality
- `--search` - Include search functionality
- `--sorting` - Include column sorting
- `--filtering` - Include data filtering
- `--bulk-actions` - Include bulk action capabilities
- `--export` - Include export functionality (CSV, PDF)
- `--pagination=` - Records per page (default: 15)
- `--force` - Overwrite existing files
- `--dry-run` - Preview without creating files

**Examples:**
```bash
# Basic CRUD
php artisan wink:views:crud users

# With search and sorting
php artisan wink:views:crud users --search --sorting

# Complete feature set
php artisan wink:views:crud products --ajax --search --sorting --filtering --bulk-actions --export

# Custom pagination
php artisan wink:views:crud orders --pagination=25
```

**Generated Files:**
- `resources/views/{table}/index.blade.php`
- `resources/views/{table}/show.blade.php`
- `resources/views/{table}/create.blade.php`
- `resources/views/{table}/edit.blade.php`

---

### `wink:views:generate-all`

Generate complete view system for all tables or specified tables.

**Signature:**
```bash
php artisan wink:views:generate-all [options]
```

**Options:**
- `--tables=` - Comma-separated list of tables
- `--exclude=` - Tables to exclude
- `--framework=` - UI framework
- `--ajax` - Include AJAX functionality
- `--components` - Generate reusable components
- `--auth` - Include authentication views
- `--admin` - Include admin layouts
- `--export` - Include export functionality
- `--parallel` - Generate tables in parallel (faster)
- `--force` - Overwrite existing files

**Examples:**
```bash
# Generate for all tables
php artisan wink:views:generate-all

# Specific tables only
php artisan wink:views:generate-all --tables=users,posts,products

# Exclude certain tables
php artisan wink:views:generate-all --exclude=migrations,sessions,password_resets

# With all features
php artisan wink:views:generate-all --framework=tailwind --ajax --components --admin

# Parallel processing for speed
php artisan wink:views:generate-all --parallel
```

## Specialized Commands

### `wink:views:components`

Generate reusable Blade components for forms, tables, modals, and UI elements.

**Signature:**
```bash
php artisan wink:views:components [table?] [options]
```

**Arguments:**
- `table` - Generate components for specific table (optional)

**Options:**
- `--framework=` - UI framework
- `--form-inputs` - Generate form input components
- `--data-tables` - Generate data table components
- `--modals` - Generate modal components
- `--search` - Generate search components
- `--alerts` - Generate alert/notification components
- `--pagination` - Generate pagination components
- `--navigation` - Generate navigation components
- `--all` - Generate all component types
- `--namespace=` - Component namespace (default: components)
- `--force` - Overwrite existing files

**Examples:**
```bash
# All components
php artisan wink:views:components --all

# Specific component types
php artisan wink:views:components --form-inputs --data-tables --modals

# Components for specific table
php artisan wink:views:components users --form-inputs

# Custom namespace
php artisan wink:views:components --all --namespace=ui
```

**Generated Files:**
```
resources/views/components/
├── form/
│   ├── input.blade.php
│   ├── select.blade.php
│   ├── textarea.blade.php
│   └── checkbox.blade.php
├── table/
│   ├── data-table.blade.php
│   ├── sortable-header.blade.php
│   └── pagination.blade.php
├── modal/
│   ├── base.blade.php
│   └── confirm.blade.php
└── alert/
    ├── success.blade.php
    ├── error.blade.php
    └── warning.blade.php
```

---

### `wink:views:forms`

Generate form views and components for create/edit operations.

**Signature:**
```bash
php artisan wink:views:forms {table} [options]
```

**Arguments:**
- `table` - The database table name (required)

**Options:**
- `--framework=` - UI framework
- `--rich-text` - Include rich text editor fields
- `--file-upload` - Include file upload handling
- `--date-picker` - Include date picker components
- `--ajax` - Enable AJAX form submission
- `--validation=` - Validation style (inline|summary|both)
- `--components` - Use blade components for form fields
- `--separate-forms` - Generate separate create/edit forms
- `--force` - Overwrite existing files

**Examples:**
```bash
# Basic forms
php artisan wink:views:forms users

# With rich features
php artisan wink:views:forms products --rich-text --file-upload --date-picker

# AJAX forms with inline validation
php artisan wink:views:forms posts --ajax --validation=inline

# Component-based forms
php artisan wink:views:forms orders --components

# Separate create/edit forms
php artisan wink:views:forms users --separate-forms
```

**Generated Files:**
- `resources/views/{table}/create.blade.php`
- `resources/views/{table}/edit.blade.php`
- `resources/views/{table}/partials/form.blade.php` (if not separate)

---

### `wink:views:tables`

Generate data table views with sorting, filtering, and pagination.

**Signature:**
```bash
php artisan wink:views:tables {table} [options]
```

**Arguments:**
- `table` - The database table name (required)

**Options:**
- `--framework=` - UI framework
- `--sorting` - Include column sorting
- `--filtering` - Include data filtering
- `--search` - Include search functionality
- `--bulk-actions` - Include bulk action capabilities
- `--export` - Include export functionality (CSV, PDF)
- `--ajax` - Enable AJAX table features
- `--responsive` - Make table responsive/mobile-friendly
- `--component` - Generate as reusable component
- `--pagination=` - Records per page
- `--force` - Overwrite existing files

**Examples:**
```bash
# Basic table
php artisan wink:views:tables users

# Full-featured table
php artisan wink:views:tables products --sorting --filtering --search --bulk-actions --export

# AJAX table with custom pagination
php artisan wink:views:tables orders --ajax --pagination=20

# Responsive component table
php artisan wink:views:tables users --responsive --component
```

**Generated Files:**
- `resources/views/{table}/index.blade.php`
- `resources/views/{table}/partials/table.blade.php`
- `resources/views/components/data-table.blade.php` (if component)

---

### `wink:views:layouts`

Generate layout templates, navigation, and page structures.

**Signature:**
```bash
php artisan wink:views:layouts [options]
```

**Options:**
- `--framework=` - UI framework
- `--auth` - Include authentication layouts
- `--admin` - Include admin/dashboard layouts
- `--error` - Include error page layouts
- `--email` - Include email layouts
- `--navigation` - Include navigation components
- `--sidebar` - Include sidebar navigation
- `--breadcrumbs` - Include breadcrumb navigation
- `--footer` - Include footer component
- `--all` - Generate all layout types
- `--force` - Overwrite existing files

**Examples:**
```bash
# Basic app layout
php artisan wink:views:layouts

# Authentication layouts
php artisan wink:views:layouts --auth

# Admin dashboard with sidebar
php artisan wink:views:layouts --admin --sidebar --breadcrumbs

# Complete layout system
php artisan wink:views:layouts --all

# Custom navigation
php artisan wink:views:layouts --navigation --footer
```

**Generated Files:**
```
resources/views/layouts/
├── app.blade.php
├── admin.blade.php
├── auth.blade.php
├── guest.blade.php
└── partials/
    ├── navigation.blade.php
    ├── sidebar.blade.php
    ├── breadcrumbs.blade.php
    └── footer.blade.php
```

## Global Options

### Framework Options

| Framework | Description | Icons | Features |
|-----------|-------------|-------|----------|
| `bootstrap` | Bootstrap 5 | Bootstrap Icons | Complete component set |
| `tailwind` | Tailwind CSS | Heroicons | Utility-first approach |
| `custom` | Framework-agnostic | None | Semantic HTML |

### Common Options

| Option | Type | Description | Default |
|--------|------|-------------|---------|
| `--framework` | string | UI framework | `bootstrap` |
| `--force` | flag | Overwrite existing files | `false` |
| `--dry-run` | flag | Preview without creating | `false` |
| `--ajax` | flag | Include AJAX functionality | `false` |
| `--components` | flag | Generate reusable components | `false` |
| `--search` | flag | Include search functionality | `false` |
| `--sorting` | flag | Include column sorting | `false` |
| `--filtering` | flag | Include data filtering | `false` |
| `--export` | flag | Include export functionality | `false` |
| `--pagination` | integer | Records per page | `15` |

### Validation Options

| Option | Values | Description |
|--------|--------|-------------|
| `--validation` | `inline` | Show validation errors inline |
| `--validation` | `summary` | Show validation summary |
| `--validation` | `both` | Show both inline and summary |

## Usage Examples

### Complete E-commerce Setup

Generate a complete e-commerce admin interface:

```bash
# Generate admin layouts
php artisan wink:views:layouts --admin --sidebar --breadcrumbs

# Generate product management
php artisan wink:views:crud products --framework=bootstrap --ajax --search --sorting --filtering --export --bulk-actions

# Generate order management
php artisan wink:views:crud orders --framework=bootstrap --ajax --search --sorting --export

# Generate customer management
php artisan wink:views:crud customers --framework=bootstrap --search --sorting

# Generate category management
php artisan wink:views:crud categories --framework=bootstrap --ajax

# Generate reusable components
php artisan wink:views:components --all --framework=bootstrap
```

### Blog Platform Setup

Create a complete blog platform:

```bash
# Generate layouts
php artisan wink:views:layouts --all --framework=tailwind

# Generate post management
php artisan wink:views:crud posts --framework=tailwind --ajax --search --sorting --rich-text

# Generate category management
php artisan wink:views:crud categories --framework=tailwind --ajax

# Generate comment management
php artisan wink:views:crud comments --framework=tailwind --search --filtering

# Generate user management
php artisan wink:views:crud users --framework=tailwind --search --sorting --export
```

### SPA-Style Application

Create views optimized for single-page application experience:

```bash
# Generate all views with AJAX
php artisan wink:views:generate-all --framework=tailwind --ajax --components

# Generate enhanced components
php artisan wink:views:components --all --framework=tailwind

# Generate API-friendly forms
php artisan wink:views:forms users --ajax --validation=inline --components
php artisan wink:views:forms posts --ajax --validation=inline --rich-text
```

## Command Combinations

### Development Workflow

```bash
# 1. Start with layouts
php artisan wink:views:layouts --admin --sidebar

# 2. Generate core entities
php artisan wink:views:crud users --framework=bootstrap --search --sorting
php artisan wink:views:crud posts --framework=bootstrap --ajax --rich-text

# 3. Add components for reusability
php artisan wink:views:components --form-inputs --data-tables

# 4. Generate remaining entities
php artisan wink:views:generate-all --exclude=users,posts --framework=bootstrap
```

### Testing and Preview

```bash
# Preview without creating files
php artisan wink:views:crud users --dry-run

# Generate test data
php artisan wink:views:crud users --framework=bootstrap --force

# Compare different frameworks
php artisan wink:views:crud users --framework=tailwind --force
```

## Advanced Usage

### Custom Configuration

Set environment variables for consistent generation:

```bash
# .env
WINK_VIEWS_FRAMEWORK=bootstrap
WINK_VIEWS_USE_COMPONENTS=true
WINK_VIEWS_AJAX_FORMS=true
WINK_VIEWS_PAGINATION_SIZE=20
```

Then generate without specifying options:

```bash
php artisan wink:views:crud users  # Uses config defaults
```

### Conditional Generation

Generate based on environment:

```bash
# Development - with all features
if [ "$APP_ENV" = "local" ]; then
    php artisan wink:views:crud users --ajax --search --sorting --export --force
else
    php artisan wink:views:crud users --search --sorting
fi
```

### Batch Processing

Generate multiple entities efficiently:

```bash
#!/bin/bash
TABLES=("users" "posts" "categories" "comments" "tags")

for table in "${TABLES[@]}"; do
    echo "Generating views for $table..."
    php artisan wink:views:crud "$table" --framework=bootstrap --ajax --search --sorting
done
```

### Integration with CI/CD

Add to deployment script:

```bash
# Generate views in production
php artisan wink:views:generate-all --framework=bootstrap --exclude=test_tables --force

# Clear caches
php artisan view:clear
php artisan config:cache
```

## Troubleshooting

### Common Issues

#### 1. Command Not Found

```bash
# Clear discovery cache
php artisan package:discover --ansi

# Check if package is installed
composer show wink/view-generator
```

#### 2. Table Not Found

```bash
# Check database connection
php artisan migrate:status

# List available tables
php artisan tinker
>>> Schema::getTableNames()
```

#### 3. Permission Errors

```bash
# Fix directory permissions
sudo chown -R www-data:www-data resources/views
sudo chmod -R 755 resources/views
```

#### 4. Template Errors

```bash
# Clear view cache
php artisan view:clear

# Republish templates
php artisan vendor:publish --tag=wink-views-templates --force
```

### Debug Mode

Enable verbose output:

```bash
php artisan wink:views:crud users --dry-run -v
```

View generated content:

```bash
php artisan wink:views:crud users --dry-run | less
```

### Validation

Check generated files:

```bash
# List generated files
find resources/views -name "*.blade.php" -newer /tmp/before_generation

# Validate Blade syntax
php artisan view:cache
```

## Performance Tips

### Parallel Generation

For large applications:

```bash
# Generate multiple tables in parallel
php artisan wink:views:generate-all --parallel --framework=bootstrap
```

### Selective Generation

Only generate what you need:

```bash
# Instead of generating all
php artisan wink:views:generate-all

# Generate specific features
php artisan wink:views:crud users --search --sorting
php artisan wink:views:tables products --export
```

### Caching

Cache configuration for faster generation:

```bash
php artisan config:cache
```

## Next Steps

- **[Configuration Guide](configuration.md)** - Customize command behavior
- **[Customization Guide](customization.md)** - Modify templates and stubs
- **[Framework Support](frameworks.md)** - Framework-specific features
- **[Advanced Features](advanced.md)** - Complex use cases and patterns

For more help:
- **[GitHub Issues](https://github.com/wink/view-generator/issues)** - Report bugs
- **[GitHub Discussions](https://github.com/wink/view-generator/discussions)** - Ask questions