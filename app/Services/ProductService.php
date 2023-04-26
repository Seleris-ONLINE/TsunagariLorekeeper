<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Product;

class ProductService extends Service
{
    /**
     * Creates a new product.
     *
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Product
     */
    public function createProduct($data, $user)
    {
        DB::beginTransaction();

        try {

            if(Product::where('product_id', $data['product_id'])->where('product_type', $data['product_type'])->exists()) throw new \Exception("This product is already in stock.");

            if(!isset($data['is_visible'])) $data['is_visible'] = 0;
            if(!isset($data['is_limited_stock'])) $data['is_limited_stock'] = 0;
            if(!isset($data['purchase_limit'])) $data['purchase_limit'] = 0;
            if(!isset($data['quantity'])) $data['quantity'] = 0;
            if($data['quantity']) $data['current_quantity'] = $data['quantity'];

            $product = Product::create($data);

            return $this->commitReturn($product);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a product.
     *
     * @param  \App\Models\Product  $product
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Product
     */
    public function updateProduct($product, $data, $user)
    {
        DB::beginTransaction();

        try {

            if(!isset($data['is_visible'])) $data['is_visible'] = 0;
            if(!isset($data['is_limited_stock'])) $data['is_limited_stock'] = 0;
            if(!isset($data['purchase_limit'])) $data['purchase_limit'] = null;
            if(!isset($data['quantity'])) $data['quantity'] = null;
            if($data['quantity']) $data['current_quantity'] = $data['quantity'];
            // More specific validation
            if(Product::where('product_id', $data['product_id'])->where('product_type', $data['product_type'])->where('id', '!=', $product->id)->exists()) throw new \Exception("This item is already in stock.");

            $product->update($data);

            return $this->commitReturn($product);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Deletes a product.
     *
     * @param  \App\Models\Product
     * @return bool
     */
    public function deleteProduct($product)
    {
        DB::beginTransaction();

        try {
            // make sure no one is in progress buying sort of deal

            if($product->is_visible) throw new \Exception("This product is currently purchaseable. Please hide it first.");
        
            $product->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts products
     */
    public function sortProduct($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Product::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}