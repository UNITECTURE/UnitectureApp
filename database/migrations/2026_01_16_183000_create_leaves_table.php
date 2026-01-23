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
        Schema::dropIfExists('leaves');
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('leave_type', ['paid', 'unpaid']);
            $table->text('reason');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days', 5, 1); // Calculated days (e.g., 2.5) -- actually date diff is usually whole, but half days possible? Sticking to PDF "1.25" balance implies decimals.
            $table->enum('status', ['pending', 'approved_by_supervisor', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
