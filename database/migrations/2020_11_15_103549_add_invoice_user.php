<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceUser extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
