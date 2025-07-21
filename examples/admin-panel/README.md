# Admin Panel Example

This example demonstrates how to create a complete admin dashboard using the Wink View Generator package with multiple related entities, role-based access control, and advanced features.

## Overview

We'll create a comprehensive e-commerce admin panel with:

- **User Management** - Manage customers and admin users
- **Product Management** - Products with categories and inventory
- **Order Management** - Order processing and tracking
- **Category Management** - Product categories and hierarchies
- **Dashboard** - Analytics and quick overview
- **Role-based Access** - Different permissions for different user types
- **Admin Layout** - Professional admin interface with sidebar navigation

## Features Included

- ✅ **Admin Dashboard Layout** with sidebar navigation
- ✅ **Multi-entity CRUD** (Users, Products, Orders, Categories)
- ✅ **Advanced Search & Filtering** across all entities
- ✅ **Bulk Operations** (delete, export, status updates)
- ✅ **Role-based Access Control** with Laravel policies
- ✅ **AJAX-powered** interactions for smooth UX
- ✅ **Export Functionality** (CSV, Excel) for all data
- ✅ **Responsive Design** that works on all devices
- ✅ **Real-time Statistics** on dashboard
- ✅ **Relationship Management** between entities

## Prerequisites

- Laravel 10.0+ application
- Wink View Generator package installed
- Database connection configured
- Authentication system in place

## Step-by-Step Implementation

### 1. Create Models and Migrations

First, create all the required models and migrations:

```bash
# Create models with migrations
php artisan make:model User -m
php artisan make:model Product -m
php artisan make:model Category -m
php artisan make:model Order -m
php artisan make:model OrderItem -m
```

### 2. Configure Migrations

#### Users Migration
```php
// database/migrations/create_users_table.php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->enum('role', ['admin', 'customer'])->default('customer');
    $table->string('phone')->nullable();
    $table->text('address')->nullable();
    $table->string('avatar')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_login_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
    
    $table->index(['role', 'is_active']);
    $table->index('email');
});
```

#### Categories Migration
```php
// database/migrations/create_categories_table.php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('image')->nullable();
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
    
    $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
    $table->index(['parent_id', 'is_active']);
    $table->index('slug');
});
```

#### Products Migration
```php
// database/migrations/create_products_table.php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description');
    $table->text('short_description')->nullable();
    $table->string('sku')->unique();
    $table->decimal('price', 10, 2);
    $table->decimal('sale_price', 10, 2)->nullable();
    $table->integer('quantity')->default(0);
    $table->string('image')->nullable();
    $table->json('gallery')->nullable();
    $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
    $table->boolean('is_featured')->default(false);
    $table->decimal('weight', 8, 2)->nullable();
    $table->json('attributes')->nullable(); // Size, color, etc.
    $table->foreignId('category_id')->constrained()->onDelete('restrict');
    $table->timestamps();
    
    $table->index(['status', 'is_featured']);
    $table->index(['category_id', 'status']);
    $table->index('sku');
});
```

#### Orders Migration
```php
// database/migrations/create_orders_table.php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('status', [
        'pending', 'confirmed', 'processing', 
        'shipped', 'delivered', 'cancelled', 'refunded'
    ])->default('pending');
    $table->decimal('subtotal', 10, 2);
    $table->decimal('tax_amount', 10, 2)->default(0);
    $table->decimal('shipping_amount', 10, 2)->default(0);
    $table->decimal('discount_amount', 10, 2)->default(0);
    $table->decimal('total_amount', 10, 2);
    $table->json('billing_address');
    $table->json('shipping_address');
    $table->string('payment_method')->nullable();
    $table->string('payment_status')->default('pending');
    $table->text('notes')->nullable();
    $table->timestamp('shipped_at')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
    $table->index('order_number');
    $table->index('status');
});
```

#### Order Items Migration
```php
// database/migrations/create_order_items_table.php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained()->onDelete('restrict');
    $table->string('product_name'); // Store product name at time of order
    $table->string('product_sku');
    $table->decimal('price', 10, 2);
    $table->integer('quantity');
    $table->decimal('total', 10, 2);
    $table->timestamps();
    
    $table->index(['order_id', 'product_id']);
});
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Generate Admin Layout

Create the admin layout with sidebar navigation:

```bash
php artisan wink:views:layouts --admin --sidebar --breadcrumbs --navigation --framework=bootstrap
```

### 5. Generate CRUD Interfaces

Generate complete CRUD interfaces for all entities:

```bash
# User management with advanced features
php artisan wink:views:crud users \
    --framework=bootstrap \
    --ajax \
    --search \
    --sorting \
    --filtering \
    --bulk-actions \
    --export \
    --layout=layouts.admin

