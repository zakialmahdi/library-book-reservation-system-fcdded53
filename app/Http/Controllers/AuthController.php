<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Render Register Form
    public function showRegister()
    {
        return view('auth.register');
    }

    // Handle User Registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create User with Hashed Password
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Auto-login the user after registration
        Auth::login($user);

        return redirect()->route('books.index')
            ->with('success', 'Account created successfully! Welcome aboard.');
    }

    // Render Login Form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Handle User Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Attempt Authentication
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('books.index'))
                ->with('success', 'Logged in successfully!');
        }

        // Return back if authentication fails
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handle Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logged out successfully.');
    }
}

