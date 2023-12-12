<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        // we need to save all of the product data in order to drop the old table
        Schema::create('new_products', function (Blueprint $table) {
            $table->id();
            $table->decimal('price', 13, 2)->default(0.00);

            $table->boolean('is_limited_stock')->default(false);
            $table->integer('total_stock')->nullable()->default(null);
            $table->integer('remaining_stock')->nullable()->default(null);
            $table->integer('purchase_limit')->nullable()->default(null);
            $table->boolean('is_visible')->default(false);

            $table->unsignedInteger('product_id');
            $table->string('product_type');

            $table->integer('sort')->nullable()->default(null);
            $table->decimal('discount', 13, 2)->nullable()->default(null);
        });

        if (Schema::hasTable('shop_products')) {
            // get all of the products
            $products = DB::table('shop_products')->get();

            // loop through each product and save it to the new table
            foreach ($products as $product) {
                DB::table('new_products')->insert([
                    'price'            => $product->price,
                    'is_limited_stock' => $product->is_limited,
                    'total_stock'      => $product->total_stock,
                    'remaining_stock'  => $product->remaining_stock,
                    'purchase_limit'   => $product->max,
                    'is_visible'       => $product->is_visible,
                    'product_id'       => $product->item_id,
                    'product_type'     => 'Item',
                    'sort'             => $product->sort,
                    'discount'         => $product->discount,
                ]);
            }
        }

        // drop the old table
        Schema::dropIfExists('shop_products');
        Schema::dropIfExists('product_info');

        // rename the new table
        Schema::rename('new_products', 'products');

        // make item log data tables longer
        Schema::table('items_log', function (Blueprint $table) {
            $table->string('log', 2048)->change();
            $table->string('data', 2048)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        // this migration cannot be reversed
        //
    }
}
