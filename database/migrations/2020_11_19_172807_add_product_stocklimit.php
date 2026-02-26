<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductStocklimit extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        schema::table('shop_products', function (Blueprint $table) {
            $table->integer('max')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn('max');
        });
    }
}
