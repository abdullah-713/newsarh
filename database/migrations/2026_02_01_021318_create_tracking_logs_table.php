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
        Schema::create('tracking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('battery_level')->nullable();
            $table->decimal('speed', 8, 2)->nullable()->comment('km/h');
            $table->decimal('accuracy', 8, 2)->nullable()->comment('meters');
            $table->enum('type', ['route', 'ping', 'check_in', 'check_out'])->default('ping');
            $table->string('activity_type')->nullable()->comment('still, walking, running, driving');
            $table->boolean('is_mock_location')->default(false);
            $table->timestamp('tracked_at');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('type');
            $table->index('tracked_at');
            $table->index(['user_id', 'tracked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_logs');
    }
};
