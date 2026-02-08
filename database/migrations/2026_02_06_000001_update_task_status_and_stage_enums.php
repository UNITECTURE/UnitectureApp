<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Make status an enum again, aligned with TaskController::STATUSES
            // Keep 'not_started' to remain compatible with any existing rows that used the old default.
            $table->enum('status', [
                'not_started',
                'wip',
                'correction',
                'completed',
                'revision',
                'closed',
                'hold',
                'under_review',
                'awaiting_resources',
            ])->default('not_started')->change();

            // Ensure stage is explicitly an enum with the canonical set as well.
            $table->enum('stage', [
                'overdue',
                'pending',
                'in_progress',
                'completed',
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Revert status back to string (as in 2026_01_20_122300_update_task_statuses.php)
            $table->string('status')->default('not_started')->change();

            // Stage column definition from 2026_01_22_000002_add_stage_to_tasks_table.php
            $table->enum('stage', ['overdue', 'pending', 'in_progress', 'completed'])
                ->default('pending')
                ->change();
        });
    }
};

