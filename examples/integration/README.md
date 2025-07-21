# Laravel Integration Example

This example demonstrates how to integrate the Wink View Generator with an existing Laravel application, showing best practices for incremental adoption and working with legacy code.

## Overview

We'll show how to:

- **Integrate with existing authentication** systems
- **Work alongside existing views** without conflicts
- **Migrate legacy CRUD interfaces** gradually
- **Maintain existing design systems** while adding new features
- **Handle complex relationships** in established codebases
- **Preserve custom business logic** in controllers
- **Use with existing middleware** and policies

## Scenario: Existing E-commerce Application

Let's assume you have an existing Laravel e-commerce application with:

- ✅ User authentication (Laravel Breeze/Jetstream)
- ✅ Existing product catalog
- ✅ Legacy admin interface
- ✅ Custom middleware and policies
- ✅ Established database schema
- ✅ Existing design system (Bootstrap 4)

**Goal**: Add modern CRUD interfaces without breaking existing functionality.

## Integration Strategy

### Phase 1: Assessment and Planning

1. **Audit Existing Views**
2. **Identify Integration Points**
3. **Plan Migration Strategy**
4. **Set Up Package Configuration**

### Phase 2: Incremental Implementation

1. **Start with New Features**
2. **Migrate Simple CRUD**
3. **Enhance Complex Views**
4. **Optimize Performance**

### Phase 3: Full Integration

1. **Replace Legacy Views**
2. **Unify Design System**
3. **Optimize Database Queries**
4. **Add Advanced Features**

## Step-by-Step Integration

### 1. Initial Setup and Configuration

#### Install Package in Existing App

```bash
# Install the package
composer require wink/view-generator

# Publish configuration
php artisan vendor:publish --provider="Wink\ViewGenerator\ViewGeneratorServiceProvider" --tag="config"
```

#### Configure for Existing Framework

```php
// config/wink-views.php
return [
    // Match your existing Bootstrap version
    'framework' => env('WINK_VIEWS_FRAMEWORK', 'bootstrap'),
    
    // Use your existing layout
    'layout' => [
        'master' => env('WINK_VIEWS_MASTER_LAYOUT', 'layouts.admin'),
        'admin' => env('WINK_VIEWS_ADMIN_LAYOUT', 'layouts.admin'),
        'auth' => env('WINK_VIEWS_AUTH_LAYOUT', 'layouts.auth'),
    ],
    
    // Match existing component namespace
    'components' => [
        'use_components' => true,
        'component_namespace' => 'admin.components', // Match existing structure
    ],
    
    // Preserve existing paths
    'paths' => [
        'views' => resource_path('views'),
        'components' => resource_path('views/admin/components'),
        'layouts' => resource_path('views/layouts'),
    ],
    
    // Integrate with existing features
    'features' => [
        'pagination' => true,
        'search' => true,
        'ajax_forms' => false, // Start without AJAX to avoid conflicts
        'export' => true,
    ],
];
```

#### Environment Configuration

```env
# .env additions
WINK_VIEWS_FRAMEWORK=bootstrap
WINK_VIEWS_MASTER_LAYOUT=layouts.admin
WINK_VIEWS_COMPONENT_NAMESPACE=admin.components
WINK_VIEWS_AJAX_FORMS=false
```

### 2. Working with Existing Authentication

#### Existing User Model Integration

```php
// app/Models/User.php (existing)
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_active'
    ];

    // Existing methods...
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Add methods for view generator compatibility
    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('M d, Y g:i A');
    }

    // Existing scopes and relationships...
}
```

#### Existing Middleware Integration

```php
// app/Http/Middleware/AdminMiddleware.php (existing)
class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
```

### 3. Gradual View Migration

#### Start with New Features First

Generate views for a new feature that doesn't exist yet:

```bash
# Generate views for a new inventory management feature
php artisan wink:views:crud inventory --framework=bootstrap --layout=layouts.admin
```

#### Migrate Simple Existing CRUD

For existing simple CRUD (like categories), generate alongside existing views:

```bash
# Generate in a subfolder to avoid conflicts
php artisan wink:views:crud categories --framework=bootstrap --layout=layouts.admin --output-path=resources/views/admin/generated
```

Then gradually migrate by:

1. **Comparing generated vs existing views**
2. **Testing functionality**
3. **Moving generated views to replace existing ones**
4. **Updating routes if needed**

### 4. Existing Controller Integration

#### Working with Existing Controllers

