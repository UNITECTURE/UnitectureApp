<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AccrueLeaves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaves:accrue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Accrue 1.25 days of leave for all active users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Starting Leave Accrual Process ---');
        
        $today = now();
        $threeMonthsAgo = $today->copy()->subMonths(3)->toDateString();
        $currentMonth = $today->format('Y-m');
        
        $this->comment("Checking users who joined on or before: {$threeMonthsAgo}");
        $this->comment("Current Accrual Month: {$currentMonth}");

        $users = User::where('status', 'active')
            ->where('joining_date', '<=', $threeMonthsAgo)
            ->where(function($query) use ($currentMonth) {
                $query->whereNull('last_accrued_month')
                      ->orWhere('last_accrued_month', '!=', $currentMonth);
            })
            ->get();
        
        if ($users->isEmpty()) {
            $this->warn('No eligible users found for accrual this month (or all already accrued).');
            return;
        }

        $count = 0;
        foreach ($users as $user) {
            $oldBalance = $user->leave_balance;
            
            // Check if balance will exceed 25 after accrual
            $newBalance = $user->leave_balance + 1.25;
            
            if ($newBalance >= 25) {
                // Reset to 0 and log the reset
                $user->update(['leave_balance' => 0, 'last_accrued_month' => $currentMonth]);
                $this->line("🔄 Balance Reset for: <info>{$user->name}</info>");
                $this->line("   [Previous Balance: {$oldBalance} | Reset to: 0 (reached 25 threshold)]");
            } else {
                // Normal accrual
                $user->increment('leave_balance', 1.25);
                $user->update(['last_accrued_month' => $currentMonth]);
                
                $newBalance = $user->leave_balance;
                $this->line("✅ Accrued 1.25 for: <info>{$user->name}</info>");
                $this->line("   [Joined: {$user->joining_date->format('Y-m-d')} | Balance: {$oldBalance} -> {$newBalance}]");
            }
            $count++;
        }

        $this->info("--- Successfully processed leaves for {$count} users ---");
        Log::info("Leaves processed for {$count} users for month {$currentMonth}.");
    }
}
