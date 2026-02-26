<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'payment_method', 'status', 'total', 'currency', 'data', 'product_distributed',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_invoices';

    /**********************************************************************************************

        RELATIONSHIPS

    **********************************************************************************************/

    /**
     * Get the user that owns the invoice.
     */
    public function user() {
        return $this->belongsTo('App\Models\User\User');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute() {
        if (!$this->id) {
            return null;
        }

        return json_decode($this->attributes['data'], true);
    }

    /**
     * Get the fontawesome icon related to the payment method.
     */
    public function getPaymentMethodIconAttribute() {
        switch ($this->payment_method) {
            case 'paypal':
                return 'fab fa-paypal';
            case 'stripe':
                return 'fab fa-cc-stripe';
            case 'manual':
                return 'fas fa-money-bill-wave';
            default:
                return 'fas fa-question';
        }
    }

    /**
     * Displays all the products in the invoice.
     *
     * @return string
     */
    public function getDisplayProductsAttribute() {
        $products = [];
        foreach ($this->data['products'] as $product) {
            $product_obj = Product::find($product['product_id']);
            $products[] = $product_obj->product->displayName.' x'.$product['quantity'];
        }

        return implode(', ', $products);
    }

    /**
     * Displays all the discounted products in the invoice if any exist.
     *
     * @return string
     */
    public function getDisplayDiscountedProductsAttribute() {
        $products = [];
        foreach ($this->data['discount']['products'] as $product) {
            $product_obj = Product::find($product['product_id']);
            $products[] = $product_obj->product->displayName.' x'.$product['quantity'];
        }

        return implode(', ', $products);
    }
}
