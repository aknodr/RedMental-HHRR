<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->nullable()
                ->after('id')
                ->constrained('clients')
                ->cascadeOnDelete();
        });

        // Drop the old (name, guard_name) unique so the same role name can exist
        // for multiple clients, then add (client_id, name, guard_name).
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
            $table->unique(['client_id', 'name', 'guard_name'], 'roles_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_scope_unique');
            $table->unique(['name', 'guard_name']);
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
