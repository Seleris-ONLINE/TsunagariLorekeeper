<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Product\Invoice;
use App\Models\Product\Product;
use App\Services\ProductService;
use Auth;
use Illuminate\Http\Request;

class ProductController extends Controller {
    /**
     * Shows the product index page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        return view('admin.products.index', [
            'products' => Product::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the invoices page.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInvoices() {
        return view('admin.products.invoices', [
            'invoices' => Invoice::orderBy('created_at', 'DESC')->get(),
        ]);
    }

    /**
     * Gets the create product page.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCreateProduct() {
        return view('admin.products.create_edit_product', [
            'product' => new Product,
        ]);
    }

    /**
     * Gets the edit product page.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getEditProduct($id) {
        $product = Product::find($id);
        if (!$product) {
            abort(404);
        }

        return view('admin.products.create_edit_product', [
            'product' => $product,
        ]);
    }

    /**
     * Creates or edits a product.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function postCreateEditProduct(Request $request, ProductService $service, $id = null) {
        $id ? $request->validate(Product::$updateRules) : $request->validate(Product::$createRules);
        $data = $request->only([
            'price', 'is_visible', 'is_limited_stock', 'total_stock', 'remaining_stock', 'purchase_limit', 'product_type', 'product_id', 'discount',
        ]);
        if ($id && $service->updateProduct(Product::find($id), $data, Auth::user())) {
            flash('Product updated successfully.')->success();
        } elseif (!$id && $product = $service->createProduct($data, Auth::user())) {
            flash('Product created successfully.')->success();

            return redirect()->to('admin/data/products/edit/'.$product->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the delete product modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function getDeleteProduct($id) {
        $product = Product::find($id);

        return view('admin.products._delete_product', [
            'product' => $product,
        ]);
    }

    /**
     * Deletes a product.
     *
     * @param int $id
     */
    public function postDeleteProduct(Request $request, ProductService $service, $id) {
        if ($id && $service->deleteProduct(Product::find($id))) {
            flash('Product deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/products');
    }

    /**
     * Sorts the products.
     */
    public function postSortProduct(Request $request, ProductService $service) {
        if ($service->sortProduct($request->get('sort'))) {
            flash('Product order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
