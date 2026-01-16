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
        Schema::table('user', function (Blueprint $table) {
            
            // 1. Rename 'name' -> 'full_name' if needed
            if (Schema::hasColumn('user', 'name') && !Schema::hasColumn('user', 'full_name')) {
                $table->renameColumn('name', 'full_name');
            }

            // 2. Drop old columns if they exist
            if (Schema::hasColumn('user', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('user', 'department')) {
                $table->dropColumn('department');
            }

            // 3. Add 'role_id' and FK
            if (!Schema::hasColumn('user', 'role_id')) {
                $table->unsignedInteger('role_id')->default(0);
                // Note: Adding FK in SQLite sometimes requires separate command or table rebuild, but Laravel handles it mostly.
                // If this fails, we catch it? No, keep it simple.
                $table->foreign('role_id')->references('id')->on('roles');
            }

            // 4. Add 'reporting_to' and FK
            if (!Schema::hasColumn('user', 'reporting_to')) {
                $table->foreignId('reporting_to')->nullable();
                $table->foreign('reporting_to')->references('id')->on('user')->onDelete('set null');
            }

            // 5. Add 'joining_date'
            if (!Schema::hasColumn('user', 'joining_date')) {
                $table->date('joining_date')->nullable();
            }

            // 6. Add 'status'
            if (!Schema::hasColumn('user', 'status')) {
                $table->string('status')->default('active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            //
        });
    }
};
