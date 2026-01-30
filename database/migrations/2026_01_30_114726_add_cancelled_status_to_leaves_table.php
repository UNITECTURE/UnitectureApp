<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            // Modify the status column to include 'cancelled'
            $table->enum('status', ['pending', 'approved_by_supervisor', 'approved', 'rejected', 'cancelled'])
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('status', ['pending', 'approved_by_supervisor', 'approved', 'rejected'])
                  ->default('pending')
                  ->change();
        });
    }
};
