# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the `wink/view-generator` Laravel package - a production-ready Blade template and UI component generator that creates complete CRUD interfaces from database schemas and controller definitions. The package is designed for legacy database modernization projects and rapid admin interface development.

## Development Status

This repository currently contains only the Product Requirements Document (PRD). The actual Laravel package implementation has not yet been started.

## Planned Architecture

Based on the PRD, the package will follow this structure:

### Core Components
- **Service Provider**: `ViewGeneratorServiceProvider.php` - Main Laravel service provider
- **Commands**: Artisan commands for generating different types of views
  - `GenerateViewsCommand.php` - Main view generation command
  - `GenerateCrudViewsCommand.php` - CRUD-specific views
  - `GenerateComponentsCommand.php` - Reusable components
- **Generators**: Core generation logic
  - `AbstractViewGenerator.php` - Base generator class
  - `CrudViewGenerator.php` - CRUD view generation
  - `ComponentGenerator.php` - Component generation
  - `FormGenerator.php` - Form generation
  - `LayoutGenerator.php` - Layout generation
- **Analyzers**: Code analysis for automatic detection
  - `ControllerAnalyzer.php` - Controller structure analysis
  - `ModelAnalyzer.php` - Model relationship analysis
  - `RouteAnalyzer.php` - Route analysis
  - `FieldAnalyzer.php` - Database field analysis

### Template System
- **Bootstrap 5**: Complete template set with Bootstrap components
- **Tailwind CSS**: Utility-first template variants
- **Custom CSS**: Framework-agnostic templates
- **Asset Management**: CSS/JS components for interactivity

### Key Features to Implement
- Multi-framework support (Bootstrap, Tailwind, Custom CSS)
- CRUD interface generation (index, show, create, edit views)
- Advanced form generation with validation display
- Data table components with sorting, filtering, pagination
- AJAX functionality and progressive enhancement
- Accessibility compliance (WCAG 2.1 AA)
- Mobile-responsive design

## Commands to Implement

Once developed, the package will provide these Artisan commands:

```bash
# Main view generation
php artisan wink:generate-views {table?} --framework=bootstrap --components --ajax

# Specialized commands
php artisan wink:views:crud users --framework=tailwind
php artisan wink:views:forms posts --rich-text
php artisan wink:views:tables products --sorting --filtering
php artisan wink:views:layouts --auth
```

## Integration Points

The package will integrate with:
- **Laravel Framework**: Views, routing, validation, authentication
- **wink/generator-core**: Base generation functionality (dependency)
- **wink/controller-generator**: Controller output analysis (integration)
- **Database Schema**: Automatic field type detection
- **Model Relationships**: Foreign key dropdowns, relationship display

## Configuration

Will use `config/wink-views.php` for:
- Framework selection (Bootstrap/Tailwind/Custom)
- Layout template configuration
- Feature toggles (pagination, search, AJAX)
- Styling options (dark mode, icons, animations)
- Form component settings (validation style, rich text editor)

## Quality Standards

All generated code must meet:
- WCAG 2.1 AA accessibility standards
- Mobile-first responsive design
- Cross-browser compatibility
- Performance optimization
- Laravel coding standards and conventions

## Development Notes

- This is a Laravel package, not a standalone application
- Templates should be customizable via stub publishing
- Generated views should integrate seamlessly with existing Laravel applications
- Focus on developer experience with intuitive commands and clear documentation
- Support both rapid prototyping and production-ready interfaces