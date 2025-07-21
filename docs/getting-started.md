# Getting Started Guide

This quick start guide will help you generate your first views with the Wink View Generator package. Follow along to create a complete CRUD interface in minutes.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Your First CRUD Interface](#your-first-crud-interface)
- [Understanding Generated Files](#understanding-generated-files)
- [Creating Controllers](#creating-controllers)
- [Setting Up Routes](#setting-up-routes)
- [Testing Your Interface](#testing-your-interface)
- [Next Steps](#next-steps)

## Prerequisites

Before starting, ensure you have:

1. **Installed the package** - See [Installation Guide](installation.md)
2. **A Laravel application** - Laravel 10.0+ recommended
3. **A database table** - We'll use a `users` table for this example
4. **An Eloquent model** - `User` model for the table

If you need to create a model and migration:

```bash
php artisan make:model User -m
```

## Your First CRUD Interface

Let's create a complete CRUD interface for managing users.

### Step 1: Generate CRUD Views

Run the following command to generate all CRUD views:

```bash
php artisan wink:views:crud users --framework=bootstrap --ajax --search --sorting
```

**Command breakdown:**
- `users` - The table/model name
- `--framework=bootstrap` - Use Bootstrap 5 styling
- `--ajax` - Include AJAX functionality
- `--search` - Add search functionality
- `--sorting` - Include column sorting

### Step 2: What Was Generated

The command creates these files:

```
resources/views/users/
├── index.blade.php      # List all users with search/sort
├── show.blade.php       # Display single user details
├── create.blade.php     # Create new user form
├── edit.blade.php       # Edit existing user form
└── partials/
    ├── form.blade.php   # Shared form fields
    └── table.blade.php  # Data table component
```

### Step 3: Preview Generated Content

Let's look at what was generated. The index view includes:

- **Responsive data table** with user listing
- **Search functionality** to filter users
- **Column sorting** for all fields
- **Pagination** for large datasets
- **Bulk actions** for managing multiple users
- **AJAX-powered** interactions
- **Mobile-responsive** design

## Understanding Generated Files

### Index View (`index.blade.php`)

The main listing page includes:

```php
@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="container-fluid">
    <!-- Page header with breadcrumbs -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Users</h1>
        <!-- Create button -->
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Add User
        </a>
    </div>

    <!-- Search and filters -->
    <div class="card shadow">
        <div class="card-body">
            <!-- Search form -->
            <!-- Data table -->
            <!-- Pagination -->
        </div>
    </div>
</div>
@endsection
```

**Key features:**
- Extends your app layout
- Includes search form
- Responsive data table
- Pagination controls
- Action buttons

### Create/Edit Forms

Form views include:

```php
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form method="POST" action="{{ route('users.store') }}" id="userForm">
        @csrf
        <!-- Form fields are automatically generated based on your model -->
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        <!-- More fields... -->
        
        <button type="submit" class="btn btn-primary">Save User</button>
    </form>
</div>
@endsection
```

**Key features:**
- CSRF protection
- Validation error display
- Field type detection
- Required field indicators
- AJAX form submission (if enabled)

## Creating Controllers

Generate a resource controller to handle the CRUD operations:

```bash
php artisan make:controller UserController --resource
```

Update the controller with basic CRUD logic:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }
        
        // Sorting
        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'asc');
            $query->orderBy($request->sort, $direction);
        } else {
            $query->latest();
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
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);
        
        $validated['password'] = bcrypt($validated['password']);
        
        User::create($validated);
        
        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }
    
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }
    
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);
        
        $user->update($validated);
        
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

## Setting Up Routes

Add the resource routes to your `routes/web.php`:

```php
<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// User management routes
Route::resource('users', UserController::class);

// Optional: Add bulk action routes
Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
Route::post('users/export', [UserController::class, 'export'])->name('users.export');
```

Check your routes:

```bash
php artisan route:list --name=users
```

## Testing Your Interface

### 1. Start the Development Server

```bash
php artisan serve
```

### 2. Visit the Users Index

Navigate to `http://localhost:8000/users` to see your generated interface.

