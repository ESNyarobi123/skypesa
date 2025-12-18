<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('withdrawals.index', compact('withdrawals'));
    }

    public function create()
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        $plan = $user->getCurrentPlan();
        
        return view('withdrawals.create', compact('wallet', 'plan'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        $plan = $user->getCurrentPlan();
        
        $minWithdrawal = $plan?->min_withdrawal ?? 10000;
        $feePercent = $plan?->withdrawal_fee_percent ?? 20;
        
        $request->validate([
            'amount' => "required|numeric|min:{$minWithdrawal}|max:" . $wallet->getAvailableBalance(),
            'payment_number' => 'required|string|max:20',
            'payment_provider' => 'required|in:mpesa,tigopesa,airtelmoney,halopesa',
        ], [
            'amount.required' => 'Tafadhali weka kiasi.',
            'amount.min' => "Kiasi cha chini ni TZS " . number_format($minWithdrawal, 0),
            'amount.max' => 'Salio lako halitoshi.',
            'payment_number.required' => 'Tafadhali weka namba ya simu.',
            'payment_provider.required' => 'Tafadhali chagua mtoa huduma.',
        ]);
        
        $amount = $request->amount;
        $fee = ($amount * $feePercent) / 100;
        $netAmount = $amount - $fee;
        
        try {
            DB::beginTransaction();
            
            // Create withdrawal request
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'payment_method' => 'mobile_money',
                'payment_number' => $request->payment_number,
                'payment_provider' => $request->payment_provider,
                'status' => 'pending',
            ]);
            
            // Debit wallet and set pending
            $wallet->debit($amount, 'withdrawal', $withdrawal, 'Ombi la kutoa pesa');
            $wallet->increment('pending_withdrawal', $amount);
            
            // Create fee transaction
            if ($fee > 0) {
                $wallet->transactions()->create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'reference' => 'FEE' . strtoupper(\Str::random(10)),
                    'type' => 'debit',
                    'category' => 'withdrawal_fee',
                    'amount' => $fee,
                    'balance_before' => $wallet->balance + $fee,
                    'balance_after' => $wallet->balance,
                    'description' => 'Ada ya kutoa pesa',
                    'transactionable_type' => Withdrawal::class,
                    'transactionable_id' => $withdrawal->id,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('withdrawals.index')
                ->with('success', 'Ombi lako limepokelewa! Utapata TZS ' . number_format($netAmount, 0) . ' ndani ya siku ' . ($plan?->processing_days ?? 7));
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Kuna tatizo. Jaribu tena baadaye.');
        }
    }
}
