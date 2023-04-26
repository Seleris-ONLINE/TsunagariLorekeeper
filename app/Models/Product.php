<?php

namespace App\Models;

use Config;
use App\Models\Model;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price', 'product_type', 'product_id', 'quantity', 'is_limited_stock', 'is_visible', 'purchase_limit', 'sort'
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
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the product attached to the prompt product.
     */
    public function product() 
    {
        switch ($this->product_type)
        {
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

}
