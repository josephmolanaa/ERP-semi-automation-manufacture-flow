<?php
// database/migrations/2026_06_09_000002_create_job_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('po_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('part_name');
            $table->string('material')->nullable();
            $table->decimal('qty', 10, 2);
            $table->string('satuan', 20)->default('pcs');
            $table->enum('status', ['pending', 'proses', 'selesai'])->default('pending');
            $table->text('keterangan')->nullable();
            $table->text('catatan_produksi')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->index('job_order_id');
            $table->index('po_item_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_items');
    }
};
