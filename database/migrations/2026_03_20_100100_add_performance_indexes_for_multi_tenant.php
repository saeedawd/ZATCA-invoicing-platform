<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(
                ['tenant_id', 'direction', 'status', 'id'],
                'invoices_tenant_dir_status_id_idx'
            );
            $table->index(
                ['tenant_id', 'direction', 'status', 'issue_date'],
                'invoices_tenant_dir_status_date_idx'
            );
        });

        Schema::table('zatca_submissions', function (Blueprint $table) {
            $table->index(
                ['tenant_id', 'id'],
                'zatca_submissions_tenant_id_id_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_tenant_dir_status_id_idx');
            $table->dropIndex('invoices_tenant_dir_status_date_idx');
        });

        Schema::table('zatca_submissions', function (Blueprint $table) {
            $table->dropIndex('zatca_submissions_tenant_id_id_idx');
        });
    }
};
