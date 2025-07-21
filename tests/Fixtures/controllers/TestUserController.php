<?php

namespace Wink\ViewGenerator\Tests\Fixtures\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Wink\ViewGenerator\Tests\Fixtures\Models\TestUser;

class TestUserController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = TestUser::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by admin status
        if ($request->filled('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $users = $query->paginate(15)->withQueryString();

        return view('test_users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('test_users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(TestUser::getValidationRules());

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        TestUser::create($validated);

        return redirect()->route('test_users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TestUser $testUser): View
    {
        $testUser->load('posts');
        return view('test_users.show', compact('testUser'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TestUser $testUser): View
    {
        return view('test_users.edit', compact('testUser'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TestUser $testUser): RedirectResponse
    {
        $rules = TestUser::getValidationRules();
        $rules['email'] = 'required|string|email|max:255|unique:test_users,email,' . $testUser->id;

        if (!$request->filled('password')) {
            unset($rules['password']);
        }

        $validated = $request->validate($rules);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $testUser->update($validated);

        return redirect()->route('test_users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TestUser $testUser): RedirectResponse
    {
        $testUser->delete();

        return redirect()->route('test_users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:test_users,id',
        ]);

        TestUser::whereIn('id', $request->selected_ids)->delete();

        return redirect()->route('test_users.index')
            ->with('success', count($request->selected_ids) . ' users deleted successfully.');
    }

    /**
     * Export users to CSV.
     */
    public function export(Request $request)
    {
        $users = TestUser::all();

        $filename = 'users_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($users) {
            $handle = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($handle, ['ID', 'Name', 'Email', 'Status', 'Admin', 'Created At']);
            
            // Add data rows
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->status,
                    $user->is_admin ? 'Yes' : 'No',
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($handle);
        }, 200, $headers);
    }
}