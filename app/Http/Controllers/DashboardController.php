<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Redirect admin to admin dashboard
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        // Get available tasks
        $tasks = Task::available()
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->take(6)
            ->get();
        
        // Get recent transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('dashboard', compact('tasks', 'recentTransactions'));
    }
}
