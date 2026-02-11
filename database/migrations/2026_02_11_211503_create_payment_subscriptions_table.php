<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_customer_id');
            $table->unsignedBigInteger('payment_plan_id');
            $table->string('gateway');
            $table->string('gateway_subscription_id')->unique();
            $table->string('status', 30)->nullable(); // active, past_due, canceled, unpaid
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('payment_customer_id')->references('id')->on('payment_customers')->onDelete('cascade');
            $table->foreign('payment_plan_id')->references('id')->on('payment_plans')->onDelete('cascade');
            $table->index(['payment_customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_subscriptions');
    }
}
