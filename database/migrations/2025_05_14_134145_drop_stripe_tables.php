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
        // Drop Stripe-related tables
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');

        // Remove Stripe columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_id',
                'pm_type',
                'pm_last_four'
            ]);
        });
    }

};
