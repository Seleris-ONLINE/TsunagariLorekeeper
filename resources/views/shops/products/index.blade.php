@extends('shops.layout')

@section('shops-title')
    {{ Config::get('lorekeeper.products.shop_title') }}
@endsection

@section('shops-content')
    {!! breadcrumbs(['Shops' => 'shops', 'Products' => 'shops/products']) !!}

    @if ($pending_invoice)
        <div class="alert alert-warning">
            <h4 class="alert-heading">
                <i class="fas fa-exclamation-triangle fa-fw"></i> Pending Invoice
            </h4>
            <p>
                You have a pending invoice. Please either complete the purchase or cancel the invoice before purchasing more items.
                <br />
                <b>Invoices expire after 24 hours, or 3 hours after authorisation.</b>
            </p>
            <hr>
            <p class="mb-0">
                <a href="#" class="btn btn-primary float-right invoice" data-id="{{ $pending_invoice->id }}">
                    <i class="fas fa-file-invoice fa-fw"></i> View Invoice
                </a>
            </p>
        </div>
    @endif

    <div class="container">
        <h3 class="text-center mb-4">
            {{ config('lorekeeper.products.shop_title') }}
            @if (Auth::check() && Auth::user()->isStaff)
                <a href="{{ url('admin/data/products') }}" class="float-right">
                    <i class="fas fa-pencil-alt fa-fw text-muted"></i>
                </a>
            @endif
        </h3>
        <div class="text-center">
            {!! config('lorekeeper.products.shop_description') !!}
        </div>
        <hr>
        @if ($products->count() > 0)
            {{-- chunk products on product_type --}}
            @foreach ($products->groupBy('product_type') as $product_type => $products)
                <div class="card mb-3 inventory-category">
                    <h5 class="card-header inventory-header">
                        {{ $product_type ?? 'Miscellaneous' }}
                    </h5>
                    <div class="card-body inventory-body">
                        @foreach ($products->chunk(4) as $chunk)
                            <div class="row mb-3">
                                @foreach ($chunk as $product)
                                    @include('shops.products._products', ['product' => $product])
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <p>No products found! Come back soon.</p>
        @endif
    </div>

    <div class="card p-2 rounded" style="position: fixed; top: 2vh; right: 2vw; width: 25vw; z-index: 1000; display: none;" id="floating-cart">
        <div class="card-body p-0">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-shopping-cart fa-fw"></i> Cart
                </h3>
                <a href="#" id="close-cart"><i class="fas fa-times fa-fw float-right" id="close-cart"></i></a>
            </div>
            <div>
                <div class="row no-gutters">
                    <div class="col"></div>
                    <div class="col-6">
                        <b>Product</b>
                    </div>
                    <div class="col-3">
                        <b>Quantity</b>
                    </div>
                    <div class="col-2">
                    </div>
                </div>
                <hr class="col-10">
                <div id="cart-items">
                </div>
            </div>
            <div class="text-right mt-2">
                {!! Form::open(['url' => 'shops/products/purchase']) !!}
                    {!! Form::button('<i class="fab fa-paypal"></i> Checkout Cart', ['class' => 'btn btn-primary btn-block cart-checkout']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        let cart = {};

        function updateCart() {
            $.ajax({
                url: '{{ url('shops/products/cart') }}/' + Object.keys(cart).join(',') + '?quantity=' + Object.values(cart).join(','),
                type: 'GET',
                success: function(response) {
                    if (response == '') {
                        $('#floating-cart').fadeOut();
                    } else {
                        $('#floating-cart').fadeIn();
                    }
                    $('#cart-items').html(response);
                    addRemoveFromCartListeners();
                }
            });
        }

        function addRemoveFromCartListeners() {
            $('.remove-from-cart').on('click', function(e) {
                e.preventDefault();
                let product_id = $(this).data('id');
                delete cart[product_id];
                updateCart();
            });
        }

        $(document).ready(function() {
            $('#floating-cart').draggable({
                handle: '.card-body'
            });

            $('.invoice').on('click', function(e) {
                e.preventDefault();
                console.log($(this).data('id'));
                loadModal("{{ url('shops/invoice') }}/" + $(this).data('id'), 'Viewing Invoice');
            });

            $('#close-cart').on('click', function() {
                cart = {};
                $('#floating-cart').fadeOut();
            });

            $('.cart-checkout').on('click', function(e) {
                e.preventDefault();
                if (Object.keys(cart).length == 0) {
                    alert('You have no items in your cart.');
                    return;
                }
                let quantity = Object.values(cart).join(',');
                let product_ids = Object.keys(cart).join(',');
                // add ids and quantity to form
                // add ids to url
                $(this).parent().attr('action', $(this).parent().attr('action') + '/' + product_ids);
                // add hidden quantity array
                $(this).parent().append('<input type="hidden" name="quantity" value="' + quantity + '">');

                $(this).parent().submit();
            });

            $('.cart').on('click', function(e) {
                e.preventDefault();

                if (cart.length > 4) {
                    alert('You can only purchase 5 items at a time.');
                    return;
                }
                // make sure that sum of quantity is less than 5
                if (Object.values(cart).reduce((a, b) => parseInt(a) + parseInt(b), 0) > 4) {
                    alert('You can only purchase 5 items at a time.');
                    return;
                }

                let quantity = $(this).parent().find('input[name="quantity"]').val();
                let product_id = $(this).parent().parent().attr('action').split('/').pop();

                if (cart[product_id]) {
                    // ensure integer
                    cart[product_id] = parseInt(cart[product_id]) + parseInt(quantity);
                } else {
                    cart[product_id] = quantity;
                }

                updateCart();
            });
        });
    </script>
@endsection
