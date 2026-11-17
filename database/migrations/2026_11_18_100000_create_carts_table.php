<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->unique();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size')->default('');
            $table->string('color')->default('');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price');
            $table->timestamps();

            // A product+variant can only exist once per cart. Empty-string
            // defaults (not NULL) keep this constraint effective on Postgres.
            $table->unique(['cart_id', 'product_id', 'size', 'color']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