```php
// app/Http/Controllers/Admin/ProductController.php (existing)
class ProductController extends Controller
{
    // Existing constructor and middleware
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    // Enhanced index method with search/filtering
    public function index(Request $request)
    {
        // Existing query logic
        $query = Product::with(['category', 'brand']);

        // Add Wink View Generator compatible search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Add filtering (new feature)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Add sorting (new feature)
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['name', 'sku', 'price', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        // Existing pagination
        $products = $query->paginate($request->get('per_page', 15));

        // Use new generated view or existing view
        $view = $request->get('use_new_interface') ? 'admin.products.index_new' : 'admin.products.index';
        
        return view($view, compact('products'));
    }

    // Add new bulk operations
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:products,id',
        ]);

        $count = Product::whereIn('id', $request->ids)->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} product(s) deleted successfully.",
            ]);
        }

        return redirect()->back()->with('success', "{$count} product(s) deleted successfully.");
    }

    // Add export functionality
    public function export(Request $request)
    {
        // Use existing export logic or enhance it
        $query = Product::with(['category', 'brand']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->ids);
        }

        $products = $query->get();

        // Use existing export service or create new one
        return Excel::download(new ProductsExport($products), 'products.xlsx');
    }

    // Existing methods remain unchanged...
    public function store(Request $request)
    {
        // Keep existing validation and business logic
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            // ... existing validation rules
        ]);

        // Existing creation logic
        $product = Product::create($validated);

        // Enhanced response for AJAX compatibility
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => $product,
                'redirect' => route('admin.products.index'),
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }
}
```

### 5. Existing Layout Integration

#### Enhance Existing Layout

```html
<!-- resources/views/layouts/admin.blade.php (existing, enhanced) -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    
    <!-- Existing CSS -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    
    <!-- Bootstrap (existing version) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Add Wink Views enhancements -->
    @stack('styles')
    
    <style>
        /* Compatibility styles for Wink Views with Bootstrap 4 */
        .table-responsive {
            border-radius: 0.375rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        
        /* Enhance existing styles for new features */
        .bulk-actions {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }
        
        .sortable {
            cursor: pointer;
            user-select: none;
        }
        
        .sortable:hover {
            background-color: rgba(0, 0, 0, 0.025);
        }
    </style>
</head>
<body class="admin-body">
    <!-- Existing navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <!-- Existing nav content -->
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                {{ config('app.name') }} Admin
            </a>
            
            <!-- Existing nav items -->
            <div class="navbar-nav ml-auto">
                <!-- Add toggle for new interface -->
                @if(request()->routeIs('admin.products.*'))
                    <div class="nav-item">
                        <a class="nav-link" href="{{ request()->fullUrlWithQuery(['use_new_interface' => !request('use_new_interface')]) }}">
                            {{ request('use_new_interface') ? 'Use Legacy Interface' : 'Try New Interface' }}
                        </a>
                    </div>
                @endif
                
                <!-- Existing user menu -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        {{ auth()->user()->name }}
                    </a>
                    <div class="dropdown-menu">
                        <!-- Existing dropdown items -->
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Existing sidebar -->
            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <!-- Existing nav items -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                               href="{{ route('admin.dashboard') }}">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" 
                               href="{{ route('admin.products.index') }}">
                                Products
                            </a>
                        </li>
                        <!-- More existing nav items -->
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main role="main" class="col-md-10 ml-sm-auto px-4">
                <!-- Existing alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Existing JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
    
    <!-- Add Wink Views enhancements -->
    @stack('scripts')
</body>
</html>
```

### 6. Database Schema Integration

#### Working with Existing Migrations

```php
// Enhance existing Product model for better compatibility
class Product extends Model
{
    // Existing fillable and relationships...
    protected $fillable = [
        'name', 'sku', 'description', 'price', 'category_id', 'status'
    ];

    // Add methods for view compatibility
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'draft' => 'warning',
            default => 'light',
        };
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getShortDescriptionAttribute(): string
    {
        return Str::limit($this->description, 100);
    }

    // Enhanced scopes for filtering
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // Existing relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Add relationship for orders if not exists
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
```

### 7. Route Integration

#### Enhance Existing Routes

