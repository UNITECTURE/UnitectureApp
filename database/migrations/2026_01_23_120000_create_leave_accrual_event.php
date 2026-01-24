<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable event scheduler
        DB::statement('SET GLOBAL event_scheduler = ON');

        // Create the MySQL Event for monthly leave accrual
        DB::statement("
            CREATE EVENT IF NOT EXISTS `leave_accrual_event`
            ON SCHEDULE EVERY 1 MONTH
            STARTS DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL 0 DAY)
            DO
            BEGIN
                DECLARE currentMonth VARCHAR(7);
                SET currentMonth = DATE_FORMAT(NOW(), '%Y-%m');

                -- Update leave balance for eligible users
                UPDATE users
                SET 
                    leave_balance = CASE 
                        WHEN (leave_balance + 1.25) >= 25 THEN 0
                        ELSE leave_balance + 1.25
                    END,
                    last_accrued_month = currentMonth,
                    updated_at = NOW()
                WHERE 
                    status = 'active'
                    AND joining_date <= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                    AND (
                        last_accrued_month IS NULL 
                        OR last_accrued_month != currentMonth
                    );
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the event
        DB::statement('DROP EVENT IF EXISTS `leave_accrual_event`');
    }
};
