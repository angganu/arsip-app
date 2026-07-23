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
        Schema::create('task_discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_master_id')->constrained('task_masters')->cascadeOnDelete();
            $table->foreignId('base_user_id')->constrained('base_users')->cascadeOnDelete();
            $table->text('message');
            $table->tinyInteger('is_read')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_discussions');
    }
};
