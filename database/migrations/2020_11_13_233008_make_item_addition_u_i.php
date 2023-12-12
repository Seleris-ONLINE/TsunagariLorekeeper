<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeItemAdditionUI extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::create('shop_products', function (Blueprint $table) {
            $table->increments('id');
            $table->double('price', 2);
            $table->string('item_id');
            $table->string('quantity')->nullable();
            $table->boolean('is_bundle')->default(0);
            $table->boolean('is_limited')->default(0);
            $table->boolean('is_visible')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::dropIfExists('shop_products');
    }
}
