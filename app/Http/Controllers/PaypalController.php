<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Config;
use Notifications;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\ExpressCheckout;
use App\Models\Invoice;
use App\Models\User\User;
use App\Models\Product;
use App\Models\Item\Item;
use App\Services\InventoryManager;

use App\Http\Controllers\Controller;

class PaypalController extends Controller
{
    
    protected $provider;

    public function __construct() {
        $this->provider = new ExpressCheckout();
    }

    public function expressCheckout(Request $request, $id) {
        // check if payment is recurring, have to do this to provide a parameter for paypal
        $recurring = $request->input('recurring', false) ? true : false;
        $amount = $request->input('amount');

        $product = Product::find($id);
        if(!$product) {
            return redirect('/cash-shop')->with(['code' => 'danger', 'message' => 'Could not find product.']);
        }
        if($product->is_limited) {
            if($amount > $product->quantity) return redirect('/cash-shop')->with(['code' => 'danger', 'message' => 'Cannot purchase more than remaining stock.']);
            if($product->quantity < 1) { 
                return redirect('/cash-shop')->with(['code' => 'danger', 'message' => 'All these items have been bought.']);
            }
        }
        // stuff cause paypal is stupid
        // we have to put this into the session because of a few reasons.
        // 1 - We need these parameters when the paypal session redirects back to our site to confirm it has been paid
        // 2 - We need it to credit the item and debit any stock; this prevents artificial stock reduction by unsure / curious users 
        $request->session()->put('amount', $amount);
        $request->session()->put('total', $product->price);
        $request->session()->put('product', $product->id);

        
        // get new invoice id
        $invoice = new Invoice();
        $invoice->save();

        // Get the cart data
        $cart = $this->getCart($recurring, $product->item, $product->price, $amount, $invoice->id);
        
        // create new invoice
        // for things like description, you can make it variable, though you will need to add it to the session since cart is called on the return function
        $invoice->title = $cart['invoice_description'];
        $invoice->price = $cart['total'];
        $invoice->user_id = Auth::user()->id;
        $invoice->save();

        // stuff to make your paypal page look nice :)
        $options = [
            'BRANDNAME' => config('lorekeeper.settings.site_name', 'Lorekeeper'),
            'LOGOIMG' => '/',
            'CHANNELTYPE' => 'Merchant',
            'CATEGORY' => 'DIGITAL_GOODS',
        ];   

        $response = $this->provider->addOptions($options)->setExpressCheckout($cart, $recurring);

        if (!$response['paypal_link']) {
          return redirect('/cash-shop')->with(['code' => 'danger', 'message' => 'Something went wrong with PayPal, please try again in a few minutes.']);
        }

        return redirect($response['paypal_link']);
      }

      
      private function getCart($recurring = false, $item, $price, $amount, $invoice_id) {
        if ($recurring) {
            // I leave this setup in case someone DOES want recurring but realistically this is useless
            return [
                'items' => [
                    [
                        'name' => 'Monthly Subscription ' . config('paypal.invoice_prefix') . ' #' . $invoice_id,
                        'price' => 20,
                        'qty' => 1,
                    ],
                ],
                'return_url' => url('/paypal/express-checkout-success?recurring=1'),
                'subscription_desc' => 'Monthly Subscription ' . config('paypal.invoice_prefix') . ' #' . $invoice_id,
                'invoice_id' => config('paypal.invoice_prefix') . 'Invoice-' . $invoice_id,
                'invoice_description' => "Order #". $invoice_id ." Invoice",
                'cancel_url' => url('/cash-shop'),
                'total' => 20, // Total price of the cart
            ];
        }
        return [
            'items' => [
                [
                    // you could pass a variable in here for name but i had so much trouble getting it to work originally
                    // I have now completely (not really but very close to) thrown up my hands
                    'name' => $item->name ?? 'ARPG Item',
                    'price' => $price,
                    'qty' => $amount,
                ],
            ],
            'return_url' => url('/paypal/express-checkout-success'),
            'invoice_id' => config('paypal.invoice_prefix') . 'Invoice-' . $invoice_id,
            'invoice_description' => "Digital ARPG Item purchase. Order #".$invoice_id." Invoice",
            'cancel_url' => url('/cash-shop'),
            'total' => $price * $amount,
        ];
    }

    public function expressCheckoutSuccess(Request $request, InventoryManager $service) {

        // this function is the completion fuction, user has been charged but more validation needed
        // check if payment is recurring
        $recurring = $request->input('recurring', false) ? true : false;

        $token = $request->get('token');
        $PayerID = $request->get('PayerID');
        // getting our stuff back
        $price = session('total');
        $product = session('product');
        $amount = session('amount');
        session()->forget(['stock', 'total', 'amount']);
        $product = Product::find($product);
        $user = Auth::user();
        
        //get details
        $response = $this->provider->getExpressCheckoutDetails($token);

        if (!in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            return redirect('/cash-shop')->with(['code' => 'danger', 'message' => 'Error processing PayPal payment']);
        }

        $invoice_id = explode('Invoice-', $response['INVNUM'])[1];
        $cart = $this->getCart($recurring, $product, $price, $amount, $invoice_id);

        // check if our payment is recurring
        // if($recurring) {

        //     $response = $this->provider->createMonthlySubscription($response['TOKEN'], $response['AMT'], $cart['subscription_desc']);
            
        //     $status = 'Invalid';

        //     if (!empty($response['PROFILESTATUS']) && in_array($response['PROFILESTATUS'], ['ActiveProfile', 'PendingProfile'])) {
        //         $status = 'Processed';
        //     }

        // } else {
        // if payment is not recurring just perform transaction on PayPal
        // and get the payment status
        $payment_status = $this->provider->doExpressCheckoutPayment($cart, $token, $PayerID);
        //}

        $invoice = Invoice::find($invoice_id);
        if(!$invoice) {
            return redirect('/cash-shop')->with(['code' => 'danger', 'message' => 'Could not find invoice.']);
        }

        // set invoice status
        $invoice->payment_status = $payment_status['PAYMENTINFO_0_PAYMENTSTATUS'];
        $invoice->save();

        Notifications::create('PURCHASE', Auth::user(), [
            'item' => $product->item->name,
        ]);

        if($invoice->paid) {
            //reduce stock by one
            $product->decrement('quantity', $amount);
            $product->save(); 

            $data = [];
            $data['data'] = 'Bought from cash store by ' . $user->name . ' for $' . $price . ' each ($' . $price * $amount . ' total)' ;
            $data['notes'] = 'Bought from cash store';

            if($service->creditItem(null, $user, 'Cash Shop Purchase', $data, $product->item, $amount)) {
                flash('Items granted successfully.')->success();
            }
            else {
                foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
            }
            
            return redirect('/paypal-success')->with(['code' => 'success', 'message' => 'Your order has been paid succesfully.']);
        }
        
        return redirect('/')->with(['code' => 'danger', 'message' => 'Error processing PayPal payment for Order ' . $invoice->id . '!']);
    }

    /*
    *
    *   Notifies merchant of a payment
    */
    public function notify(Request $request)
    {

        // not used, may add in future
        
    }
}
        
    

