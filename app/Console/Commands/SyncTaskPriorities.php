<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;

class SyncTaskPriorities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:sync-priorities {--ids=* : Optional task IDs to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync task priority automatically based on remaining time to deadline';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ids = $this->option('ids');
        $affected = Task::bulkSyncPrioritiesFromDeadlines(now(), !empty($ids) ? $ids : null);

        $this->info("Synced task priorities. Rows affected: {$affected}");

        return self::SUCCESS;
    }
}

