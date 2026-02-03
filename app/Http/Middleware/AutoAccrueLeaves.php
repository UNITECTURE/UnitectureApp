<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoAccrueLeaves
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $today = now();
            $currentMonth = $today->format('Y-m');
            $threeMonthsAgo = $today->copy()->subMonths(3)->toDateString();

            // Logic: Active user, joined > 3 months ago, not yet accrued this month
            if ($user->status === 'active' && 
                $user->joining_date->toDateString() <= $threeMonthsAgo && 
                $user->last_accrued_month !== $currentMonth) {
                
                $user->increment('leave_balance', 1.25);
                $user->update(['last_accrued_month' => $currentMonth]);
                
                // Optional: Log it or add a flash message
                // session()->flash('success', 'Monthly leave credits added automatically!');
            }
        }

        return $next($request);
    }
}
