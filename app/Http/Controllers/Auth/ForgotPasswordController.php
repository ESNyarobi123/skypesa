<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetOtp;
use App\Mail\PasswordResetOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show the email request form
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send OTP to user email
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $email = $request->email;
        $otp = rand(100000, 999999);

        // Save OTP
        PasswordResetOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send Email
        try {
            Mail::to($email)->send(new PasswordResetOtpMail($otp));
            return redirect()->route('password.otp', ['email' => $email])
                ->with('success', 'Kodi ya uhakiki imetumwa kwenye email yako.');
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
            return back()->with('error', 'Imeshindikana kutuma email. Tafadhali jaribu tena baadaye.');
        }
    }

    /**
     * Show the OTP verification form
     */
    public function showOtpForm(Request $request)
    {
        $email = $request->email;
        return view('auth.passwords.otp', compact('email'));
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $otpRecord = PasswordResetOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return back()->with('error', 'Kodi ya uhakiki si sahihi au imeshaisha muda wake.');
        }

        // Mark as used
        $otpRecord->update(['is_used' => true]);

        // Store email in session for the next step
        session(['reset_email' => $request->email]);

        return redirect()->route('password.reset.form')
            ->with('success', 'Uhakiki umekamilika. Sasa weka nenosiri jipya.');
    }

    /**
     * Show the password reset form
     */
    public function showResetForm()
    {
        if (!session('reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.passwords.reset');
    }

    /**
     * Reset the password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Mtumiaji hajapatikana.');
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Clear session
        session()->forget('reset_email');

        return redirect()->route('login')->with('success', 'Nenosiri lako limebadilishwa kikamilifu. Sasa unaweza kuingia.');
    }
}
