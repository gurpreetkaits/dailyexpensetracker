<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('paddle_subscription_id')->nullable();
            $table->string('paddle_customer_id')->nullable();
            $table->string('subscription_status')->default('none');
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('last_payment_failed_at')->nullable();
            $table->string('country')->default('US');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'paddle_subscription_id',
                'paddle_customer_id',
                'subscription_status',
                'last_payment_at',
                'last_payment_failed_at',
                'country'
            ]);
        });
    }
}; 