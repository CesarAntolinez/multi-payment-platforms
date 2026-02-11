<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_customer_id');
            $table->string('gateway');
            $table->string('gateway_card_id')->unique();
            $table->string('brand'); // visa, mastercard, etc
            $table->string('last4');
            $table->integer('exp_month');
            $table->integer('exp_year');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('payment_customer_id')->references('id')->on('payment_customers')->onDelete('cascade');
            $table->index(['payment_customer_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_cards');
    }
}
