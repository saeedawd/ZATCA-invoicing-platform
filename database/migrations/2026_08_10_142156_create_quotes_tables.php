<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('quote_number')->nullable();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->date('issue_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('currency', 3)->default('SAR');
            $table->text('note')->nullable();
            $table->decimal('line_extension_amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('taxable_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('status', 30)->default('draft');
            $table->string('pdf_path')->nullable();
            $table->unsignedBigInteger('converted_invoice_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'quote_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'issue_date']);
        });

        Schema::create('quote_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
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

            $table->index(['tenant_id', 'quote_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('quote_id')->nullable()->after('party_id')->constrained('quotes')->nullOnDelete();
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->foreign('converted_invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['converted_invoice_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quote_id');
        });

        Schema::dropIfExists('quote_lines');
        Schema::dropIfExists('quotes');
    }
};
