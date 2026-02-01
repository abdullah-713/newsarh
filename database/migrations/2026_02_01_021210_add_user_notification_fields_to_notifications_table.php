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
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable()->after('message');
            $table->json('data')->nullable()->after('action_url');
            $table->boolean('is_read')->default(false)->after('data');
            $table->timestamp('read_at')->nullable()->after('is_read');
            
            $table->index('user_id');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['is_read']);
            $table->dropColumn(['user_id', 'body', 'data', 'is_read', 'read_at']);
        });
    }
};
