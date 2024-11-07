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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name');
            $table->decimal('target', 10, 2);
            $table->decimal('saved', 10, 2)->default(0);
            $table->date('target_date');
            $table->decimal('monthly_contribution', 10, 2);
            $table->timestamps();
        });
    }
};