```php
// routes/web.php or routes/admin.php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        
        // Existing routes
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Enhanced product routes
        Route::resource('products', ProductController::class);
        
        // Add new Wink Views compatible routes
        Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete'])
            ->name('products.bulk-delete');
        Route::post('products/bulk-update-status', [ProductController::class, 'bulkUpdateStatus'])
            ->name('products.bulk-update-status');
        Route::post('products/export', [ProductController::class, 'export'])
            ->name('products.export');
        
        // Existing category routes
        Route::resource('categories', CategoryController::class);
        
        // Existing order routes
        Route::resource('orders', OrderController::class)->except(['create', 'store']);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])
            ->name('orders.update-status');
    });
```

### 8. Component Integration

#### Create Shared Components

```bash
# Generate components that work with existing structure
php artisan wink:views:components --framework=bootstrap --namespace=admin.components
```

#### Existing Component Enhancement

```php
// resources/views/admin/components/product-card.blade.php (existing, enhanced)
@props(['product', 'showActions' => true])

<div class="card product-card h-100">
    @if($product->image)
        <img src="{{ asset($product->image) }}" class="card-img-top" alt="{{ $product->name }}">
    @endif
    
    <div class="card-body">
        <h5 class="card-title">{{ $product->name }}</h5>
        <p class="card-text">{{ $product->short_description }}</p>
        
        <div class="d-flex justify-content-between align-items-center">
            <span class="h6 mb-0">{{ $product->formatted_price }}</span>
            <span class="badge badge-{{ $product->status_badge }}">
                {{ ucfirst($product->status) }}
            </span>
        </div>
    </div>
    
    @if($showActions)
        <div class="card-footer">
            <div class="btn-group w-100">
                <a href="{{ route('admin.products.show', $product) }}" 
                   class="btn btn-outline-info btn-sm">
                    <i class="bi bi-eye"></i> View
                </a>
                <a href="{{ route('admin.products.edit', $product) }}" 
                   class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <button type="button" 
                        class="btn btn-outline-danger btn-sm"
                        onclick="confirmDelete('{{ $product->id }}', '{{ addslashes($product->name) }}')">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>
    @endif
</div>
```

### 9. Progressive Enhancement

#### A/B Testing New vs Old Interface

```php
// app/Http/Controllers/Admin/ProductController.php
public function index(Request $request)
{
    // ... existing query logic ...

    // Determine which view to use
    $useNewInterface = $request->boolean('use_new_interface') || 
                      auth()->user()->preferences['use_new_admin_interface'] ?? false;

    if ($useNewInterface) {
        // Use generated view with all new features
        return view('admin.products.index_enhanced', compact('products'));
    } else {
        // Use existing view
        return view('admin.products.index', compact('products'));
    }
}
```

#### Feature Flags

```php
// config/features.php
return [
    'enhanced_product_interface' => env('FEATURE_ENHANCED_PRODUCTS', false),
    'bulk_operations' => env('FEATURE_BULK_OPS', true),
    'advanced_search' => env('FEATURE_ADVANCED_SEARCH', true),
    'ajax_forms' => env('FEATURE_AJAX_FORMS', false),
];
```

```php
// In your controller
if (config('features.bulk_operations')) {
    // Include bulk operation functionality
}
```

### 10. Testing Integration

#### Test Existing Functionality

```bash
# Run existing tests to ensure no regression
php artisan test

# Test specific areas that might be affected
php artisan test --filter=ProductTest
php artisan test --filter=AdminTest
```

#### Add Tests for New Features

```php
// tests/Feature/Admin/ProductBulkOperationsTest.php
class ProductBulkOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_delete_products()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $products = Product::factory()->count(3)->create();

        $response = $this->actingAs($admin)
            ->postJson(route('admin.products.bulk-delete'), [
                'ids' => $products->pluck('id')->toArray()
            ]);

        $response->assertSuccessful();
        $this->assertEquals(0, Product::count());
    }

    public function test_non_admin_cannot_access_bulk_delete()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $products = Product::factory()->count(3)->create();

        $response = $this->actingAs($user)
            ->postJson(route('admin.products.bulk-delete'), [
                'ids' => $products->pluck('id')->toArray()
            ]);

        $response->assertForbidden();
    }
}
```

### 11. Performance Considerations

#### Optimize Existing Queries

