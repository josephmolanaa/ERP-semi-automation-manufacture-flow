<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->index(['deleted_at', 'tanggal'], 'quotations_deleted_tanggal_idx');
            $table->index(['deleted_at', 'status', 'tanggal'], 'quotations_deleted_status_tanggal_idx');
            $table->index(['created_by', 'deleted_at', 'tanggal'], 'quotations_created_deleted_tanggal_idx');
        });

        Schema::table('pos', function (Blueprint $table): void {
            $table->index(['deleted_at', 'tanggal_po'], 'pos_deleted_tanggal_po_idx');
            $table->index(['deleted_at', 'status', 'tanggal_po'], 'pos_deleted_status_tanggal_po_idx');
        });

        Schema::table('job_orders', function (Blueprint $table): void {
            $table->index(['deleted_at', 'status', 'estimasi_selesai'], 'job_orders_deleted_status_estimasi_idx');
            $table->index(['deleted_at', 'created_at'], 'job_orders_deleted_created_idx');
            $table->index(['po_id', 'status'], 'job_orders_po_status_idx');
        });

        Schema::table('job_progress', function (Blueprint $table): void {
            $table->index(['job_order_id', 'created_at'], 'job_progress_job_created_idx');
        });

        Schema::table('surat_jalans', function (Blueprint $table): void {
            $table->index(['deleted_at', 'tanggal_kirim'], 'surat_jalans_deleted_tanggal_idx');
            $table->index(['deleted_at', 'status', 'tanggal_kirim'], 'surat_jalans_deleted_status_tanggal_idx');
            $table->index(['job_order_id', 'deleted_at'], 'surat_jalans_job_deleted_idx');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->index(['deleted_at', 'tanggal'], 'invoices_deleted_tanggal_idx');
            $table->index(['deleted_at', 'status_bayar', 'tanggal'], 'invoices_deleted_status_tanggal_idx');
            $table->index(['deleted_at', 'status_bayar', 'jatuh_tempo'], 'invoices_deleted_status_due_idx');
            $table->index(['sj_id', 'deleted_at'], 'invoices_sj_deleted_idx');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->index(['deleted_at', 'is_active'], 'customers_deleted_active_idx');
            $table->index(['deleted_at', 'company'], 'customers_deleted_company_idx');
            $table->index(['deleted_at', 'name'], 'customers_deleted_name_idx');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->dropIndex('quotations_deleted_tanggal_idx');
            $table->dropIndex('quotations_deleted_status_tanggal_idx');
            $table->dropIndex('quotations_created_deleted_tanggal_idx');
        });

        Schema::table('pos', function (Blueprint $table): void {
            $table->dropIndex('pos_deleted_tanggal_po_idx');
            $table->dropIndex('pos_deleted_status_tanggal_po_idx');
        });

        Schema::table('job_orders', function (Blueprint $table): void {
            $table->dropIndex('job_orders_deleted_status_estimasi_idx');
            $table->dropIndex('job_orders_deleted_created_idx');
            $table->dropIndex('job_orders_po_status_idx');
        });

        Schema::table('job_progress', function (Blueprint $table): void {
            $table->dropIndex('job_progress_job_created_idx');
        });

        Schema::table('surat_jalans', function (Blueprint $table): void {
            $table->dropIndex('surat_jalans_deleted_tanggal_idx');
            $table->dropIndex('surat_jalans_deleted_status_tanggal_idx');
            $table->dropIndex('surat_jalans_job_deleted_idx');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropIndex('invoices_deleted_tanggal_idx');
            $table->dropIndex('invoices_deleted_status_tanggal_idx');
            $table->dropIndex('invoices_deleted_status_due_idx');
            $table->dropIndex('invoices_sj_deleted_idx');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropIndex('customers_deleted_active_idx');
            $table->dropIndex('customers_deleted_company_idx');
            $table->dropIndex('customers_deleted_name_idx');
        });
    }
};
