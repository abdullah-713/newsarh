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
        Schema::create('integrity_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('reported_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->enum('report_type', ['violation', 'harassment', 'theft', 'fraud', 'other'])->default('violation');
            $table->text('content');
            $table->json('evidence_files')->nullable();
            $table->boolean('is_anonymous_claim')->default(true);
            $table->text('admin_notes')->nullable();
            $table->enum('status', ['pending', 'investigating', 'resolved', 'dismissed', 'fake'])->default('pending');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('resolved_at')->nullable();
            $table->json('sender_revealed_to')->nullable();
            $table->timestamps();
            
            $table->index('sender_id');
            $table->index('reported_id');
            $table->index('status');
            $table->index(['created_at' => 'desc']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrity_reports');
    }
};
