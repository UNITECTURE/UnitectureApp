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
        Schema::table('attendances', function (Blueprint $table) {
            // Change clock_in and clock_out from TIME to DATETIME
            $table->dateTime('clock_in')->nullable()->change();
            $table->dateTime('clock_out')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Revert back to TIME type
            $table->time('clock_in')->nullable()->change();
            $table->time('clock_out')->nullable()->change();
        });
    }
};
