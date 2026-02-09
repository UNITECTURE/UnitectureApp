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
            // Add requester_role_id to track the role of person who created the leave
            $table->unsignedBigInteger('requester_role_id')->after('user_id')->nullable();
            
            // Update status enum to include approved_by_superadmin
            DB::statement("ALTER TABLE leaves MODIFY COLUMN status ENUM('pending', 'approved_by_supervisor', 'approved', 'rejected', 'cancelled', 'approved_by_superadmin') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('requester_role_id');
            
            // Revert status enum
            DB::statement("ALTER TABLE leaves MODIFY COLUMN status ENUM('pending', 'approved_by_supervisor', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");
        });
    }
};
