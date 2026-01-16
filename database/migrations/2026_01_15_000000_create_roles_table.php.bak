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
        Schema::create('roles', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary(); // 0, 2, 3 as requested
            $table->string('name');
            $table->timestamps();
        });

        // Seed the roles as requested
        DB::table('roles')->insert([
            ['id' => 0, 'name' => 'employee', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'supervisor', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
