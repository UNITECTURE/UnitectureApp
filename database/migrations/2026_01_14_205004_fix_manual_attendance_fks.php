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
        Schema::dropIfExists('manual_attendance_requests');
        
        Schema::create('manual_attendance_requests', function (Blueprint $table) {
            $table->id();
            // Reference 'users' table
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->date('date');
            $table->string('duration');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            
            // Reference 'users' table for approver
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_attendance_requests');
    }
};
