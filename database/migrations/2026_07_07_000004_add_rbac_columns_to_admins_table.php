<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (!Schema::hasColumn('admins', 'role_id')) {
                $table->foreignId('role_id')
                    ->nullable()
                    ->after('password')
                    ->constrained('roles')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('admins', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('role_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }

            if (Schema::hasColumn('admins', 'role_id')) {
                $table->dropConstrainedForeignId('role_id');
            }

            if (Schema::hasColumn('admins', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
