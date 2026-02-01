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
        Schema::create('trap_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('trap_type', 50);
            $table->foreignId('trap_config_id')->nullable()->constrained('trap_configurations')->nullOnDelete()->cascadeOnUpdate();
            $table->string('action_taken', 50);
            $table->enum('action_category', ['positive', 'neutral', 'negative', 'critical'])->default('neutral');
            $table->integer('score_change')->default(0);
            $table->integer('trust_delta')->default(0);
            $table->integer('curiosity_delta')->default(0);
            $table->integer('integrity_delta')->default(0);
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->json('context_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('user_id');
            $table->index('trap_type');
            $table->index('action_category');
            $table->index(['created_at' => 'desc']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trap_logs');
    }
};
