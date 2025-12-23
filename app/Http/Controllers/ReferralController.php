<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $referrals = User::where('referred_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $totalReferrals = $referrals->total();
        
        // Get referral settings from admin
        $referralBonusReferrer = Setting::get('referral_bonus_referrer', 500);
        $referralBonusNewUser = Setting::get('referral_bonus_new_user', 200);
        $referralEnabled = Setting::get('referral_enabled', true);
        $referralRequireTask = Setting::get('referral_require_task_completion', true);
        
        // Calculate total earnings from referrals
        $referralEarnings = $user->wallet?->transactions()
            ->where('category', 'referral')
            ->where('type', 'credit')
            ->sum('amount') ?? 0;
        
        return view('referrals.index', compact(
            'referrals', 
            'totalReferrals',
            'referralBonusReferrer',
            'referralBonusNewUser',
            'referralEnabled',
            'referralRequireTask',
            'referralEarnings'
        ));
    }
}
