<?php

namespace App\Http\Controllers;

use App\Models\Product\Invoice;
use App\Models\Product\Product;
use App\Models\Shop\Shop;
use App\Services\PaymentManager;
use Auth;
use Illuminate\Http\Request;

class PaymentController extends Controller {
    /**
     * Product shop index page. Payment agnostic.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStoreFront(Request $request) {
        $products = Product::where('is_visible', 1)->orderBy('sort', 'DESC')->get();
        $pending_invoice = Invoice::where('user_id', Auth::user()->id)->whereIn('status', ['CREATED', 'PENDING', 'PAYER_ACTION_REQUIRED'])->first();

        return view('shops.products.index', [
            'shops'           => Shop::where('is_active', 1)->orderBy('sort', 'DESC')->get(),
            'products'        => $products,
            'pending_invoice' => $pending_invoice,
        ]);
    }

    /**
     * Gets the cart modal.
     *
     * @param mixed|null $ids
     *
     * @return array
     */
    public function getCart(Request $request, $ids = null) {
        if (!$ids) {
            return [];
        }
        $ids = explode(',', $ids);
        $quantity = (array) explode(',', $request->get('quantity', []));
        $products = Product::whereIn('id', $ids)->where('is_visible', 1)->get();
        $html = [];
        foreach ($ids as $key=>$id) {
            $html[] = view('shops.products._cart_item', [
                'product'   => $products->where('id', $id)->first(),
                'quantity'  => $quantity[$key] ?? 1,
            ])->render();
        }

        return $html;
    }

    /**
     * Gets the invoice modal.
     *
     * @param mixed $id
     */
    public function getInvoice(PaymentManager $service, Request $request, $id) {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->user_id != Auth::user()->id) {
            abort(404);
        }
        $url = $service->getPaypalOrderCompletionUrl($invoice);
        if ($url == 'COMPLETED') {
            $service->confirmPaypalOrder(Auth::user());
        }

        return view('shops.products._invoice', [
            'url'     => $url,
            'invoice' => $invoice,
        ]);
    }

    /**
     * Payment Gateway.
     *
     * @param mixed $ids
     * @param mixed $payment_method
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postCheckout(PaymentManager $service, $ids, $payment_method = 'paypal') {
        $ids = explode(',', $ids);
        $quantity = (array) explode(',', request()->get('quantity', []));
        // no stripe yet
        if (!$response = $service->createInvoice(['product_ids' => $ids, 'quantity' => $quantity], Auth::user(), $payment_method)) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            if ($payment_method == 'paypal') {
                if ($response['status'] == 'PAYER_ACTION_REQUIRED') {
                    return redirect($response['links'][1]['href']);
                } elseif ($response['status'] == 'COMPLETED') {
                    $service->confirmPaypalOrder(Auth::user());
                }
            }
        }

        return redirect()->to('shops/products');
    }

    /**********************************************************************************************

        PAYPAL

    **********************************************************************************************/

    /**
     * PayPal success callback, user must confirm order.
     */
    public function getPaypalConfirm(PaymentManager $service, Request $request) {
        $token = $request->get('token');

        // find the invoice by token, filter by data['order_id']
        $invoice = Invoice::where('user_id', Auth::user()->id)->get()->filter(function ($invoice) use ($token) {
            return $invoice->data['order_id'] == $token;
        })->first();

        if (!$invoice) {
            abort(404);
        }

        $url = $service->getPaypalOrderCompletionUrl($invoice);

        return view('shops.products.confirm', [
            'shops'   => Shop::where('is_active', 1)->orderBy('sort', 'DESC')->get(),
            'url'     => $url,
            'invoice' => $invoice,
        ]);
    }

    /**
     * Finalises user's paypal order after confirmation.
     *
     * @param mixed $id
     */
    public function postPaypalConfirm(PaymentManager $service, Request $request, $id) {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->user_id != Auth::user()->id) {
            abort(404);
        }

        if ($invoice->status != 'PAYER_ACTION_REQUIRED') {
            abort(404);
        }

        $service->confirmPaypalOrder(Auth::user());

        return redirect()->to('shops/products');
    }

    /**
     * PayPal success callback.
     */
    public function getPaypalSuccess(PaymentManager $service, Request $request) {
        $service->confirmPaypalOrder(Auth::user());

        return redirect()->to('shops/products');
    }

    /**
     * Cancels a paypal order from the paypal gateway.
     */
    public function getPaypalCancel(PaymentManager $service, Request $request) {
        $service->cancelPaypalOrder(Auth::user());

        return redirect()->to('shops/products');
    }

    /**
     * Cancels a paypal order at the confirmation stage.
     *
     * @param mixed $id
     */
    public function postPaypalCancel(PaymentManager $service, Request $request, $id) {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->user_id != Auth::user()->id) {
            abort(404);
        }
        $service->cancelPaypalOrder(Auth::user(), $invoice);

        return redirect()->to('shops/products');
    }
}
