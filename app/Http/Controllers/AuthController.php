<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * AUTH SECTION
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validation
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Please provide username.',
            'password.required' => 'Please provide password.',
        ]);

        // Get User
        $user = User::where('username', $request->username)->first();

        // Find if user exist
        if (!$user) {
            return back()->withErrors(['username' => 'Username not found.']);
        }

        // Password Validation
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        // Check user status
        if ($user->is_active === FALSE) {
            return back()->withErrors(['username' => 'This account is inactive']);
        }

        // Generate session
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Redirect to dashboard
        return redirect()->intended('/')->with('success', 'Welcome');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Off You Go.');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:4|confirmed',
        ]);

        $user = Auth::user();

        $user->name = $request->name;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        /** @disregard P1013 */
        $user->save();

        return redirect()->back()->with('success', 'Profile updated.');
    }
}
