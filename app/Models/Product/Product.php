<?php

namespace App\Models\Product;

use App\Models\Model;

class Product extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price', 'is_limited_stock', 'total_stock', 'remaining_stock', 'purchase_limit', 'product_type', 'product_id', 'is_visible', 'sort', 'discount',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'price' => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'price' => 'required',
    ];

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include products that have available stock.
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInStock($query) {
        return $query->where('is_limited_stock', 0)->orWhere('remaining_stock', '>', 0);
    }

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the product attached to the prompt product.
     */
    public function product() {
        switch ($this->product_type) {
            case 'Item':
                return $this->belongsTo('App\Models\Item\Item', 'product_id');
                break;
            case 'Currency':
                return $this->belongsTo('App\Models\Currency\Currency', 'product_id');
                break;
            case 'LootTable':
                return $this->belongsTo('App\Models\Loot\LootTable', 'product_id');
                break;
            case 'Raffle':
                return $this->belongsTo('App\Models\Raffle\Raffle', 'product_id');
                break;
        }

        return null;
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Gets the price of the product including any discounts.
     */
    public function getTotalPriceAttribute() {
        return $this->price - $this->discount;
    }

    /**
     * Gets a colour from green to red based on the percentage of stock remaining.
     */
    public function getStockColourAttribute() {
        if ($this->is_limited_stock) {
            $percentage = $this->remaining_stock / ($this->total_stock ?? 1);
            $percentage = max(0, min(100, $percentage));

            $startColour = '#bf261d';
            $endColour = '#95cf44';

            // Convert the hex colors to RGB values
            $r1 = hexdec(substr($startColour, 1, 2));
            $g1 = hexdec(substr($startColour, 3, 2));
            $b1 = hexdec(substr($startColour, 5, 2));

            $r2 = hexdec(substr($endColour, 1, 2));
            $g2 = hexdec(substr($endColour, 3, 2));
            $b2 = hexdec(substr($endColour, 5, 2));

            // Interpolate the RGB values
            $r = round($r1 + ($r2 - $r1) * $percentage);
            $g = round($g1 + ($g2 - $g1) * $percentage);
            $b = round($b1 + ($b2 - $b1) * $percentage);

            // Convert the interpolated RGB values back to a hex color
            $interpolatedHex = sprintf('#%02x%02x%02x', $r, $g, $b);

            return $interpolatedHex;
        }
    }

    /**
     * returns if the product has available stock or not.
     */
    public function getIsOutOfStockAttribute() {
        if ($this->is_limited_stock && $this->remaining_stock <= 0) {
            return true;
        }

        return false;
    }
}
