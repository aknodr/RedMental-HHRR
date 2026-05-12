<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->nullOnDelete();
            $table->boolean('is_super_admin')->default(false)->after('password');
            $table->boolean('active')->default(true)->after('is_super_admin');
            $table->string('phone')->nullable()->after('active');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'is_super_admin', 'active', 'phone', 'avatar_path', 'last_login_at']);
        });
    }
};
