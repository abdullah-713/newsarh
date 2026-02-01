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
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->time('work_start_time')->default('08:00:00');
            $table->time('work_end_time')->default('17:00:00');
            $table->unsignedInteger('grace_period_minutes')->default(15);
            $table->enum('attendance_mode', ['unrestricted', 'time_only', 'location_only', 'time_and_location'])->default('time_and_location');
            $table->json('working_days')->nullable()->comment('أيام العمل [0=الأحد, 6=السبت]');
            $table->json('allowed_branches')->nullable()->comment('الفروع المسموح التسجيل منها');
            $table->unsignedInteger('geofence_radius')->default(100)->comment('نصف قطر السماح بالمتر');
            $table->boolean('is_flexible_hours')->default(false);
            $table->decimal('min_working_hours', 4, 2)->default(8.00);
            $table->decimal('max_working_hours', 4, 2)->default(12.00);
            $table->unsignedInteger('early_checkin_minutes')->default(30);
            $table->boolean('late_checkout_allowed')->default(true);
            $table->boolean('overtime_allowed')->default(true);
            $table->boolean('remote_checkin_allowed')->default(false);
            $table->decimal('late_penalty_per_minute', 5, 2)->default(0.50);
            $table->decimal('early_bonus_points', 5, 2)->default(5.00);
            $table->decimal('overtime_bonus_per_hour', 5, 2)->default(10.00);
            $table->boolean('is_active')->default(true)->index();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            
            $table->index('attendance_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_schedules');
    }
};
