<?php

namespace App\Http\Controllers\Admin\Data;

use DB;
use Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\ProductInfo;
use App\Models\Item\Item;

use App\Services\ProductService;

class ProductController extends Controller
{
    /**
     * Shows the product index page.
     * 
     * @return \Illuminate\Http\Response
     */
    public function Index(Request $request) 
    {
        return view('admin.cash_shop.index', [
            'products' => Product::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Gets the create product page.
     * 
     * @return \Illuminate\Http\Response
     */
    public function getCreateProduct() {

        return view('admin.cash_shop.create_edit_product', [
            'product' => new Product,
        ]);
    }

    /**
     * Gets the edit product page.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getEditProduct($id) {
        $product = Product::find($id);
        if(!$product) abort(404);
        return view('admin.cash_shop.create_edit_product', [
            'product' => $product,
        ]);
    }

    /**
     * Creates or edits a product.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\ProductService  $service
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function postCreateEditProduct(Request $request, ProductService $service, $id = null)
    {
        $id ? $request->validate(Product::$updateRules) : $request->validate(Product::$createRules);
        $data = $request->only([
            'price', 'is_visible', 'is_limited_stock', 'quantity', 'purchase_limit', 'product_type', 'product_id'
        ]);
        if($id && $service->updateProduct(Product::find($id), $data, Auth::user())) {
            flash('Product updated successfully.')->success();
        }
        else if (!$id && $product = $service->createProduct($data, Auth::user())) {
            flash('Product created successfully.')->success();
            return redirect()->to('admin/data/products/edit/'.$product->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    // delete
    public function getDeleteProduct($id)
    {
        $products = Product::find($id);
        return view('admin.cash_shop._delete_product', [
            'products' => $products,
        ]);
    }
    
    // post delete
    public function postDeleteProduct(Request $request, ProductService $service, $id)
    {
        if($id && $service->deleteProduct(Product::find($id))) {
            flash('Product deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/products');
    }

    // shop
    public function getEditShop() {

        $shop = ProductInfo::where('id', 1)->first();
        return view('admin.cash_shop.edit_desc', [
            'shop' => $shop,
        ]);
    }

    public function postEditShop(Request $request, ProductService $service) {

        $data = $request->only([
            'title', 'desc', 'bdesc', 'btitle'
        ]);
        if($service->editShop(ProductInfo::find(1), $data)) {
            flash('Shop info edited successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/products');
    }

   //sorts
    public function postSortProduct(Request $request, ProductService $service)
    {
        if($service->sortProduct($request->get('sort'))) {
            flash('Product order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
