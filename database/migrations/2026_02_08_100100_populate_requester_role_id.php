<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate requester_role_id for all existing leaves based on the user's role_id
        DB::statement('
            UPDATE leaves
            SET requester_role_id = (
                SELECT role_id FROM users WHERE users.id = leaves.user_id
            )
            WHERE requester_role_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set back to NULL if rolling back
        DB::statement('UPDATE leaves SET requester_role_id = NULL');
    }
};
