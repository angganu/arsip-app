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
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_master_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_detail_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 191)->comment('nama file yang disimpan (code)');
            $table->string('original_name', 191)->nullable()->comment('nama file asli');
            $table->string('path', 191);
            $table->string('extension', 191)->nullable();
            $table->integer('size')->nullable()->comment('in Kb');
            $table->string('description', 191)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_attachments');
    }
};
