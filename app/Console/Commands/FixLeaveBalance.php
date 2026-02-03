<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class FixLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaves:fix-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix corrupted leave balances and reset to 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Fixing Leave Balances ===');
        
        $users = User::where('status', 'active')->get();
        
        foreach ($users as $user) {
            $oldBalance = $user->leave_balance;
            
            // Reset all balances to 0 and clear last_accrued_month
            $user->update([
                'leave_balance' => 0,
                'last_accrued_month' => null
            ]);
            
            $this->line("✅ Fixed: <info>{$user->name}</info> | {$oldBalance} → 0");
        }
        
        $this->info("=== All leave balances reset to 0 ===");
        $this->info("Balances will be recalculated automatically when users access leaves page!");
    }
}
