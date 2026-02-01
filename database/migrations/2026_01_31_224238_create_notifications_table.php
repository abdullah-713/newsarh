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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->default('info');
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('icon', 50)->default('bi-bell');
            $table->enum('scope_type', ['global', 'branch', 'user'])->default('user');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_persistent')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('type');
            $table->index(['scope_type', 'scope_id']);
            $table->index('expires_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