# Product management with all features
php artisan wink:views:crud products \
    --framework=bootstrap \
    --ajax \
    --search \
    --sorting \
    --filtering \
    --bulk-actions \
    --export \
    --layout=layouts.admin

# Category management
php artisan wink:views:crud categories \
    --framework=bootstrap \
    --ajax \
    --search \
    --sorting \
    --layout=layouts.admin

# Order management
php artisan wink:views:crud orders \
    --framework=bootstrap \
    --ajax \
    --search \
    --sorting \
    --filtering \
    --export \
    --layout=layouts.admin

# Generate reusable components
php artisan wink:views:components \
    --all \
    --framework=bootstrap \
    --namespace=admin
```

### 6. Create Controllers

#### UserController for Admin
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage-users');
    }

    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        // Role filtering
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Status filtering
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Date filtering
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['name', 'email', 'role', 'created_at', 'last_login_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['orders' => function ($query) {
            $query->latest()->take(10);
        }]);

        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,customer',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'data' => $user,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,customer',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'data' => $user,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $name = $user->name;
        $user->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "User '{$name}' deleted successfully.",
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$name}' deleted successfully.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:users,id',
        ]);

        // Remove current user from deletion list
        $ids = array_filter($request->ids, fn($id) => $id != auth()->id());

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account.',
            ], 422);
        }

        $count = User::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} user(s) deleted successfully.",
        ]);
    }

    public function toggleStatus(User $user)
    {
        // Prevent disabling own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot disable your own account.',
            ], 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "User {$status} successfully.",
            'is_active' => $user->is_active,
        ]);
    }
}
```

### 7. Set Up Admin Routes

Create admin routes with proper middleware:

```php
// routes/web.php

use App\Http\Controllers\Admin\{
    DashboardController,
    UserController,
    ProductController,
    CategoryController,
    OrderController,
};

// Admin routes with authentication and authorization
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // User Management
        Route::resource('users', UserController::class);
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])
            ->name('users.bulk-delete');
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
        
        // Product Management
        Route::resource('products', ProductController::class);
        Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete'])
            ->name('products.bulk-delete');
        Route::post('products/bulk-update-status', [ProductController::class, 'bulkUpdateStatus'])
            ->name('products.bulk-update-status');
        Route::post('products/export', [ProductController::class, 'export'])
            ->name('products.export');
        
        // Category Management
        Route::resource('categories', CategoryController::class);
        Route::post('categories/bulk-delete', [CategoryController::class, 'bulkDelete'])
            ->name('categories.bulk-delete');
        
        // Order Management
        Route::resource('orders', OrderController::class)->except(['create', 'store']);
        Route::patch('orders/{order}/update-status', [OrderController::class, 'updateStatus'])
            ->name('orders.update-status');
        Route::post('orders/bulk-update-status', [OrderController::class, 'bulkUpdateStatus'])
            ->name('orders.bulk-update-status');
        Route::post('orders/export', [OrderController::class, 'export'])
            ->name('orders.export');
    });
```

### 8. Create Dashboard Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Product, Order, Category};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_categories' => Category::count(),
            'revenue_today' => Order::whereDate('created_at', today())
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
            'revenue_month' => Order::whereMonth('created_at', now()->month)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
        ];

        // Recent orders
        $recentOrders = Order::with(['user'])
            ->latest()
            ->take(10)
            ->get();

        // Top products (by order quantity)
        $topProducts = Product::withCount(['orderItems as total_sold' => function ($query) {
            $query->selectRaw('sum(quantity)');
        }])
        ->orderBy('total_sold', 'desc')
        ->take(5)
        ->get();

        // Monthly revenue chart data
        $monthlyRevenue = Order::selectRaw('MONTH(created_at) as month, SUM(total_amount) as revenue')
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', 'cancelled')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('revenue', 'month')
            ->toArray();

        // Fill missing months with 0
        $revenueData = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenueData[] = $monthlyRevenue[$i] ?? 0;
        }

        return view('admin.dashboard', compact(
            'stats', 
            'recentOrders', 
            'topProducts', 
            'revenueData'
        ));
    }
}
```

### 9. Create Authorization Policies

Create policies for fine-grained access control:

```bash
php artisan make:policy UserPolicy --model=User
php artisan make:policy ProductPolicy --model=Product
php artisan make:policy OrderPolicy --model=Order
```

Example UserPolicy:
```php
<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, User $model): bool
    {
        return $user->role === 'admin' || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, User $model): bool
    {
        return $user->role === 'admin' || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->role === 'admin' && $user->id !== $model->id;
    }

    public function bulkDelete(User $user): bool
    {
        return $user->role === 'admin';
    }
}
```

### 10. Create Middleware

Create admin middleware:

```bash
php artisan make:middleware AdminMiddleware
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Admin access required.');
        }

        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    // ... other middleware
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
];
```

### 11. Seed Sample Data

Create factories and seeders for testing:

```bash
php artisan make:factory UserFactory
php artisan make:factory ProductFactory  
php artisan make:factory CategoryFactory
php artisan make:factory OrderFactory
php artisan make:seeder AdminSeeder
```

Example AdminSeeder:
```php
<?php

