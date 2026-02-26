<div class="card">
    <div class="card-header">
        <h5>Invoice #{{ $invoice->id }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-4 col-md-6 col-4">
                <h5>Payment Method</h5>
            </div>
            <div class="col-lg-8 col-md-6 col-8">
                <i class="{!! $invoice->paymentMethodIcon !!}"></i>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-4">
                <h5>Total</h5>
            </div>
            <div class="col-lg-8 col-md-6 col-8">
                {{ $invoice->currency . ' ' . $invoice->total }}
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-4">
                <h5>Products</h5>
            </div>
            <div class="col-lg-8 col-md-6 col-8">
                {!! $invoice->displayProducts !!}
            </div>
        </div>
        @if (isset($invoice->data['discount']) && $invoice->data['discount'] != [])
            <div class="row">
                <div class="col-lg-4 col-md-6 col-4">
                    <h5>Discount</h5>
                </div>
                <div class="col-lg-8 col-md-6 col-8">
                    {{ $invoice->currency . ' ' . $invoice->data['discount']['discount'] }}
                    ({!! $invoice->displayDiscountedProducts !!})
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-lg-4 col-md-6 col-4">
                <h5>Date</h5>
            </div>
            <div class="col-lg-8 col-md-6 col-8">
                {!! format_date($invoice->created_at) !!}
            </div>
        </div>
        @if (!isset($confirm) && (Auth::check() && Auth::user()->id == $invoice->user_id))
            @if ($url != 'COMPLETED')
                {{-- check if url contains 'checkoutnow' --}}
                @if (strpos($url, 'checkoutnow') != false)
                    <p class="mb-0 alert alert-info">
                        If you wish to cancel your order, please do so through the Payment Method.
                    </p>
    </div>
    <div class="card-footer">
        <a href="{{ $url }}" class="float-right btn btn-info">
            <i class="fas fa-file-invoice fa-fw"></i> Continue to Payment Method
        </a>
    </div>
@else
</div>
<div class="card-footer">
    <a href="{{ url('shops/products/paypal/confirm?token=' . $invoice->data['order_id']) }}" class="float-right btn btn-success">
        <i class="fas fa-file-invoice fa-fw"></i> Continue to Confirm Purchase
    </a>
</div>
@endif
@else
</div>
<div class="card-footer">
    <div class="alert alert-success mb-0">
        <h4 class="alert-heading">
            <i class="fas fa-check fa-fw"></i> Thank you for your purchase!
        </h4>
        <p class="mb-0">
            Your purchase has been completed successfully and your items have been added to your inventory.
            <br>Please refresh the page to get rid of this message.
            <br><br><b><a href="{{ url('shops/history') }}">You can view your purchase history here</a>.</b>
        </p>
    </div>
</div>
@endif
@endif
</div>
</div>
