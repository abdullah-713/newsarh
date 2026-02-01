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
        Schema::create('trap_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('trap_type', 50)->unique();
            $table->string('trap_name', 100);
            $table->string('trap_name_ar', 100);
            $table->text('description')->nullable();
            $table->decimal('trigger_chance', 4, 2)->default(0.10);
            $table->unsignedInteger('cooldown_minutes')->default(10080);
            $table->unsignedInteger('min_role_level')->default(1);
            $table->unsignedInteger('max_role_level')->default(7);
            $table->boolean('is_active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trap_configurations');
    }
};
