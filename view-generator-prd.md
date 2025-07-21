# Product Requirements Document: Wink View Generator

## 1. Product Overview

### 1.1 Product Name
`wink/view-generator`

### 1.2 Purpose
Generate production-ready Blade templates and UI components from database schemas and controller definitions, creating complete CRUD interfaces for legacy database modernization projects.

### 1.3 Target Users
- Laravel developers building admin interfaces
- Enterprise teams creating data management tools
- Agencies delivering full-stack applications quickly
- Solo developers needing complete UI scaffolding

### 1.4 Success Metrics
- 90% reduction in view creation time
- Mobile-responsive generated interfaces
- Accessibility compliant (WCAG 2.1 AA)
- Framework-agnostic component architecture

## 2. Core Requirements

### 2.1 View Types

#### 2.1.1 CRUD Interface Views
**Index View**: Data table with search, filter, and pagination
**Show View**: Detail view with related data display
**Create View**: Form for new record creation
**Edit View**: Form for record modification

#### 2.1.2 Component Views
**Form Components**: Reusable input components
**Table Components**: Sortable, filterable data tables
**Modal Components**: Create/edit/delete modals
**Search Components**: Advanced search interfaces

#### 2.1.3 Layout Views
**Master Layout**: Application shell with navigation
**Dashboard Layout**: Admin interface layout
**Authentication Layout**: Login/register pages
**Error Pages**: 404, 500, 403 custom pages

### 2.2 UI Framework Support

#### 2.2.1 Bootstrap 5
- Bootstrap classes and components
- Bootstrap Icons integration
- Responsive grid system
- Bootstrap JavaScript components

#### 2.2.2 Tailwind CSS
- Tailwind utility classes
- Tailwind UI components
- Responsive design utilities
- Dark mode support

#### 2.2.3 Custom CSS Framework
- Framework-agnostic HTML structure
- CSS custom properties
- Semantic HTML elements
- Progressive enhancement

### 2.3 Advanced Features

#### 2.3.1 Form Generation
- Automatic field type detection
- Validation error display
- CSRF protection
- File upload handling
- Rich text editors (TinyMCE, CKEditor)

#### 2.3.2 Data Tables
- Server-side pagination
- Column sorting
- Advanced filtering
- Export functionality (CSV, PDF)
- Bulk actions

#### 2.3.3 Relationship Display
- Foreign key dropdowns
- Many-to-many checkboxes
- Related data tables
- Inline editing

#### 2.3.4 Interactive Features
- AJAX form submission
- Real-time validation
- Auto-save functionality
- Progressive web app features

## 3. Technical Specifications

### 3.1 Package Structure
```
src/
├── ViewGeneratorServiceProvider.php
├── Commands/
│   ├── GenerateViewsCommand.php
│   ├── GenerateCrudViewsCommand.php
│   └── GenerateComponentsCommand.php
├── Generators/
│   ├── AbstractViewGenerator.php
│   ├── CrudViewGenerator.php
│   ├── ComponentGenerator.php
│   ├── FormGenerator.php
│   └── LayoutGenerator.php
├── Analyzers/
│   ├── ControllerAnalyzer.php
│   ├── ModelAnalyzer.php
│   ├── RouteAnalyzer.php
│   └── FieldAnalyzer.php
├── Templates/
│   ├── bootstrap/
│   │   ├── layouts/
│   │   ├── crud/
│   │   ├── components/
│   │   └── forms/
│   ├── tailwind/
│   │   ├── layouts/
│   │   ├── crud/
│   │   ├── components/
│   │   └── forms/
│   └── custom/
├── Assets/
│   ├── js/
│   │   ├── components.js
│   │   ├── forms.js
│   │   └── tables.js
│   └── css/
│       ├── components.css
│       └── utilities.css
└── Config/
    └── ViewConfig.php
```

### 3.2 Dependencies
- `illuminate/support: ^10.0|^11.0`
- `illuminate/view: ^10.0|^11.0`
- `wink/generator-core: ^1.0`
- Integration with `wink/controller-generator` outputs

### 3.3 Configuration System
```php
// config/wink-views.php
return [
    'framework' => 'bootstrap', // bootstrap|tailwind|custom
    'layout' => [
        'master' => 'layouts.app',
        'admin' => 'layouts.admin',
        'auth' => 'layouts.auth',
    ],
    'components' => [
        'use_components' => true,
        'component_namespace' => 'components',
        'livewire_integration' => false,
    ],
    'features' => [
        'pagination' => true,
        'search' => true,
        'filtering' => true,
        'sorting' => true,
        'bulk_actions' => true,
        'export' => true,
        'ajax_forms' => true,
    ],
    'styling' => [
        'dark_mode' => true,
        'animations' => true,
        'icons' => 'bootstrap-icons', // bootstrap-icons|heroicons|feather
    ],
    'forms' => [
        'validation_style' => 'inline', // inline|summary|both
        'rich_text_editor' => 'tinymce', // tinymce|ckeditor|none
        'date_picker' => 'flatpickr',
        'file_upload' => 'dropzone',
    ],
];
```

## 4. User Stories

### 4.1 As a Developer Building Admin Interface
**Story**: I need complete CRUD views for managing user data
**Acceptance Criteria**:
- Generate index table with search and pagination
- Create/edit forms with proper validation display
- Show view with related data
- Responsive design that works on mobile

