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
        Schema::table('recurring_expenses', function (Blueprint $table) {
            $table->dateTime('first_payment_date')->nullable();
            $table->string('manual_amount')->nullable();
        });
    }
};
