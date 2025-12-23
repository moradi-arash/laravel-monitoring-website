<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(): View
    {
        $users = User::withCount('websites')->orderBy('created_at', 'desc')->paginate(15);
        
        // Calculate full totals for all users, not just current page
        $adminCount = User::where('role', 'admin')->count();
        $regularCount = User::where('role', 'user')->count();
        
        return view('users.index', compact('users', 'adminCount', 'regularCount'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $user->load(['websites', 'settings']);
        
        return view('users.show', compact('user'));
    }

    /**
     * Update the specified user's role.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:user,admin'],
        ]);

        // Prevent admin from demoting themselves
        if ($user->id === auth()->id() && $validated['role'] === 'user') {
            return redirect()->back()
                ->withErrors(['role' => 'You cannot demote yourself from admin role.']);
        }

        $user->update(['role' => $validated['role']]);

        return redirect()->route('users.index')
            ->with('status', 'User role updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->withErrors(['delete' => 'You cannot delete your own account. Please ask another administrator.']);
        }

        // Validate that the admin typed the correct name
        $request->validate([
            'confirm_name' => ['required', 'string', Rule::in([$user->name])],
        ], [
            'confirm_name.in' => 'The name you entered does not match the user\'s name. Deletion cancelled.',
        ]);

        // Store user name for success message
        $userName = $user->name;
        $websitesCount = $user->websites()->count();

        // Delete the user (cascade deletes will handle related data)
        // The database migrations have onDelete('cascade') for:
        // - user_settings table
        // - websites table
        $user->delete();

        return redirect()->route('users.index')
            ->with('status', "User '{$userName}' and {$websitesCount} associated website(s) have been permanently deleted.");
    }
}
