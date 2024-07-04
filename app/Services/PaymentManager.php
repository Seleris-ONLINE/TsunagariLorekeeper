<?php

namespace App\Services;

use App\Models\Product\Invoice;
use App\Models\Product\Product;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentManager extends Service {
    /**
     * Distributes products to the user.
     *
     * @param App\Models\Product\Invoice $invoice
     */
    public function distributeProducts($invoice) {
        DB::beginTransaction();

        try {
            if ($invoice->products_distributed) {
                throw new \Exception('Products have already been distributed. If you have not received your products, please contact a member of staff.');
            }

            $product_info = [];
            $total_assets = createAssetsArray();
            foreach ($invoice->data['products'] as $p_info) {
                $product = Product::find($p_info['product_id']);

                if (!$product) {
                    flash('One or more of the products you purchased are no longer available. Please contact a member of staff.')->error();

                    return $this->rollbackReturn(false);
                }

                $assets = createAssetsArray();
                addAsset($assets, $product->product, $p_info['quantity']);
                addAsset($total_assets, $product->product, $p_info['quantity']);

                // update product quantity
                if ($product->is_limited_stock) {
                    $product->remaining_stock -= $p_info['quantity'];
                    $product->save();
                }

                if (!$rewards = fillUserAssets($assets, null, $invoice->user, 'Product Purchase', [
                    'data'  => 'Purchased by '.$invoice->user->name.' for $'.$product->price.' each ($'.$product->price * $p_info['quantity'].' total)',
                    'notes' => 'Purchased by '.$invoice->user->name,
                ])) {
                    flash('The product '.$product->product->name.' could not be distributed. Please contact a member of staff.')->error();

                    return $this->rollbackReturn(false);
                }
            }

            flash('Received: '.createRewardsString($total_assets))->success();

            $invoice->update([
                'products_distributed' => true,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates an invoice.
     *
     * @param array                $data
     * @param App\Models\User\User $user
     * @param mixed                $payment_method
     *
     * @return App\Models\Product\Invoice
     */
    public function createInvoice($data, $user, $payment_method = 'paypal') {
        DB::beginTransaction();

        try {
            // check if there is an existing, pending invoice
            $invoice = Invoice::where('user_id', $user->id)->where('payment_method', $payment_method)->whereIn('status', ['PENDING', 'PAYER_ACTION_REQUIRED'])->orderBy('id', 'desc')->first();
            if ($invoice) {
                throw new \Exception('You already have a pending invoice. Please complete or cancel it on the payer website before creating a new one.');
            }
            if (!isset($data['product_ids'])) {
                throw new \Exception('You must select at least one product.');
            }
            if (count($data['product_ids']) < 1) {
                throw new \Exception('You must select at least one product.');
            }
            if (count($data['product_ids']) > 5) {
                throw new \Exception('You cannot buy more than 5 products at once.');
            }

            // check if isOutOfStock attribute is false
            $products = Product::inStock()->whereIn('id', $data['product_ids'])->where('is_visible', 1)->get();

            if (count($products) < count($data['product_ids'])) {
                throw new \Exception('One or more of the selected products are not available.');
            }

            // invoice_total_price includes discounts
            $invoice_total_price = 0;
            $product_data = [
                'products'    => [],
                'total_price' => 0, // total price without discounts
            ];
            foreach ($data['product_ids'] as $key=>$product_id) {
                $product = $products->where('id', $product_id)->first();

                if (!$product) {
                    throw new \Exception('This product does not exist.');
                }
                if (!$product->is_visible) {
                    throw new \Exception('This product is not available.');
                }
                if ($product->is_limited_stock) {
                    if ($data['quantity'][$key] > $product->remaining_stock) {
                        throw new \Exception('You cannot buy more than the available quantity.');
                    }
                    if ($product->remaining_stock < 1) {
                        throw new \Exception('This product is out of stock.');
                    }
                    // check if there is a purchase limit
                    if ($product->purchase_limit) {
                        if ($data['quantity'][$key] > $product->purchase_limit) {
                            throw new \Exception('You cannot buy more than the purchase limit.');
                        }
                        $previous_purchases = Invoice::where('user_id', $user->id)->where('payment_method', $payment_method)->where('status', 'COMPLETED')->whereJsonContains('data->products', [['product_id' => $product->id]])->get();
                        $total_purchased = 0;
                        foreach ($previous_purchases as $purchase) {
                            foreach ($purchase->data['products'] as $p_info) {
                                if ($p_info['product_id'] == $product->id) {
                                    $total_purchased += $p_info['quantity'];
                                }
                            }
                        }
                        if ($total_purchased + $data['quantity'][$key] > $product->purchase_limit) {
                            throw new \Exception('You cannot buy more than the purchase limit.');
                        }
                    }
                }
                $invoice_total_price += $product->totalPrice * $data['quantity'][$key];

                // Add to product data
                $product_data['products'][] = [
                    'product_id'    => $product->id,
                    'quantity'      => $data['quantity'][$key],
                    'is_discounted' => $product->discount ? true : false,
                ];
                $product_data['total_price'] += $product->price * $data['quantity'][$key];
            }

            $invoice = Invoice::create([
                'user_id'        => $user->id,
                'payment_method' => $payment_method,
                'status'         => 'PENDING',
                'total'          => $invoice_total_price,
                'currency'       => Config::get('paypal.currency'),
            ]);

            if ($payment_method == 'paypal') {
                $response = $this->processPaypalTransaction($product_data, $invoice, $user);

                if (isset($response['debug_id']) || isset($response['error'])) {
                    Log::error('Paypal error: '.json_encode($response));
                    throw new \Exception('An error occurred creating an order. Please try again later.');
                }

                $invoice->update([
                    'status'          => $response['status'],
                    'data'            => [
                        'products'  => $product_data['products'],
                        'order_id'  => $response['id'],
                        'discount'  => $product_data['total_price'] - $invoice_total_price > 0 ? [
                            'total_price' => $invoice_total_price,
                            'discount'    => $product_data['total_price'] - $invoice_total_price,
                            'products'    => array_filter($product_data['products'], function ($product) {
                                return $product['is_discounted'];
                            }),
                        ] : [],
                    ],
                ]);

                return $this->commitReturn($response);
            } else {
                throw new \Exception('Requested payment method is not supported.');
            }

            return $this->commitReturn($response);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        PAYPAL

    **********************************************************************************************/

    /**
     * Processes a paypal transaction.
     *
     * @param mixed $data
     * @param mixed $invoice
     * @param mixed $user
     *
     * @return mixed $response
     */
    public function processPaypalTransaction($data, $invoice, $user) {
        DB::beginTransaction();

        try {
            $provider = new PayPalClient;
            $provider->getAccessToken();

            $item_data = [];
            $discount = 0;
            $total_price = 0;
            foreach ($data['products'] as $product_info) {
                $product = Product::findOrFail($product_info['product_id']);

                if (!$product) {
                    throw new \Exception('This product does not exist.');
                }

                // add discount
                $discount += $product->discount ? ($product->discount * $product_info['quantity']) : 0;
                // add total price, this is different from invoice total since it does not include discounts
                $total_price += $product->price * $product_info['quantity'];

                $item_data[] = [
                    'name'        => $product->product->name,
                    'description' => Config::get('app.name') . ' DIGITAL ITEM:' .Str::limit($product->product->name, 20, '...'),
                    'sku'         => $product->product->id,
                    'unit_amount' => [
                        'currency_code' => Config::get('paypal.currency'),
                        'value'         => $product->price,
                    ],
                    'quantity'    => $product_info['quantity'],
                    'category'    => 'DIGITAL_GOODS',
                ];
            }

            $order_data = [
                'intent'          => 'CAPTURE',
                'description'     => 'Digital products purchase on '.Config::get('app.name'),
                'soft_descriptor' => Config::get('app.name'),
                'invoice_id'      => $invoice->id,
                'purchase_units'  => [[
                    'amount' => [
                        'currency_code' => Config::get('paypal.currency'),
                        'value'         => $invoice->total,
                        'breakdown'     => [ // TODO
                            'item_total' => [
                                'currency_code' => Config::get('paypal.currency'),
                                'value'         => $data['total_price'],
                            ],
                            'discount' => [
                                'currency_code' => Config::get('paypal.currency'),
                                'value'         => $discount,
                            ],
                        ],
                    ],
                    'items' => $item_data,
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'shipping_preference' => 'NO_SHIPPING',
                            'landing_pahge'       => 'NO_PREFERENCE',
                            'brand_name'          => Config::get('app.name'),
                            'return_url'          => url('shops/products/paypal/confirm'),
                            'cancel_url'          => url('shops/products/paypal/cancel'),
                        ],
                    ],
                ],
            ];

            $response = $provider->createOrder($order_data);

            return $this->commitReturn($response);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Returns an order's completion url.
     *
     * @param mixed $invoice
     *
     * @return string $url
     */
    public function getPaypalOrderCompletionUrl($invoice) {
        DB::beginTransaction();

        try {
            $provider = new PayPalClient;
            $provider->getAccessToken();

            $response = $provider->showOrderDetails($invoice->data['order_id']);

            if ($response['status'] == 'COMPLETED') {
                return $this->commitReturn($response['status']);
            }

            return $this->commitReturn($response['links'][1]['href']);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Cancels a paypal order.
     *
     * @param App\Models\User\User $user
     * @param mixed|null           $invoice
     */
    public function cancelPaypalOrder($user, $invoice = null) {
        DB::beginTransaction();

        try {
            // we dont send anything to paypal, we just cancel the invoice
            // paypal will automatically cancel the order after 3 hours
            // we will also cancel the order after 3 hours, using our cron job

            if (!$invoice) {
                $invoice = Invoice::where('user_id', $user->id)->where('payment_method', 'paypal')->whereIn('status', ['PENDING', 'PAYER_ACTION_REQUIRED'])->orderBy('id', 'desc')->first();
            }
            if (!$invoice) {
                throw new \Exception('You have no invoices.');
            }

            $invoice->update([
                'status' => 'CANCELLED',
            ]);

            flash('Payment cancelled successfully.')->info();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Gets a specific order's details (generally the users latest invoice)
     * this is used to either cancel a pending invoice or complete a pending invoice.
     *
     * @param App\Models\User\User $user
     * @param mixed|null           $invoice
     *
     * @return mixed $response
     */
    public function confirmPaypalOrder($user, $invoice = null) {
        DB::beginTransaction();

        try {
            if (!$invoice) {
                $invoice = Invoice::where('user_id', $user->id)->where('payment_method', 'paypal')->where('status', 'PAYER_ACTION_REQUIRED')->orderBy('id', 'desc')->first();
            }

            if (!$invoice) {
                throw new \Exception('You have no invoices.');
            }

            $provider = new PayPalClient;
            $provider->getAccessToken();

            $response = $provider->showOrderDetails($invoice->data['order_id']);

            // if the order is already completed, we can just return the response
            if ($response['status'] == 'COMPLETED') {
                $invoice->update([
                    'status' => $response['status'],
                ]);

                // distribute products
                $this->distributeProducts($invoice);

                flash('Payment completed successfully.')->success();

                return $this->commitReturn($response);
            }

            if (!$response['status'] == 'APPROVED') {
                throw new \Exception('Something went wrong, and the payment was not approved.');
            }

            $invoice->update([
                'status' => $response['status'],
            ]);

            $response = $provider->capturePaymentOrder($invoice->data['order_id']);

            if (!$response['status'] == 'COMPLETED') {
                throw new \Exception('Something went wrong, and the payment was not completed.');
            }

            $invoice->update([
                'status' => $response['status'],
            ]);

            // distribute products
            $this->distributeProducts($invoice);

            flash('Payment completed successfully!')->success();

            return $this->commitReturn($response);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
