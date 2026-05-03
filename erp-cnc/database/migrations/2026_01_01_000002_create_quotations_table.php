<?php
// database/migrations/2026_01_01_000002_create_quotations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30)->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->date('tanggal');
            $table->date('berlaku_sampai');
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'converted'])
                  ->default('draft');
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->string('approval_token', 64)->nullable()->unique();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'tanggal']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