### 3. Test Functionality

Try these features:

- **Browse users** - View the data table
- **Search users** - Use the search box
- **Sort columns** - Click column headers
- **Create user** - Click "Add User" button
- **Edit user** - Click edit button in table
- **Delete user** - Click delete button

### 4. Check Mobile Responsiveness

View the interface on different screen sizes to see the responsive design.

## Adding More Features

### Enable Components

Generate reusable components:

```bash
php artisan wink:views:components --form-inputs --data-tables
```

### Add Export Functionality

Install export packages:

```bash
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
```

Generate views with export:

```bash
php artisan wink:views:crud users --export --framework=bootstrap
```

### Create Admin Layout

Generate admin layouts:

```bash
php artisan wink:views:layouts --admin --sidebar --breadcrumbs
```

## Working with Different Models

### Blog Posts Example

Create a posts model and generate CRUD:

```bash
php artisan make:model Post -m
php artisan wink:views:crud posts --framework=tailwind --ajax --search --export
```

### Products with Categories

For related models:

```bash
php artisan make:model Product -m
php artisan make:model Category -m
php artisan wink:views:crud products --components --filtering --sorting
```

## Customizing Generated Views

### Override Templates

Publish templates to customize:

```bash
php artisan vendor:publish --tag=wink-views-templates
```

Edit templates in `resources/stubs/wink-views/`.

### Modify Configuration

Adjust `config/wink-views.php`:

```php
return [
    'framework' => 'bootstrap',
    'features' => [
        'pagination' => true,
        'search' => true,
        'sorting' => true,
        'ajax_forms' => true,
    ],
    'tables' => [
        'pagination_size' => 20,
    ],
];
```

## Common Patterns

### 1. Admin Dashboard

```bash
# Generate admin layouts
php artisan wink:views:layouts --admin --sidebar

# Generate all admin CRUD interfaces
php artisan wink:views:generate-all --framework=bootstrap --admin --components
```

### 2. API-First Application

```bash
# Generate with AJAX for SPA-like experience
php artisan wink:views:crud users --ajax --framework=tailwind
```

### 3. Multi-Tenant Application

```bash
# Generate tenant-specific views
php artisan wink:views:crud tenant_users --components --filtering
```

## Best Practices

### 1. Model Preparation

Ensure your models have:

```php
class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    
    protected $hidden = ['password'];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    // Define relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
```

### 2. Validation Rules

Create form requests for validation:

```bash
php artisan make:request StoreUserRequest
php artisan make:request UpdateUserRequest
```

### 3. Authorization

Use Laravel policies:

```bash
php artisan make:policy UserPolicy --model=User
```

### 4. Consistent Styling

Choose one framework and stick with it:

```env
WINK_VIEWS_FRAMEWORK=bootstrap
```

## Troubleshooting

### Common Issues

1. **Views not found**: Check that routes are correctly defined
2. **Styling issues**: Ensure CSS framework is included in layout
3. **AJAX not working**: Check that CSRF token is included in meta tags
4. **Search not working**: Verify search logic in controller

### Debug Tips

```bash
# Clear caches
php artisan view:clear
php artisan config:clear

# Check generated files
ls -la resources/views/users/

# Test routes
php artisan route:list --name=users
```

## Next Steps

Now that you have a working CRUD interface:

1. **Explore [Commands Reference](commands.md)** - Learn all available commands
2. **Read [Configuration Guide](configuration.md)** - Customize package behavior
3. **Check [Framework Support](frameworks.md)** - Framework-specific features
4. **Review [Advanced Features](advanced.md)** - Complex use cases
5. **Browse [Examples](../examples/)** - Real-world examples

## Support

Need help getting started?

- **Check [Installation Guide](installation.md)** for setup issues
- **Browse [GitHub Issues](https://github.com/wink/view-generator/issues)** for common problems
- **Join [GitHub Discussions](https://github.com/wink/view-generator/discussions)** for questions
- **Read [FAQ](advanced.md#faq)** for frequently asked questions

Happy coding! 🚀