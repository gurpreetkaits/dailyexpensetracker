<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['expense', 'income']);
            $table->decimal('amount', 10, 2); // Allows up to 99,999,999.99
            $table->string('note')->nullable();
            $table->string('category')->nullable(); // Will be auto-detected from note
            $table->date('transaction_date')->default(now());
            $table->timestamps();
            
            // Optional fields you might want:
            // $table->string('payment_method')->nullable(); // cash, card, upi, etc.
            // $table->foreignId('wallet_id')->nullable()->constrained();
            // $table->string('attachment')->nullable(); // for receipts
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};