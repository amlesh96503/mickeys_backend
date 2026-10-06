<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_admin_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();

            // ========== ROLE ==========
            $table->enum('role', ['super_admin', 'admin', 'manager', 'editor', 'viewer'])
                ->default('admin');

            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('permissions')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('status');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
