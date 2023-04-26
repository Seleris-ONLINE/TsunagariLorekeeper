<?php

namespace App\Http\Controllers;

use Auth;
use DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\InventoryManager;
use App\Services\ShopManager;
use App\Models\Product;

class StoreController extends Controller
{

    public function storeFront() {
    // add products
    $products = Product::where('is_visible', 1)->orderBy('sort', 'DESC')->get();
       
        return view('cash_shop.index', [
            'products' => $products,
            // 'shop' => $desc,
        ]);
    }

    public function success(){
        
        return view('cash_shop.paypal_success', [
           
        ]);
    }
}