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
            if (Schema::hasColumn('tasks', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('tasks', 'time_estimate')) {
                $table->dropColumn('time_estimate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('title')->after('project_id');
            $table->string('time_estimate')->nullable()->after('end_date');
        });
    }
};
