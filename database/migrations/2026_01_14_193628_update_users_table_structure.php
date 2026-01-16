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
            $table->renameColumn('name', 'full_name');
            $table->unsignedInteger('role_id')->default(0); // Default to Employee (0)
            $table->foreign('role_id')->references('id')->on('roles');
            
            $table->foreignId('reporting_to')->nullable()->constrained('users')->onDelete('set null');
            $table->date('joining_date')->nullable();
            $table->string('status')->default('active'); // active, passive
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('full_name', 'name');
            
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
            
            $table->dropForeign(['reporting_to']);
            $table->dropColumn('reporting_to');
            
            $table->dropColumn('joining_date');
            $table->dropColumn('status');
        });
    }
};
