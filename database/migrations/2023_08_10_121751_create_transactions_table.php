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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('item_sku', 50)->nullable();
            $table->string('item_name')->nullable();
            $table->double('item_price', 10, 2)->nullable();
            $table->string('item_price_currency', 10)->nullable();
            $table->string('payer_id', 50)->nullable();
            $table->string('payer_name', 50)->nullable();
            $table->string('payer_email', 50)->nullable();
            $table->string('payer_country', 20)->nullable();
            $table->string('merchant_id')->nullable();
            $table->string('merchant_email', 50)->nullable();
            $table->string('order_id', 50);
            $table->string('transaction_id', 50);
            $table->double('paid_amount', 10, 2);
            $table->string('paid_amount_currency', 10);
            $table->string('payment_source', 50)->nullable();
            $table->string('payment_status', 25);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
