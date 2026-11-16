<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('id');
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete()->after('user_id');
            $table->string('shipping_first_name')->nullable()->after('customer_name');
            $table->string('shipping_last_name')->nullable()->after('shipping_first_name');
            $table->string('shipping_phone', 20)->nullable()->after('shipping_last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['address_id']);
            $table->dropColumn(['user_id', 'address_id', 'shipping_first_name', 'shipping_last_name', 'shipping_phone']);
        });
    }
};