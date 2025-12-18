<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        
        return view('referrals.index', compact('referrals', 'totalReferrals'));
    }
}
