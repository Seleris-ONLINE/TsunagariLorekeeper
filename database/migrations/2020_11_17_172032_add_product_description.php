<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductDescription extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        schema::create('product_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('desc')->nullable();
            $table->string('title')->nullable();
            $table->string('bdesc')->nullable();
            $table->string('btitle')->nullable();
        });

        DB::table('product_info')->insert(
            [
                [
                    'desc'   => 'Lorem Ipsum',
                    'title'  => 'Item Stock',
                    'bdesc'  => 'Lorem Ipsum',
                    'btitle' => 'Item Stock',
                ],
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::dropIfExists('product_info');
    }
}
