<?php
// database/migrations/2026_01_01_000005_create_job_orders_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_job', 30)->unique();
            $table->foreignId('po_id')->constrained('pos')->restrictOnDelete();
            $table->enum('status', [
                'pending', 'design', 'machining',
                'assembly', 'qc', 'finished', 'delayed'
            ])->default('pending');
            $table->date('estimasi_selesai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->integer('progress_persen')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index('po_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};