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
        Schema::table('users',function(Blueprint $table){
            $table->bigInteger('polar_subscription_id')->unsigned()->nullable()->after('remember_token');
        });
        // Create polar_subscriptions table
        Schema::create('polar_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('polar_id')->unique();
            $table->enum('status',['active','inactive']);
            $table->string('plan_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'name']);
        });

        // Create polar_subscription_items table
        Schema::create('polar_subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('polar_subscriptions')->onDelete('cascade');
            $table->string('polar_id')->unique();
            $table->string('product_id')->nullable();
            $table->string('plan_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polar_migrations');
    }
};
