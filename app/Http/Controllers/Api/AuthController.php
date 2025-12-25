<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => ['required', 'confirmed', Password::min(6)],
            'referral_code' => 'nullable|string|exists:users,referral_code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find referrer if code provided
        $referrer = null;
        if ($request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by' => $referrer?->id,
        ]);

        // Create wallet
        $user->wallet()->create(['balance' => 0]);

        // Create free subscription
        $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
        if ($freePlan) {
            $user->subscriptions()->create([
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'started_at' => now(),
            ]);
        }

        // Send Welcome Email
        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email (API): ' . $e->getMessage());
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Akaunti imefunguliwa!',
            'data' => [
                'user' => $this->userResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Attempt login
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email au password si sahihi',
            ], 401);
        }

        $user = Auth::user();

        // Check if active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akaunti yako imezuiwa. Wasiliana na msaada.',
            ], 403);
        }

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Generate token
        $token = $user->createToken($request->device_name ?? 'auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Karibu tena!',
            'data' => [
                'user' => $this->userResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Umetoka kwenye akaunti',
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Delete current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email haipatikani',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $otp = rand(100000, 999999);
        
        // Store OTP in cache for 15 minutes
        \Cache::put('password_reset_otp_' . $email, $otp, 900);

        try {
            Mail::to($email)->send(new \App\Mail\PasswordResetOtpMail($otp));
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Imeshindikana kutuma email. Jaribu tena baadaye.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Maelekezo ya kubadilisha password yametumwa kwenye email yako',
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string', // This is the OTP
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $otp = $request->token;
        
        $cachedOtp = \Cache::get('password_reset_otp_' . $email);
        
        if (!$cachedOtp || $cachedOtp != $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kodi ya uhakiki si sahihi au imeisha muda wake',
            ], 400);
        }

        $user = User::where('email', $email)->first();
        $user->update(['password' => Hash::make($request->password)]);
        
        // Clear OTP
        \Cache::forget('password_reset_otp_' . $email);

        return response()->json([
            'success' => true,
            'message' => 'Password imebadilishwa. Ingia sasa.',
        ]);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $otp = $request->code;
        
        $cachedOtp = \Cache::get('email_verification_otp_' . $email);
        
        if (!$cachedOtp || $cachedOtp != $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kodi ya uhakiki si sahihi au imeisha muda wake',
            ], 400);
        }

        $user = User::where('email', $email)->first();
        
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email tayari imethibitishwa!',
            ]);
        }
        
        $user->markEmailAsVerified();
        
        // Clear OTP
        \Cache::forget('email_verification_otp_' . $email);

        return response()->json([
            'success' => true,
            'message' => 'Email imethibitishwa!',
        ]);
    }

    /**
     * Resend verification
     */
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email tayari imethibitishwa',
            ], 400);
        }

        $otp = rand(100000, 999999);
        \Cache::put('email_verification_otp_' . $user->email, $otp, 900);

        try {
            Mail::to($user->email)->send(new \App\Mail\EmailVerificationOtpMail($otp));
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Imeshindikana kutuma email. Jaribu tena baadaye.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Code mpya imetumwa',
        ]);
    }

    /**
     * Format user resource
     */
    protected function userResource(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->getAvatarUrl(),
            'role' => $user->role,
            'referral_code' => $user->referral_code,
            'is_verified' => $user->is_verified,
            'wallet' => [
                'balance' => $user->wallet?->balance ?? 0,
            ],
            'subscription' => $user->activeSubscription ? [
                'plan' => $user->activeSubscription->plan->name,
                'expires_at' => $user->activeSubscription->expires_at?->toISOString(),
            ] : null,
            'created_at' => $user->created_at->toISOString(),
        ];
    }
}
