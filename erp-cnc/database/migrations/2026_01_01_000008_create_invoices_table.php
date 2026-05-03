<?php
// database/migrations/2026_01_01_000008_create_invoices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_invoice', 30)->unique();
            $table->foreignId('sj_id')->constrained('surat_jalans')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->date('tanggal');
            $table->date('jatuh_tempo')->nullable();
            $table->decimal('total', 15, 2);
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->enum('status_bayar', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status_bayar', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
