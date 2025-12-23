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
        
        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Search by name, phone, or payment_name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('payment_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
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

    /**
     * Bulk approve multiple withdrawals
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'withdrawal_ids' => 'required|array|min:1',
            'withdrawal_ids.*' => 'exists:withdrawals,id',
        ]);

        $approved = 0;
        $skipped = 0;

        foreach ($request->withdrawal_ids as $id) {
            $withdrawal = Withdrawal::find($id);
            
            if ($withdrawal && $withdrawal->isPending()) {
                $withdrawal->approve(auth()->user(), 'Bulk approved by admin');
                $approved++;
            } else {
                $skipped++;
            }
        }

        $message = "Approved: {$approved}";
        if ($skipped > 0) {
            $message .= ", Skipped: {$skipped} (not pending)";
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk reject multiple withdrawals
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'withdrawal_ids' => 'required|array|min:1',
            'withdrawal_ids.*' => 'exists:withdrawals,id',
            'reason' => 'required|string|max:500',
        ]);

        $rejected = 0;
        $skipped = 0;

        foreach ($request->withdrawal_ids as $id) {
            $withdrawal = Withdrawal::find($id);
            
            if ($withdrawal && ($withdrawal->isPending() || $withdrawal->isProcessing())) {
                $withdrawal->reject($request->reason, auth()->user());
                $rejected++;
            } else {
                $skipped++;
            }
        }

        $message = "Rejected: {$rejected}";
        if ($skipped > 0) {
            $message .= ", Skipped: {$skipped} (not pending/processing)";
        }

        return back()->with('success', $message);
    }

    /**
     * Export withdrawals to CSV
     */
    public function export(Request $request)
    {
        $query = Withdrawal::with('user');
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $withdrawals = $query->latest()->get();
        
        $filename = 'withdrawals_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($withdrawals) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'ID',
                'User',
                'Phone',
                'Amount',
                'Fee',
                'Net Amount',
                'Status',
                'Payment Number',
                'Account Name',
                'Provider',
                'Created At',
                'Processed At',
            ]);
            
            foreach ($withdrawals as $withdrawal) {
                fputcsv($file, [
                    $withdrawal->id,
                    $withdrawal->user->name ?? 'N/A',
                    $withdrawal->user->phone ?? 'N/A',
                    $withdrawal->amount,
                    $withdrawal->fee,
                    $withdrawal->net_amount,
                    $withdrawal->status,
                    $withdrawal->payment_number,
                    $withdrawal->payment_name ?? '',
                    $withdrawal->payment_provider ?? '',
                    $withdrawal->created_at->format('Y-m-d H:i:s'),
                    $withdrawal->paid_at ? $withdrawal->paid_at->format('Y-m-d H:i:s') : '',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