namespace Database\Seeders;

use App\Models\{User, Category, Product, Order};
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create sample categories
        $categories = Category::factory()->count(10)->create();

        // Create sample products
        Product::factory()->count(50)->create();

        // Create sample customers
        $customers = User::factory()->count(25)->create([
            'role' => 'customer'
        ]);

        // Create sample orders
        Order::factory()->count(100)->create();
    }
}
```

### 12. Update Admin Layout

Ensure your admin layout includes proper navigation:

```html
<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="admin-layout">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i>
                Admin Panel
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                               href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
                               href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people me-2"></i>
                                Users
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" 
                               href="{{ route('admin.categories.index') }}">
                                <i class="bi bi-tags me-2"></i>
                                Categories
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" 
                               href="{{ route('admin.products.index') }}">
                                <i class="bi bi-box me-2"></i>
                                Products
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" 
                               href="{{ route('admin.orders.index') }}">
                                <i class="bi bi-receipt me-2"></i>
                                Orders
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('scripts')
</body>
</html>
```

## Testing the Admin Panel

### 1. Run Migrations and Seeders

```bash
php artisan migrate:fresh --seed
```

### 2. Start Development Server

```bash
php artisan serve
```

### 3. Access Admin Panel

Navigate to `http://localhost:8000/admin` and login with:
- Email: `admin@example.com`
- Password: `password`

### 4. Test All Features

- **Dashboard** - View statistics and charts
- **User Management** - Create, edit, delete users
- **Product Management** - Manage product catalog
- **Order Management** - Process and track orders
- **Bulk Operations** - Select multiple items and perform actions
- **Search & Filtering** - Find specific records quickly
- **Export Data** - Download data as CSV

## Generated Files Structure

```
app/
├── Http/
│   ├── Controllers/Admin/
│   │   ├── DashboardController.php
│   │   ├── UserController.php
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   └── OrderController.php
│   ├── Middleware/
│   │   └── AdminMiddleware.php
│   └── Policies/
│       ├── UserPolicy.php
│       ├── ProductPolicy.php
│       └── OrderPolicy.php
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── Category.php
│   ├── Order.php
│   └── OrderItem.php

resources/views/admin/
├── dashboard.blade.php
├── users/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── products/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── categories/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── orders/
│   ├── index.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
└── layouts/
    └── admin.blade.php

database/
├── migrations/
├── factories/
└── seeders/
```

## Key Features Demonstrated

1. **Complete Admin Interface** - Professional dashboard with sidebar
2. **Multi-entity Management** - Related models with proper relationships
3. **Advanced Filtering** - Complex search and filter combinations
4. **Bulk Operations** - Mass actions for efficiency
5. **Role-based Access** - Proper authorization and permissions
6. **AJAX Interactions** - Smooth user experience
7. **Data Export** - CSV/Excel export functionality
8. **Responsive Design** - Works on all screen sizes
9. **Real-time Stats** - Dashboard with live data
10. **Professional UI** - Production-ready interface

## Customization Options

### 1. Add More Entities

```bash
php artisan make:model Invoice -m
php artisan wink:views:crud invoices --framework=bootstrap --ajax --layout=layouts.admin
```

### 2. Custom Dashboard Widgets

Create custom widgets by editing the dashboard view:

```php
// Add to DashboardController
$lowStockProducts = Product::where('quantity', '<=', 10)->count();
$todayOrders = Order::whereDate('created_at', today())->count();
```

### 3. Advanced Reports

Add reporting functionality:

```bash
php artisan make:controller Admin\ReportController
```

### 4. API Integration

Add API endpoints for mobile apps:

```bash
php artisan make:controller Api\AdminController --api
```

This admin panel example demonstrates the full power of the Wink View Generator package, showing how quickly you can build a complete, feature-rich administration interface for complex applications.

**Total development time**: ~2 hours  
**Lines of code generated**: ~5,000+  
**Features included**: 25+ out of the box