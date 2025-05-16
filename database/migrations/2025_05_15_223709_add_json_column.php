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
        Schema::table('polar_subscriptions', function (Blueprint $table) {
            $table->string('polar_subscription_id')->nullable();
            $table->longText('response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polar_subscriptions', function (Blueprint $table) {
            $table->dropColumn('polar_subscription_id');
            $table->dropColumn('response');
        });
    }
};
