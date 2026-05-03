<?php
// database/seeders/RolePermissionSeeder.php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'sales', 'produksi', 'finance', 'gudang'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $permissions = [
            // Quotation
            'view_quotation', 'create_quotation', 'edit_quotation',
            'delete_quotation', 'send_quotation', 'convert_quotation',
            // PO
            'view_po', 'create_po', 'edit_po',
            // Job Order
            'view_job_order', 'update_job_progress',
            // Surat Jalan
            'view_surat_jalan', 'create_surat_jalan',
            // Invoice
            'view_invoice', 'create_invoice', 'mark_paid_invoice',
            // Dashboard
            'view_dashboard',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Admin = semua
        Role::findByName('admin')->givePermissionTo(Permission::all());

        // Sales
        Role::findByName('sales')->givePermissionTo([
            'view_quotation', 'create_quotation', 'edit_quotation',
            'send_quotation', 'view_po', 'view_dashboard',
        ]);

        // Produksi
        Role::findByName('produksi')->givePermissionTo([
            'view_job_order', 'update_job_progress', 'view_dashboard',
        ]);

        // Finance
        Role::findByName('finance')->givePermissionTo([
            'view_invoice', 'create_invoice', 'mark_paid_invoice',
            'view_po', 'view_dashboard',
        ]);

        // Gudang
        Role::findByName('gudang')->givePermissionTo([
            'view_surat_jalan', 'create_surat_jalan', 'view_job_order',
        ]);
    }
}