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
        Schema::create('base_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('base_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('base_role_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_user_id')->constrained('base_users')->cascadeOnDelete();
            $table->foreignId('base_role_id')->constrained('base_roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['base_user_id', 'base_role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_role_users');
        Schema::dropIfExists('base_roles');
        Schema::dropIfExists('base_users');
    }
};
