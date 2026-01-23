<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // We will modify the status column to include new statuses.
        // Using DB statement for enum modification to be explicit for MySQL/Postgres if needed, 
        // but for now we'll try to change it to string or expanded enum using Schema builder if possible.
        // To be safe and flexible, let's change it to string, or just update the enum definition.

        Schema::table('tasks', function (Blueprint $table) {
            // It is often safer to change to string to avoid enum constraints during development
            // or re-define the enum. Let's re-define the enum.
            // Note: DB::statement is used because modifying enums in pure Eloquent can be tricky depending on the driver.

            $table->string('status')->default('not_started')->change();
            // We'll treat it as string in DB but validate in app to allow all the new statuses.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Revert to original enum if needed, but string is compatible
            // $table->enum('status', ['todo', 'in_progress', 'review', 'done'])->default('todo')->change();
        });
    }
};
