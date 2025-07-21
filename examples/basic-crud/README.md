# Basic CRUD Example

This example demonstrates how to create a simple CRUD interface for managing blog posts using the Wink View Generator package.

## Overview

We'll create a complete blog post management system with:

- **Posts table** with title, content, status, and timestamps
- **CRUD operations** (Create, Read, Update, Delete)
- **Search functionality** to find posts
- **Sorting capabilities** for all columns
- **Bootstrap 5 styling** for a professional look

## Prerequisites

- Laravel 10.0+ application
- Wink View Generator package installed
- Database connection configured

## Step-by-Step Guide

### 1. Create the Post Model and Migration

```bash
php artisan make:model Post -m
```

### 2. Configure the Migration

Edit `database/migrations/create_posts_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'published_at']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### 3. Configure the Post Model

Edit `app/Models/Post.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'status',
        'slug',
        'excerpt',
        'featured_image',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            if (empty($post->excerpt)) {
                $post->excerpt = Str::limit(strip_tags($post->content), 150);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->getOriginal('slug'))) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft' => 'warning',
            'archived' => 'secondary',
            default => 'light',
        };
    }

    public function getFormattedPublishedAtAttribute(): string
    {
        return $this->published_at?->format('M d, Y') ?? 'Not published';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}
```

### 4. Run the Migration

```bash
php artisan migrate
```

### 5. Generate CRUD Views

Now generate the complete CRUD interface:

```bash
php artisan wink:views:crud posts --framework=bootstrap --ajax --search --sorting --filtering
```

This command generates:

- `resources/views/posts/index.blade.php` - List all posts
- `resources/views/posts/show.blade.php` - Show single post
- `resources/views/posts/create.blade.php` - Create new post
- `resources/views/posts/edit.blade.php` - Edit existing post
- `resources/views/posts/partials/form.blade.php` - Shared form fields

### 6. Create the PostController

```bash
php artisan make:controller PostController --resource
```

Edit `app/Http/Controllers/PostController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%")
                  ->orWhere('excerpt', 'like', "%{$searchTerm}%");
            });
        }

        // Status filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
        
        $allowedSorts = ['title', 'status', 'created_at', 'published_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        $perPage = $request->get('per_page', 15);
        $posts = $query->paginate($perPage);

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,archived',
            'slug' => 'nullable|string|unique:posts',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set published_at if status is published and not set
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = Post::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post created successfully.',
                'data' => $post,
            ]);
        }

        return redirect()->route('posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,archived',
            'slug' => 'nullable|string|unique:posts,slug,' . $post->id,
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        // Set published_at if status changed to published
        if ($validated['status'] === 'published' && 
            $post->status !== 'published' && 
            empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully.',
                'data' => $post,
            ]);
        }

        return redirect()->route('posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully.',
            ]);
        }

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:posts,id',
        ]);

        $count = Post::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} posts deleted successfully.",
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'ids' => 'nullable|array',
            'ids.*' => 'exists:posts,id',
        ]);

        $query = Post::query();

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->ids);
        }

        $posts = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="posts.csv"',
        ];

        $callback = function () use ($posts) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, ['ID', 'Title', 'Status', 'Created At', 'Published At']);
            
            // CSV data
            foreach ($posts as $post) {
                fputcsv($file, [
                    $post->id,
                    $post->title,
                    $post->status,
                    $post->created_at->format('Y-m-d H:i:s'),
                    $post->published_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
```

### 7. Add Routes

Add these routes to `routes/web.php`:

```php
<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

// Post management routes
Route::resource('posts', PostController::class);

// Bulk actions
Route::post('posts/bulk-delete', [PostController::class, 'bulkDelete'])
    ->name('posts.bulk-delete');

// Export functionality
Route::post('posts/export', [PostController::class, 'export'])
    ->name('posts.export');
```

### 8. Create a Factory (Optional)

For testing with sample data:

```bash
php artisan make:factory PostFactory
```

Edit `database/factories/PostFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(6, true);
        
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(5, true),
            'excerpt' => fake()->paragraph(),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'featured_image' => fake()->imageUrl(800, 600, 'business'),
            'published_at' => fake()->optional(0.7)->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
```

### 9. Seed Sample Data

Create a seeder:

```bash
php artisan make:seeder PostSeeder
```

Edit `database/seeders/PostSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Create 50 posts with different statuses
        Post::factory()
            ->count(30)
            ->published()
            ->create();

        Post::factory()
            ->count(15)
            ->draft()
            ->create();

        Post::factory()
            ->count(5)
            ->state(['status' => 'archived'])
            ->create();
    }
}
```

Run the seeder:

```bash
php artisan db:seed --class=PostSeeder
```

### 10. Update Your App Layout

Ensure your `resources/views/layouts/app.blade.php` includes Bootstrap 5:

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('posts.index') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('posts.index') }}">Posts</a>
                <a class="nav-link" href="{{ route('posts.create') }}">New Post</a>
            </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
```

## Testing the CRUD Interface

### 1. Start the Development Server

```bash
php artisan serve
```

### 2. Visit the Posts Index

Navigate to `http://localhost:8000/posts` to see your generated interface.

### 3. Test All Features

Try these features:

- **Browse posts** - View the paginated list
- **Search posts** - Use the search box to find specific posts
- **Sort columns** - Click column headers to sort
- **Filter by status** - Use the status dropdown
- **Create new post** - Click "Add Post" button
- **Edit post** - Click edit button in the table
- **View post** - Click view button for details
- **Delete post** - Click delete button (with confirmation)
- **Bulk actions** - Select multiple posts and delete
- **Export** - Export selected posts to CSV

### 4. Test AJAX Functionality

The generated views include AJAX features:

- Form submissions without page reload
- Real-time search as you type
- Smooth delete confirmations
- Loading states during operations

## Generated Files Overview

### Index View (`resources/views/posts/index.blade.php`)

Features included:
- Responsive Bootstrap table
- Search form with real-time filtering
- Column sorting with visual indicators
- Status filtering dropdown
- Pagination controls
- Bulk selection and actions
- Export functionality
- Mobile-responsive design
- Empty state when no posts found

### Create/Edit Forms

Features included:
- CSRF protection
- Validation error display
- Rich form fields based on model attributes
- AJAX form submission
- Progress indicators
- Auto-save functionality (if enabled)

### Show View

Features included:
- Clean detail layout
- Action buttons (Edit, Delete)
- Breadcrumb navigation
- Related data display

## Customization Examples

### 1. Add Rich Text Editor

Update your configuration:

```env
WINK_VIEWS_RICH_TEXT_EDITOR=tinymce
```

Regenerate with rich text:

```bash
php artisan wink:views:forms posts --rich-text --force
```

### 2. Enable File Uploads

Add file upload field:

```env
WINK_VIEWS_FILE_UPLOAD=dropzone
```

### 3. Custom Styling

Override the default templates by publishing them:

```bash
php artisan vendor:publish --tag=wink-views-templates
```

Then edit `resources/stubs/wink-views/bootstrap/crud/index.blade.php.stub`.

## Next Steps

1. **Add Categories** - Create a categories table and relationship
2. **Add Users** - Associate posts with users
3. **Add Comments** - Create a comment system
4. **Add Tags** - Implement tagging functionality
5. **Add Media** - Integrate file upload for images

## Troubleshooting

### Common Issues

1. **Styles not loading** - Ensure Bootstrap CSS is included in your layout
2. **AJAX not working** - Check that CSRF token is in meta tags
3. **Search not working** - Verify the search logic in your controller
4. **Sorting broken** - Check that column names match database fields

### Debug Tips

```bash
# Clear caches
php artisan view:clear
php artisan config:clear

# Check routes
php artisan route:list --name=posts

# Test database connection
php artisan tinker
>>> App\Models\Post::count()
```

This basic CRUD example demonstrates the power of the Wink View Generator package. With a single command, you get a complete, feature-rich interface that would take hours to build manually.

## Files Created

```
app/
├── Http/Controllers/PostController.php
└── Models/Post.php

database/
├── factories/PostFactory.php
├── migrations/xxxx_create_posts_table.php
└── seeders/PostSeeder.php

resources/views/posts/
├── index.blade.php
├── show.blade.php
├── create.blade.php
├── edit.blade.php
└── partials/
    └── form.blade.php

routes/
└── web.php (updated)
```

**Total time to create**: ~10 minutes  
**Lines of code generated**: ~1,000+  
**Features included**: 15+ out of the box