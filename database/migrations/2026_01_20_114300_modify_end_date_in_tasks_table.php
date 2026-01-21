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
            // Change end_date to dateTime to support time selection
            // Note: This requires doctrine/dbal package if strictly modifying. 
            // However, in many recent Laravel versions standard types are supported better.
            // If this fails, ensure doctrine/dbal is installed: composer require doctrine/dbal
            $table->dateTime('end_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('end_date')->nullable()->change();
        });
    }
};
