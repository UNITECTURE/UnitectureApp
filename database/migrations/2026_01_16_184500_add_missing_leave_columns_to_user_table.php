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
            if (!Schema::hasColumn('user', 'leave_balance')) {
                $table->decimal('leave_balance', 5, 2)->default(0.00)->after('status');
            }
            if (!Schema::hasColumn('user', 'last_accrued_month')) {
                $table->string('last_accrued_month', 7)->nullable()->after('leave_balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['leave_balance', 'last_accrued_month']);
        });
    }
};