```php
// Before (existing)
public function index()
{
    $products = Product::paginate(15);
    return view('admin.products.index', compact('products'));
}

// After (optimized for new features)
public function index(Request $request)
{
    $query = Product::with(['category:id,name']) // Eager load only needed fields
        ->select(['id', 'name', 'sku', 'price', 'status', 'category_id', 'created_at']); // Only select needed columns

    // Add search with full-text index if available
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        if (DB::getDriverName() === 'mysql') {
            $query->whereRaw('MATCH(name, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerm . '*']);
        } else {
            $query->where('name', 'like', "%{$searchTerm}%");
        }
    }

    $products = $query->paginate($request->get('per_page', 15));
    
    return view('admin.products.index', compact('products'));
}
```

#### Add Database Indexes

```php
// database/migrations/add_indexes_for_search.php
public function up()
{
    Schema::table('products', function (Blueprint $table) {
        $table->index(['status', 'created_at']);
        $table->index('category_id');
        
        // Add full-text index for better search performance
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products ADD FULLTEXT(name, description)');
        }
    });
}
```

### 12. Deployment Strategy

#### Staged Rollout

```bash
# 1. Deploy to staging with feature flags disabled
FEATURE_ENHANCED_PRODUCTS=false

# 2. Test thoroughly on staging
php artisan test
php artisan dusk

# 3. Deploy to production with gradual rollout
FEATURE_ENHANCED_PRODUCTS=true

# 4. Monitor performance and user feedback
# 5. Gradually enable for all users
```

#### Rollback Plan

```php
// Keep both views available for quick rollback
if (config('features.enhanced_product_interface') && !request('use_legacy')) {
    return view('admin.products.index_enhanced', compact('products'));
} else {
    return view('admin.products.index_legacy', compact('products'));
}
```

## Integration Checklist

### ✅ Pre-Integration

- [ ] Backup database and codebase
- [ ] Document existing functionality
- [ ] Set up staging environment
- [ ] Plan rollback strategy

### ✅ During Integration

- [ ] Install and configure package
- [ ] Generate views in separate directories first
- [ ] Test compatibility with existing code
- [ ] Enhance existing controllers gradually
- [ ] Add new routes without breaking existing ones
- [ ] Update layouts to support new features

### ✅ Post-Integration

- [ ] Run comprehensive tests
- [ ] Monitor performance
- [ ] Train users on new features
- [ ] Document changes
- [ ] Plan gradual migration of remaining views

## Common Integration Challenges

### 1. **CSS Conflicts**

**Problem**: New generated views conflict with existing styles.

**Solution**:
```css
/* Namespace new styles */
.wink-generated {
    /* New view styles */
}

/* Or use CSS-in-JS for components */
.enhanced-table {
    /* Enhanced table styles */
}
```

### 2. **JavaScript Conflicts**

**Problem**: AJAX functionality conflicts with existing JS.

**Solution**:
```javascript
// Use namespacing
window.WinkViews = {
    initializeBulkActions: function() {
        // Implementation
    }
};

// Or use modules
import { initializeBulkActions } from './wink-views.js';
```

### 3. **Route Conflicts**

**Problem**: New routes conflict with existing ones.

**Solution**:
```php
// Use route prefixes or different names
Route::post('admin/products/wink-bulk-delete', [ProductController::class, 'bulkDelete'])
    ->name('admin.products.wink.bulk-delete');
```

### 4. **Database Performance**

**Problem**: New search features slow down existing queries.

**Solution**:
```php
// Add proper indexes
Schema::table('products', function (Blueprint $table) {
    $table->index(['status', 'created_at']);
});

// Use query optimization
$query->select(['id', 'name', 'status']) // Only needed fields
      ->with(['category:id,name']); // Eager load with specific fields
```

## Benefits of Integration Approach

### 1. **Risk Mitigation**
- Gradual migration reduces risk
- Easy rollback if issues occur
- Existing functionality preserved

### 2. **User Experience**
- Users can choose their preferred interface
- Gradual learning curve
- No disruption to daily workflows

### 3. **Development Efficiency**
- Leverage existing business logic
- Reuse authentication and authorization
- Maintain existing integrations

### 4. **Cost Effectiveness**
- No need to rewrite everything
- Incremental improvements
- Return on investment quickly

This integration example shows how the Wink View Generator can be successfully integrated into existing Laravel applications without disrupting current functionality, while providing a clear path to modernize and enhance your admin interfaces.

**Key Takeaways:**
- 🔄 **Incremental Migration**: Start small, grow gradually
- 🛡️ **Risk Management**: Always have a rollback plan
- 👥 **User-Centric**: Let users choose their preferred interface
- 📊 **Performance First**: Monitor and optimize continuously
- 🧪 **Test Everything**: Comprehensive testing prevents issues