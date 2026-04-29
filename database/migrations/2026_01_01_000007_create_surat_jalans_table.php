<?php
// database/migrations/2026_01_01_000007_create_surat_jalans_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_jalans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sj', 30)->unique();
            $table->foreignId('job_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->date('tanggal_kirim');
            $table->string('ekspedisi')->nullable();
            $table->string('no_resi')->nullable();
            $table->string('penerima')->nullable();
            $table->text('alamat_kirim')->nullable();
            $table->enum('status', ['disiapkan', 'dikirim', 'diterima'])->default('disiapkan');
            $table->timestamp('diterima_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'tanggal_kirim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_jalans');
    }
};