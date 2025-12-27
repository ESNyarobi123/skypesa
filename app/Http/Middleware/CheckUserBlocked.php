<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check if user is blocked
 * 
 * If user is blocked, redirect them to blocked page (web)
 * or return JSON error (API)
 */
class CheckUserBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isBlocked()) {
            // Check if this is an API request
            if ($request->expectsJson() || $request->is('api/*')) {
                $adminWhatsApp = Setting::get('whatsapp_support_number', '255700000000');
                $cleanNumber = preg_replace('/[^0-9]/', '', $adminWhatsApp);
                
                return response()->json([
                    'status' => 'blocked',
                    'message' => 'Akaunti yako imezuiwa. Wasiliana na admin kupitia WhatsApp.',
                    'is_blocked' => true,
                    'blocking_info' => $user->getBlockingInfo(),
                    'support' => [
                        'whatsapp' => $adminWhatsApp,
                        'whatsapp_url' => 'https://wa.me/' . $cleanNumber,
                    ],
                ], 403);
            }

            // Web request - redirect to blocked page
            return redirect()->route('user.blocked');
        }

        return $next($request);
    }
}
