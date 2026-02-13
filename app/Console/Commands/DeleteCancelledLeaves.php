<?php

namespace App\Console\Commands;

use App\Models\Leave;
use Illuminate\Console\Command;

class DeleteCancelledLeaves extends Command
{
    protected $signature = 'delete:cancelled-leaves';

    protected $description = 'Delete all cancelled leaves from the database';

    public function handle()
    {
        $count = Leave::where('status', 'cancelled')->count();

        if ($count === 0) {
            $this->info('No cancelled leaves found in the database.');
            return Command::SUCCESS;
        }

        $this->warn("Found {$count} cancelled leave(s) to delete.");
        
        if (!$this->confirm("Are you sure you want to delete {$count} cancelled leave(s)? This cannot be undone.")) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        Leave::where('status', 'cancelled')->delete();

        $this->info("✓ Successfully deleted {$count} cancelled leave(s) from the database.");
        return Command::SUCCESS;
    }
}
