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
        Schema::create('task_details', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->comment('Kode: GA-2603001/023');
            $table->foreignId('task_master_id')->constrained()->cascadeOnDelete();
            $table->dateTime('date_planning_start')->nullable();
            $table->dateTime('date_planning_finish')->nullable();
            $table->unsignedInteger('duration_planning')->nullable()->comment('total durasi perencanaan (minutes)');
            $table->dateTime('date_realization_start')->nullable()->comment('tanggal activity row pertama');
            $table->dateTime('date_realization_finish')->nullable()->comment('tanggal activity row terakhir');
            $table->unsignedInteger('duration_realization')->nullable()->comment('total durasi submission berdasarkan durasi employee (minutes)');
            $table->integer('point_target')->nullable()->comment('dari activity & planning durasi');
            $table->integer('point_achievement')->nullable()->comment('dari activity & realisasi durasi');
            $table->text('description')->nullable()->comment('catatan UNTUK teknisi (ketika planning)');
            $table->text('note')->nullable()->comment('catatan DARI teknisi (ketika realisasi)');
            $table->unsignedTinyInteger('status')->default(0)->comment('0: new planning; 1: sedang di proses; 2: submited / done; 3: hold;');
            $table->unsignedTinyInteger('unfinished')->default(0)->comment('# pekerjaan tidak diselesaiakan; 0: pekerjaan selesai; 1: tanpa keterangan; 2: pindah pekerjaan; 3: sakit; 4: izin; 5: cuti;');
            $table->boolean('unscheduled')->default(false)->comment('realisasi pekerjaan tidak dijadwalkan sebelumnya.');
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
        Schema::dropIfExists('task_details');
    }
};
