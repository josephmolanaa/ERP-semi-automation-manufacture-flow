<?php
// database/migrations/2026_01_01_000003_create_quotation_items_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->string('part_name');
            $table->string('material')->nullable();
            $table->decimal('qty', 10, 2);
            $table->string('satuan', 20)->default('pcs');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->index('quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};