<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('pdf_path')->nullable();
        });

        Schema::table('pos', function (Blueprint $table) {
            $table->string('pdf_path')->nullable();
        });

        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->string('pdf_path')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('pdf_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });

        Schema::table('pos', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });

        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
};
