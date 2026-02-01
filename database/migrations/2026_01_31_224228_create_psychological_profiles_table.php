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
        Schema::create('psychological_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('trust_score')->default(100);
            $table->integer('curiosity_score')->default(0);
            $table->integer('integrity_score')->default(100);
            $table->enum('profile_type', ['loyal_sentinel', 'curious_observer', 'opportunist', 'active_exploiter', 'potential_insider', 'undetermined'])->default('undetermined');
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->unsignedInteger('total_traps_seen')->default(0);
            $table->unsignedInteger('total_violations')->default(0);
            $table->timestamp('last_trap_at')->nullable();
            $table->timestamps();
            
            $table->index('profile_type');
            $table->index('risk_level');
            $table->index('trust_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psychological_profiles');
    }
};
