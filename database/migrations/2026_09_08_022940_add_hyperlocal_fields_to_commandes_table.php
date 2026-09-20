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
        Schema::table('commandes', function (Blueprint $table) {
            $table->string('payment_method')->default('card')->after('total');
            $table->foreignId('delivery_point_id')->nullable()->after('payment_method')->constrained('delivery_points');
            $table->string('mobile_money_number')->nullable()->after('delivery_point_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropForeign(['delivery_point_id']);
            $table->dropColumn(['payment_method', 'delivery_point_id', 'mobile_money_number']);
        });
    }
};
