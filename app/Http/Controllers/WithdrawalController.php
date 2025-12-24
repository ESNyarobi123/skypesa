<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Services\FraudDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    protected FraudDetectionService $fraudService;

    public function __construct(FraudDetectionService $fraudService)
    {
        $this->fraudService = $fraudService;
    }

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
            'payment_name' => 'required|string|min:3|max:100|regex:/^[a-zA-Z\s]+$/',
            'payment_provider' => 'required|in:mpesa,tigopesa,airtelmoney,halopesa',
        ], [
            'amount.required' => 'Tafadhali weka kiasi.',
            'amount.min' => "Kiasi cha chini ni TZS " . number_format($minWithdrawal, 0),
            'amount.max' => 'Salio lako halitoshi.',
            'payment_number.required' => 'Tafadhali weka namba ya simu.',
            'payment_name.required' => 'Tafadhali weka jina kamili la mwenye akaunti.',
            'payment_name.min' => 'Jina liwe na angalau herufi 3.',
            'payment_name.regex' => 'Jina liwe na herufi tu (bila namba au alama).',
            'payment_provider.required' => 'Tafadhali chagua mtoa huduma.',
        ]);
        
        $amount = $request->amount;
        $fee = ($amount * $feePercent) / 100;
        $netAmount = $amount - $fee;
        
        try {
            DB::beginTransaction();
            
            // Create withdrawal request first (needed for risk assessment)
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'payment_method' => 'mobile_money',
                'payment_number' => $request->payment_number,
                'payment_name' => $request->payment_name,
                'payment_provider' => $request->payment_provider,
                'status' => 'pending',
            ]);
            
            // Now assess withdrawal risk using FraudDetectionService
            $riskAssessment = $this->fraudService->assessWithdrawalRisk($withdrawal);
            
            // Determine delay based on risk
            $delayHours = $riskAssessment['delay_hours'];
            $processableAt = now()->addHours($delayHours);
            
            // Log risk assessment
            Log::channel('fraud')->info('Withdrawal risk assessment', [
                'user_id' => $user->id,
                'amount' => $amount,
                'risk_score' => $riskAssessment['score'],
                'risk_level' => $riskAssessment['risk_level'],
                'delay_hours' => $delayHours,
                'factors' => $riskAssessment['factors'],
            ]);
            
            // Update withdrawal with risk data
            $withdrawal->update([
                'processable_at' => $processableAt,
                'delay_hours' => $delayHours,
                'risk_score' => $riskAssessment['score'],
                'risk_factors' => $riskAssessment['factors'],
                'is_frozen' => $riskAssessment['should_freeze'] ?? false,
                'freeze_reason' => ($riskAssessment['should_freeze'] ?? false)
                    ? 'Auto-frozen: High risk score (' . $riskAssessment['score'] . ')'
                    : null,
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
            
            // Build success message with delay info
            if ($delayHours >= 24) {
                $delayText = ceil($delayHours / 24) . ' siku';
            } else {
                $delayText = $delayHours . ' saa';
            }
            
            $message = 'Ombi lako limepokelewa! Utapata TZS ' . number_format($netAmount, 0);
            if ($delayHours > 0) {
                $message .= " baada ya {$delayText}";
            }
            if ($withdrawal->is_frozen) {
                $message .= ' (Inahitaji ukaguzi wa ziada)';
            }
            
            return redirect()->route('withdrawals.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Kuna tatizo. Jaribu tena baadaye.');
        }
    }
}
