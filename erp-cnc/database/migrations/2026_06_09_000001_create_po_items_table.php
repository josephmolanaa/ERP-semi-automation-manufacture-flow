<?php
// database/migrations/2026_06_09_000001_create_po_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('pos')->cascadeOnDelete();
            $table->foreignId('quotation_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('part_name');
            $table->string('material')->nullable();
            $table->decimal('qty', 10, 2);
            $table->string('satuan', 20)->default('pcs');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->index('po_id');
            $table->index('quotation_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_items');
    }
};
