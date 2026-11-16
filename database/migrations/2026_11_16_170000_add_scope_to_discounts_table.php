<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->string('applies_to')->default('all')->after('percentage');
            $table->json('product_ids')->nullable()->after('applies_to');
            $table->json('brand_ids')->nullable()->after('product_ids');
            $table->json('category_ids')->nullable()->after('brand_ids');
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['applies_to', 'product_ids', 'brand_ids', 'category_ids']);
        });
    }
};