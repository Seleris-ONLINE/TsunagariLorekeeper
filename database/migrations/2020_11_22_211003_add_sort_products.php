<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortProducts extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        schema::table('shop_products', function (Blueprint $table) {
            $table->integer('sort')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn('sort');
        });
    }
}
