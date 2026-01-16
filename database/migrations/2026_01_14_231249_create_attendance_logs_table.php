<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('biometric_id')->index(); // Index for faster searching
            $table->dateTime('punch_time');
            $table->string('device_id', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
            // Note: User spec says created_at DEFAULT CURRENT_TIMESTAMP. 
            // Laravel's timestamps() creates created_at/updated_at. 
            // I'll stick close to user spec but usually we want standard laravel models.
            // But User said "Raw logs...". I'll use explicit column.
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_logs');
    }
};
