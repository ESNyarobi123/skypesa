<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        
        // Check if input is phone or email
        $field = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [
            $field => $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Record login
            auth()->user()->recordLogin($request->ip());
            
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Taarifa ulizoweka si sahihi.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => 'required|accepted',
        ], [
            'name.required' => 'Tafadhali weka jina lako.',
            'email.required' => 'Tafadhali weka email yako.',
            'email.unique' => 'Email hii imeshatumika.',
            'phone.required' => 'Tafadhali weka namba ya simu.',
            'phone.unique' => 'Namba hii imeshatumika.',
            'password.required' => 'Tafadhali weka nenosiri.',
            'password.confirmed' => 'Nenosiri hazifanani.',
            'terms.accepted' => 'Lazima ukubali masharti na vigezo.',
        ]);

        // Check referral code
        $referredBy = null;
        if ($request->filled('referral_code')) {
            $referrer = User::where('referral_code', strtoupper($request->referral_code))->first();
            if ($referrer) {
                $referredBy = $referrer->id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'referred_by' => $referredBy,
        ]);

        Auth::login($user);
        
        $user->recordLogin($request->ip());

        return redirect(route('dashboard'))->with('success', 'Karibu SKYpesa! Akaunti yako imefunguliwa.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }
}
