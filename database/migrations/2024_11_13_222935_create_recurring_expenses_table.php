<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('amount', 10, 2);
            $table->integer('payment_day');
            $table->date('first_payment_date');
            $table->date('end_date')->nullable(); // For EMIs or fixed-term subscriptions
            $table->enum('recurrence', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->enum('type', ['subscription', 'emi', 'bill', 'other'])->default('subscription');
            // EMI specific fields
            $table->decimal('principal_amount', 10, 2)->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable(); // Annual interest rate
            $table->integer('tenure_months')->nullable();
            $table->decimal('total_interest', 10, 2)->nullable();
            $table->integer('payments_made')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recurring_expenses');
    }
};