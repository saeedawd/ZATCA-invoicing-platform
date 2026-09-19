<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('trialing');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('legal_name_ar')->nullable();
            $table->string('legal_name_en')->nullable();
            $table->string('vat_number', 15)->nullable();
            $table->string('cr_number', 20)->nullable();
            $table->string('building_number', 10)->nullable();
            $table->string('street')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('additional_no', 20)->nullable();
            $table->string('plot_identification', 20)->nullable();
            $table->string('country', 2)->default('SA');
            $table->string('seller_id_type', 20)->default('CRN');
            $table->string('seller_id_value')->nullable();
            $table->boolean('is_vat_registered')->default(true);
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'vat_number']);
        });

        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('customer');
            $table->string('name');
            $table->string('vat_number', 15)->nullable();
            $table->string('cr_number', 20)->nullable();
            $table->string('building_number', 10)->nullable();
            $table->string('street')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('additional_no', 20)->nullable();
            $table->string('country', 2)->default('SA');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'name']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('unit_code', 10)->default('PCE');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('tax_category', 5)->default('S');
            $table->decimal('tax_rate', 5, 2)->default(15);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'sku']);
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('key', 50);
            $table->string('prefix', 20)->nullable();
            $table->unsignedBigInteger('next_number')->default(1);
            $table->unsignedTinyInteger('padding')->default(6);
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
        });

        Schema::create('zatca_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('environment', 20)->default('sandbox');
            $table->string('device_serial')->nullable();
            $table->string('solution_name')->nullable();
            $table->string('version')->nullable();
            $table->timestamp('otp_used_at')->nullable();
            $table->longText('csr')->nullable();
            $table->longText('private_key_encrypted')->nullable();
            $table->longText('public_cert')->nullable();
            $table->longText('csid_compliance')->nullable();
            $table->longText('csid_production')->nullable();
            $table->longText('secret_encrypted')->nullable();
            $table->longText('binary_security_token')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('last_invoice_hash')->nullable();
            $table->unsignedBigInteger('invoice_counter')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'environment', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('direction', 20)->default('sales');
            $table->string('invoice_type', 20)->default('simplified');
            $table->string('document_type_code', 10)->default('388');
            $table->string('invoice_number')->nullable();
            $table->uuid('uuid_zatca')->nullable();
            $table->date('issue_date')->nullable();
            $table->time('issue_time')->nullable();
            $table->date('supply_date')->nullable();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->string('currency', 3)->default('SAR');
            $table->string('payment_means_code', 10)->default('10');
            $table->text('note')->nullable();
            $table->decimal('line_extension_amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('taxable_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('prepaid_amount', 14, 2)->default(0);
            $table->decimal('payable_amount', 14, 2)->default(0);
            $table->text('previous_hash')->nullable();
            $table->text('invoice_hash')->nullable();
            $table->longText('qr_tlv_base64')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('status', 30)->default('draft');
            $table->string('zatca_status', 30)->default('draft');
            $table->unsignedBigInteger('counter_value')->nullable();
            $table->foreignId('zatca_device_id')->nullable()->constrained('zatca_devices')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'invoice_number']);
            $table->index(['tenant_id', 'direction', 'issue_date']);
            $table->index(['tenant_id', 'zatca_status']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('line_no');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 14, 4)->default(1);
            $table->string('unit_code', 10)->default('PCE');
            $table->decimal('unit_price', 14, 4)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->string('tax_category', 5)->default('S');
            $table->decimal('tax_rate', 5, 2)->default(15);
            $table->decimal('line_net', 14, 2)->default(0);
            $table->decimal('line_tax', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'invoice_id']);
        });

        Schema::create('invoice_tax_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('tax_category', 5);
            $table->decimal('tax_rate', 5, 2)->default(15);
            $table->decimal('taxable_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'invoice_id']);
        });

        Schema::create('invoice_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('referenced_invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'invoice_id']);
        });

        Schema::create('zatca_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zatca_device_id')->nullable()->constrained('zatca_devices')->nullOnDelete();
            $table->string('type', 20);
            $table->uuid('request_uuid')->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('request_xml_path')->nullable();
            $table->json('response_json')->nullable();
            $table->string('clearance_status')->nullable();
            $table->string('reporting_status')->nullable();
            $table->json('errors_json')->nullable();
            $table->json('warnings_json')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'invoice_id']);
            $table->index(['tenant_id', 'status']);
            $table->unique(['invoice_id', 'idempotency_key']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('zatca_submissions');
        Schema::dropIfExists('invoice_references');
        Schema::dropIfExists('invoice_tax_totals');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('zatca_devices');
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('products');
        Schema::dropIfExists('parties');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('subscriptions');
    }
};
