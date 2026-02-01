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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('recorded_branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate()->comment('الفرع المسجل فيه الحضور');
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lng', 10, 7)->nullable();
            $table->decimal('check_out_lat', 10, 7)->nullable();
            $table->decimal('check_out_lng', 10, 7)->nullable();
            $table->string('check_in_address')->nullable();
            $table->string('check_out_address')->nullable();
            $table->enum('check_in_method', ['manual', 'auto_gps'])->default('manual')->comment('طريقة تسجيل الحضور');
            $table->decimal('check_in_distance', 10, 2)->nullable();
            $table->decimal('check_out_distance', 10, 2)->nullable();
            $table->unsignedInteger('work_minutes')->nullable();
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_leave_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->decimal('penalty_points', 10, 2)->default(0.00);
            $table->decimal('bonus_points', 10, 2)->default(0.00);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave', 'holiday'])->default('present');
            $table->text('notes')->nullable();
            $table->boolean('is_locked')->default(false)->comment('قفل السجل بعد الترحيل');
            $table->tinyInteger('mood_score')->nullable();
            $table->string('device_fingerprint', 64)->nullable();
            $table->json('fraud_flags')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
            $table->index('branch_id');
            $table->index('recorded_branch_id');
            $table->index('date');
            $table->index('status');
            $table->index('check_in_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
