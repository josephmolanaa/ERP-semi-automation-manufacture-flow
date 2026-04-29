<?php
// database/migrations/2026_01_01_000004_create_pos_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_po', 30)->unique();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->date('tanggal_po');
            $table->date('estimasi_selesai')->nullable();
            $table->enum('status', ['pending', 'proses', 'selesai', 'cancelled'])->default('pending');
            $table->decimal('total', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'tanggal_po']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos');
    }
};