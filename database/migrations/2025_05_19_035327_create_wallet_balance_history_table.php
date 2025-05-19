<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wallet_balance_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->decimal('balance', 12, 2);
            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallet_balance_history');
    }
}; 