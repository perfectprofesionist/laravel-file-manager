<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * ProfileController
 *
 * This controller manages user profile operations such as viewing, updating profile details,
 * changing passwords, and uploading avatars. It ensures that only authenticated users can access these features.
 *
 * Thank you for keeping the user experience smooth and secure!
 */
class ProfileController extends Controller
{
    /**
     * Require authentication for all profile actions.
     *
     * This constructor applies the 'auth' middleware to ensure only logged-in users can access profile features.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the user's profile dashboard.
     *
     * Loads the current user's information and available roles for display on the profile page.
     */
    public function index()
    {
        $user = Auth::user();
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
        return view('users.profile', compact('user','roles','userRole'));
    }

    /**
     * Update the user's profile information (name and email).
     *
     * Validates and saves the updated profile details for the authenticated user.
     * Shows a success message on completion.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $user = Auth::user();
        $input = $request->only(['name', 'email']);

        $user->update($input);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update only the user's password.
     *
     * Validates and securely updates the password for the authenticated user.
     * Shows a success message on completion.
     */
    public function storeOnlyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'confirm_password' => 'required_with:password|same:password',
        ]);

        $input = $request->all();

        if ($request->filled('password')) {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password']);
        }

        auth()->user()->update($input);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's avatar image.
     *
     * Validates and stores a new avatar image for the authenticated user, keeping the profile fresh and personal.
     * Shows a success message on completion.
     */
    public function storeAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'image|mimes:jpg,jpeg,png',
        ], [
            'avatar.max' => 'The avatar file size must not exceed 2 MB.',
        ]);
        $input = $request->all();

        if ($request->hasFile('avatar')) {
            // Save the avatar in a private storage location
            $avatarPath = $request->file('avatar')->store('avatars', 'local');
            $input['avatar'] = basename($avatarPath);
        } else {
            unset($input['avatar']);
        }

        auth()->user()->update($input);

        return back()->with('success', 'Profile updated successfully.');
    }
}
