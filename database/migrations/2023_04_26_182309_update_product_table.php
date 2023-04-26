<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // // we need to save all of the product data in order to drop the old table
        Schema::create('new_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('price');

            $table->boolean('is_limited_stock')->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('purchase_limit')->default(0);
            $table->boolean('is_visible')->default(1);

            $table->unsignedInteger('product_id');
            $table->string('product_type');

            $table->integer('sort')->unsigned()->default(0);
        });

        // get all of the products
        $products = DB::table('shop_products')->get();

        // loop through each product and save it to the new table
        foreach ($products as $product)
        {
            DB::table('new_products')->insert([
                'price' => $product->price,
                'quantity' => $product->quantity,
                'is_limited_stock' => $product->is_limited,
                'purchase_limit' => $product->max,
                'is_visible' => $product->is_visible,
                'product_id' => $product->item_id,
                'product_type' => 'Item',
                'sort' => $product->sort,
            ]);
        }

        // drop the old table
        Schema::dropIfExists('shop_products');
        Schema::dropIfExists('product_info');

        // rename the new table
        Schema::rename('new_products', 'products');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        // this migration cannot be reversed
        //
    }
}
