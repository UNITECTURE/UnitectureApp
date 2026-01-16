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
        Schema::table('users', function (Blueprint $table) {
            // Add new fields
            $table->unsignedTinyInteger('role_id')->default(0)->after('password');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreignId('reporting_to')->nullable()->after('role_id')->constrained('users');
            $table->date('joining_date')->nullable()->after('reporting_to');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('joining_date');
            
            // Drop old fields
            $table->dropColumn(['role', 'department']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['users_role_id_foreign']);
            $table->dropForeign(['users_reporting_to_foreign']);
            $table->dropColumn(['role_id', 'reporting_to', 'joining_date', 'status']);
        });
    }
};
