<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('email');
            $table->boolean('show_logo')->default(true)->after('logo_path');
            $table->string('invoice_template', 32)->default('classic')->after('show_logo');
            $table->string('brand_primary', 7)->default('#0b5c41')->after('invoice_template');
            $table->string('brand_secondary', 7)->default('#c9a24b')->after('brand_primary');
            $table->text('invoice_footer')->nullable()->after('brand_secondary');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'show_logo',
                'invoice_template',
                'brand_primary',
                'brand_secondary',
                'invoice_footer',
            ]);
        });
    }
};
