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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('challenge_type', ['individual', 'team', 'branch', 'company'])->default('individual');
            $table->enum('target_type', ['attendance_count', 'no_late', 'early_arrival', 'overtime', 'points', 'custom']);
            $table->integer('target_value')->default(1);
            $table->integer('reward_points')->default(100);
            $table->foreignId('reward_badge_id')->nullable()->constrained('badges')->nullOnDelete()->cascadeOnUpdate();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('max_participants')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
