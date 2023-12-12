<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateInvoiceTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::create('product_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('payment_method');
            $table->string('status');
            $table->decimal('total', 13, 2)->default(0.00);
            $table->string('currency');
            $table->json('data')->nullable()->default(null);
            $table->boolean('products_distributed')->default(false);
            $table->timestamps();
        });

        if (Schema::hasTable('invoices')) {
            $invoices = DB::table('invoices')->get();

            // save the old invoices to csv
            $fp = fopen('invoices.csv', 'w');
            // add the header
            fputcsv($fp, ['id', 'title', 'price', 'payment_status', 'recurring_id', 'created_at', 'updated_at', 'user_id']);
            foreach ($invoices as $invoice) {
                fputcsv($fp, (array) $invoice);
            }
            fclose($fp);

            // drop the old table
            Schema::dropIfExists('invoices');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        // this is a destructive migration and cannot be reversed
    }
}
