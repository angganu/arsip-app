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
        Schema::create('base_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_user_id')->unique()->constrained('base_users')->cascadeOnDelete();
            $table->string('avatar_path')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('mst_department_id')->nullable()->constrained('mst_departments')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_user_profiles');
    }
};
