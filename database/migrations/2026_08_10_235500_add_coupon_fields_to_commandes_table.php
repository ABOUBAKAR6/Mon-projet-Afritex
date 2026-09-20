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
            if (! Schema::hasColumn('commandes', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('adresse_livraison');
            }

            if (! Schema::hasColumn('commandes', 'discount_amount')) {
                $table->bigInteger('discount_amount')->default(0)->after('coupon_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            if (Schema::hasColumn('commandes', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }

            if (Schema::hasColumn('commandes', 'coupon_code')) {
                $table->dropColumn('coupon_code');
            }
        });
    }
};
