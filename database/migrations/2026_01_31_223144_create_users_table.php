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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('emp_code', 50)->unique();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('full_name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();
            $table->foreignId('role_id')->default(1)->constrained('roles')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('managed_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('department', 100)->nullable()->comment('Legacy field');
            $table->string('job_title', 100)->nullable()->comment('Legacy field');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('job_title_id')->nullable()->constrained('job_titles')->nullOnDelete()->cascadeOnUpdate();
            $table->date('hire_date')->nullable();
            $table->string('national_id', 20)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_super_admin')->default(false)->index()->comment('صلاحيات مطلقة');
            $table->boolean('is_online')->default(false)->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->decimal('last_latitude', 10, 7)->nullable();
            $table->decimal('last_longitude', 10, 7)->nullable();
            $table->unsignedTinyInteger('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->rememberToken();
            $table->decimal('current_points', 10, 2)->default(0.00);
            $table->decimal('total_points_earned', 10, 2)->default(0.00);
            $table->decimal('total_points_deducted', 10, 2)->default(0.00);
            $table->unsignedInteger('streak_count')->default(0)->comment('عداد الأيام المثالية المتتالية');
            $table->json('preferences')->nullable();
            $table->json('custom_schedule')->nullable();
            $table->json('permissions')->nullable()->comment('صلاحيات فردية للمستخدم');
            $table->json('visible_modules')->nullable()->comment('الوحدات المرئية للمستخدم');
            $table->enum('theme_mode', ['light', 'dark', 'auto'])->default('auto');
            $table->time('dark_mode_start')->default('18:00:00');
            $table->time('dark_mode_end')->default('06:00:00');
            $table->timestamps();
            
            $table->index('role_id');
            $table->index('branch_id');
            $table->index('managed_by');
            $table->index('department_id');
            $table->index('team_id');
            $table->index('job_title_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
