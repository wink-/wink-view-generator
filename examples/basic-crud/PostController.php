<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * PostController
 * 
 * Example controller showing how to integrate with Wink View Generator
 * generated views. This controller handles all CRUD operations for posts
 * including search, sorting, filtering, bulk actions, and export.
 */
class PostController extends Controller
{
    /**
     * Display a paginated listing of posts with search and filtering.
     */
    public function index(Request $request)
    {
        $query = Post::query();

        // Search functionality - searches across title, content, and excerpt
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%")
                  ->orWhere('excerpt', 'like', "%{$searchTerm}%");
            });
        }

        // Status filtering - filter by draft, published, or archived
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filtering
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting with security check
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['title', 'status', 'created_at', 'published_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        // Pagination with configurable per-page
        $perPage = $request->get('per_page', 15);
        $posts = $query->paginate($perPage);

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created post in storage.
     */
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
            
            // Ensure uniqueness
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (Post::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        // Auto-set published_at if status is published and not set
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = Post::create($validated);

        // Handle AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post created successfully.',
                'data' => $post,
                'redirect' => route('posts.index'),
            ]);
        }

        return redirect()->route('posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     */
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

        // Auto-set published_at if status changed to published
        if ($validated['status'] === 'published' && 
            $post->status !== 'published' && 
            empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Clear published_at if status changed from published
        if ($validated['status'] !== 'published' && $post->status === 'published') {
            $validated['published_at'] = null;
        }

        $post->update($validated);

        // Handle AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully.',
                'data' => $post,
                'redirect' => route('posts.index'),
            ]);
        }

        return redirect()->route('posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        $title = $post->title;
        $post->delete();

        // Handle AJAX requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Post '{$title}' deleted successfully.",
            ]);
        }

        return redirect()->route('posts.index')
            ->with('success', "Post '{$title}' deleted successfully.");
    }

    /**
     * Delete multiple posts at once.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:posts,id',
        ]);

        $count = Post::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} post(s) deleted successfully.",
        ]);
    }

    /**
     * Export posts to CSV format.
     */
    public function export(Request $request)
    {
        $request->validate([
            'ids' => 'nullable|array',
            'ids.*' => 'exists:posts,id',
            'format' => 'nullable|in:csv,xlsx',
        ]);

        $query = Post::query();

        // Export selected posts or all filtered posts
        if ($request->filled('ids')) {
            $query->whereIn('id', $request->ids);
        } else {
            // Apply same filters as index
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('content', 'like', "%{$searchTerm}%")
                      ->orWhere('excerpt', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        }

        $posts = $query->orderBy('created_at', 'desc')->get();

        $format = $request->get('format', 'csv');
        $filename = 'posts-' . now()->format('Y-m-d-H-i-s') . '.' . $format;

        if ($format === 'csv') {
            return $this->exportToCsv($posts, $filename);
        }

        // Add support for other formats (Excel, PDF, etc.)
        return $this->exportToCsv($posts, $filename);
    }

    /**
     * Export posts to CSV format.
     */
    private function exportToCsv($posts, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($posts) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Title',
                'Slug',
                'Status',
                'Excerpt',
                'Created At',
                'Published At',
                'Updated At',
            ]);
            
            // CSV data
            foreach ($posts as $post) {
                fputcsv($file, [
                    $post->id,
                    $post->title,
                    $post->slug,
                    ucfirst($post->status),
                    $post->excerpt,
                    $post->created_at->format('Y-m-d H:i:s'),
                    $post->published_at?->format('Y-m-d H:i:s') ?? '',
                    $post->updated_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Toggle post status between draft and published.
     */
    public function toggleStatus(Post $post)
    {
        $newStatus = $post->status === 'published' ? 'draft' : 'published';
        
        $post->update([
            'status' => $newStatus,
            'published_at' => $newStatus === 'published' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Post status changed to {$newStatus}.",
            'status' => $newStatus,
        ]);
    }

    /**
     * Duplicate a post.
     */
    public function duplicate(Post $post)
    {
        $newPost = $post->replicate();
        $newPost->title = $post->title . ' (Copy)';
        $newPost->slug = Str::slug($newPost->title);
        $newPost->status = 'draft';
        $newPost->published_at = null;
        
        // Ensure unique slug
        $baseSlug = $newPost->slug;
        $counter = 1;
        while (Post::where('slug', $newPost->slug)->exists()) {
            $newPost->slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        $newPost->save();

        return response()->json([
            'success' => true,
            'message' => 'Post duplicated successfully.',
            'data' => $newPost,
        ]);
    }
}