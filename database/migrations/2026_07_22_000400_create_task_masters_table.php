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
        Schema::create('task_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 25)->nullable()->comment('Kode: GA-2603001');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('has_schedule')->default(false)->comment('0: false; 1: true;');
            $table->unsignedSmallInteger('interval_schedule')->default(0)->comment('0: Others / Unscheduled; 1: Hourly; 2: Daily; 3: Monthly; 4: Yearly;');
            $table->unsignedSmallInteger('interval_value')->default(0);
            $table->unsignedBigInteger('planned_by')->nullable()->comment('user id');
            $table->date('date_planning_start')->nullable();
            $table->date('date_planning_finish')->nullable();
            $table->unsignedInteger('duration_planning')->nullable()->comment('total durasi perencanaan (minutes)');
            $table->date('date_realization_start')->nullable()->comment('tanggal submit pertama kali');
            $table->date('date_realization_finish')->nullable()->comment('tanggal submit terakhir');
            $table->unsignedInteger('duration_realization')->nullable()->comment('total durasi submition berdasarkan durasi employee (minutes)');
            $table->unsignedInteger('priority')->default(0)->comment('0: not set; 1: low; 2: medium; 3: high; 4: urgent;');
            $table->unsignedSmallInteger('status')->default(0)->comment('0: new planning; 1: sedang di proses; 2: selesai; 3: cancelled; 4: hold; 5: draft');
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
        Schema::dropIfExists('task_masters');
    }
};