### 4.2 As an Enterprise Developer
**Story**: I need views that match company design system
**Acceptance Criteria**:
- Use custom stub templates with company branding
- Include accessibility features
- Generate consistent navigation structure
- Support custom CSS framework

### 4.3 As a Full-Stack Developer
**Story**: I need interactive views with modern UX
**Acceptance Criteria**:
- AJAX form submission with loading states
- Real-time validation feedback
- Modal dialogs for quick actions
- Progressive enhancement for performance

## 5. Commands Specification

### 5.1 Main Command
```bash
php artisan wink:generate-views {table?}
    {--framework=bootstrap}    # bootstrap|tailwind|custom
    {--layout=app}            # Master layout template
    {--components}            # Generate reusable components
    {--ajax}                  # Include AJAX functionality
    {--auth}                  # Include authentication views
    {--force}                 # Overwrite existing files
    {--dry-run}               # Preview without creating files
```

### 5.2 Specialized Commands
```bash
# Generate complete CRUD views
php artisan wink:views:crud users --framework=tailwind --components

# Generate form components only
php artisan wink:views:forms posts --rich-text --file-upload

# Generate data table views
php artisan wink:views:tables products --sorting --filtering --export

# Generate layout templates
php artisan wink:views:layouts --framework=bootstrap --auth

# Generate all views from controllers
php artisan wink:views:generate-all --framework=tailwind --ajax
```

## 6. Generated View Examples

### 6.1 Index View (Bootstrap)
```blade
@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Users</h5>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Add User
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Search and Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       placeholder="Search users..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none">
                                            Name
                                            @if(request('sort') == 'name')
                                                <i class="bi bi-chevron-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th width="200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $user->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('users.show', $user) }}" 
                                                   class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', $user) }}" 
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteUser({{ $user->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                No users found
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
@include('users.partials.delete-modal')
@endsection

@push('scripts')
<script>
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting user');
            }
        });
    }
}
</script>
@endpush
```

### 6.2 Form Component (Tailwind)
```blade
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'help' => '',
    'options' => []
])

<div class="mb-6">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @if($type === 'select')
        <select name="{{ $name }}" 
                id="{{ $name }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error($name) border-red-500 @enderror"
                {{ $required ? 'required' : '' }}>
            <option value="">Choose {{ strtolower($label) }}...</option>
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @elseif($type === 'textarea')
        <textarea name="{{ $name }}" 
                  id="{{ $name }}"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error($name) border-red-500 @enderror"
                  placeholder="{{ $placeholder }}"
                  {{ $required ? 'required' : '' }}>{{ old($name, $value) }}</textarea>
    @else
        <input type="{{ $type }}" 
               name="{{ $name }}" 
               id="{{ $name }}"
               value="{{ old($name, $value) }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error($name) border-red-500 @enderror"
               placeholder="{{ $placeholder }}"
               {{ $required ? 'required' : '' }}>
    @endif

    @if($help)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

## 7. Integration Requirements

### 7.1 Controller Integration
- Read controller method signatures
- Detect form validation rules
- Map controller routes to view actions
- Handle resource route conventions

### 7.2 Model Integration
- Use model attributes for form fields
- Detect relationships for form dropdowns
- Apply model validation rules to forms
- Handle model casts for display formatting

### 7.3 Asset Integration
- Generate accompanying CSS/JS files
- Include framework-specific assets
- Handle asset compilation (Vite, Mix)
- CDN vs local asset options

## 8. Quality Requirements

### 8.1 Accessibility
- WCAG 2.1 AA compliance
- Proper ARIA labels and roles
- Keyboard navigation support
- Screen reader compatibility

### 8.2 Performance
- Optimized HTML structure
- Lazy loading for images
- Efficient CSS/JS loading
- Progressive enhancement

### 8.3 Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsiveness
- Progressive web app features
- Graceful degradation

## 9. Testing Requirements

### 9.1 Generated View Tests
- Blade template compilation tests
- Component rendering tests
- Form submission tests
- JavaScript functionality tests

### 9.2 Visual Regression Testing
- Screenshot comparison tests
- Cross-browser testing
- Mobile device testing
- Accessibility testing

## 10. Documentation Requirements

### 10.1 Template Documentation
- Stub template customization guide
- Component usage examples
- Styling customization guide
- JavaScript integration patterns

### 10.2 Generated Code Documentation
- View documentation comments
- Component prop documentation
- CSS class documentation
- JavaScript API documentation

## 11. Success Criteria

### 11.1 Functional Success
- Generate working views for any Laravel controller
- Support multiple UI frameworks
- Create accessible, responsive interfaces
- Integrate seamlessly with Laravel features

### 11.2 Quality Success
- WCAG 2.1 AA accessibility compliance
- Mobile-first responsive design
- Cross-browser compatibility
- Performance optimization

### 11.3 Developer Experience
- Intuitive command interface
- Customizable templates
- Clear documentation
- Easy framework switching

## 12. Future Enhancements

### 12.1 Phase 2 Features
- Vue.js/React component generation
- Livewire component integration
- Inertia.js support
- PWA features

### 12.2 Phase 3 Features
- Headless UI integration
- Design system integration
- Real-time updates (WebSockets)
- Advanced animations and interactions