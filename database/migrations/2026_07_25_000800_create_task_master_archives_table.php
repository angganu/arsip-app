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
        Schema::create('task_master_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_master_id')->constrained()->cascadeOnDelete();
            $table->string('name', 191)->comment('generated archive file name');
            $table->string('path', 191)->comment('path on storage disk');
            $table->string('extension', 20)->default('pdf');
            $table->integer('size')->nullable()->comment('in Kb');
            $table->string('description', 191)->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
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
        Schema::dropIfExists('task_master_archives');
    }
};
