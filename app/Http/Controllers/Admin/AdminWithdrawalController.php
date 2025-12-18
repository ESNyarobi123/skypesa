<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = Withdrawal::with('user');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $withdrawals = $query->latest()
            ->paginate(20)
            ->withQueryString();
        
        // Stats
        $pendingCount = Withdrawal::where('status', 'pending')->count();
        $pendingAmount = Withdrawal::where('status', 'pending')->sum('net_amount');
        
        return view('admin.withdrawals.index', compact('withdrawals', 'pendingCount', 'pendingAmount'));
    }

    public function approve(Request $request, Withdrawal $withdrawal)
    {
        if (!$withdrawal->isPending()) {
            return back()->with('error', 'Ombi hili haliwezi kukubaliwa.');
        }
        
        $withdrawal->approve(auth()->user(), $request->notes);
        
        return back()->with('success', 'Ombi limekubaliwa!');
    }

    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        if (!$withdrawal->isPending() && !$withdrawal->isProcessing()) {
            return back()->with('error', 'Ombi hili haliwezi kukataliwa.');
        }
        
        $withdrawal->reject($request->reason, auth()->user());
        
        return back()->with('success', 'Ombi limekataliwa na pesa imerudishwa.');
    }

    public function markPaid(Request $request, Withdrawal $withdrawal)
    {
        if (!$withdrawal->isApproved()) {
            return back()->with('error', 'Ombi hili lazima likubaliwe kwanza.');
        }
        
        $withdrawal->markAsPaid($request->zenopay_reference);
        
        return back()->with('success', 'Ombi limewekwa kama limelipwa!');
    }
}
