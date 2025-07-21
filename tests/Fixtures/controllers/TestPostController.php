<?php

namespace Wink\ViewGenerator\Tests\Fixtures\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Wink\ViewGenerator\Tests\Fixtures\Models\TestPost;
use Wink\ViewGenerator\Tests\Fixtures\Models\TestUser;
use Wink\ViewGenerator\Tests\Fixtures\Models\TestCategory;

class TestPostController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = TestPost::with(['user', 'categories']);

        // Search functionality
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by featured
        if ($request->filled('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        // Filter by author
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('test_categories.id', $request->category_id);
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $posts = $query->paginate(15)->withQueryString();

        // Get filter options
        $users = TestUser::orderBy('name')->get();
        $categories = TestCategory::where('is_active', true)->orderBy('name')->get();

        return view('test_posts.index', compact('posts', 'users', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $users = TestUser::orderBy('name')->get();
        $categories = TestCategory::where('is_active', true)->orderBy('name')->get();

        return view('test_posts.create', compact('users', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(TestPost::getValidationRules());

        $post = TestPost::create($validated);

        // Sync categories
        if ($request->filled('categories')) {
            $post->categories()->sync($request->categories);
        }

        return redirect()->route('test_posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TestPost $testPost): View
    {
        $testPost->load(['user', 'categories']);
        $testPost->increment('views_count');

        return view('test_posts.show', compact('testPost'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TestPost $testPost): View
    {
        $testPost->load('categories');
        $users = TestUser::orderBy('name')->get();
        $categories = TestCategory::where('is_active', true)->orderBy('name')->get();

        return view('test_posts.edit', compact('testPost', 'users', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TestPost $testPost): RedirectResponse
    {
        $rules = TestPost::getValidationRules();
        $rules['slug'] = 'required|string|max:255|unique:test_posts,slug,' . $testPost->id;

        $validated = $request->validate($rules);

        $testPost->update($validated);

        // Sync categories
        if ($request->filled('categories')) {
            $testPost->categories()->sync($request->categories);
        }

        return redirect()->route('test_posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TestPost $testPost): RedirectResponse
    {
        $testPost->delete();

        return redirect()->route('test_posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Bulk operations on posts.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,publish,draft,feature,unfeature',
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:test_posts,id',
        ]);

        $posts = TestPost::whereIn('id', $request->selected_ids);

        switch ($request->action) {
            case 'delete':
                $posts->delete();
                $message = count($request->selected_ids) . ' posts deleted successfully.';
                break;
            case 'publish':
                $posts->update(['status' => 'published', 'published_at' => now()]);
                $message = count($request->selected_ids) . ' posts published successfully.';
                break;
            case 'draft':
                $posts->update(['status' => 'draft', 'published_at' => null]);
                $message = count($request->selected_ids) . ' posts moved to draft.';
                break;
            case 'feature':
                $posts->update(['featured' => true]);
                $message = count($request->selected_ids) . ' posts featured successfully.';
                break;
            case 'unfeature':
                $posts->update(['featured' => false]);
                $message = count($request->selected_ids) . ' posts unfeatured successfully.';
                break;
        }

        return redirect()->route('test_posts.index')->with('success', $message);
    }
}