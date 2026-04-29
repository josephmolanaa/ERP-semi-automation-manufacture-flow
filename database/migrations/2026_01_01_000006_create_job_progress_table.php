<?php
// database/migrations/2026_01_01_000006_create_job_progress_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('users')->restrictOnDelete();
            $table->enum('tahap', ['design', 'machining', 'assembly', 'qc', 'finishing']);
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->json('foto_paths')->nullable(); // array of file paths
            $table->integer('durasi_menit')->nullable(); // estimasi waktu mesin
            $table->timestamps();

            $table->index(['job_order_id', 'tahap']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_progress');
    }
};
