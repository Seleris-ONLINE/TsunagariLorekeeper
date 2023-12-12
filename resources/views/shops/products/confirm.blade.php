@extends('shops.layout')

@section('shops-title')
    {{ Config::get('lorekeeper.products.shop_title') }} - Confirm Invoice #{{ $invoice->id }}
@endsection

@section('shops-content')
    {!! breadcrumbs(['Shops' => 'shops', 'Products' => 'shops/products', 'Confirm Invoice #' . $invoice->id => 'shops/products/confirm/' . $invoice->id]) !!}

    <h1>Confirm Invoice #{{ $invoice->id }}</h1>

    <div class="alert alert-info">
        <h3>Confirm Purchase</h3>
        <p>This will charge your chosen payment method and mark the invoice as paid.</p>
        <p>Your items will be distributed to you automatically.</p>
    </div>

    @include('shops.products._invoice', [
        'invoice' => $invoice,
        'confirm' => true,
    ])

    <div class="row justify-content-end mr-2">
        {!! Form::open(['url' => 'shops/products/paypal/cancel/' . $invoice->id, 'method' => 'POST']) !!}
        <div class="form-group mt-3">
            {!! Form::submit('Cancel Purchase', ['class' => 'btn btn-danger']) !!}
        </div>
        {!! Form::close() !!}

        {{-- justify to end --}}
        {!! Form::open(['url' => 'shops/products/paypal/confirm/' . $invoice->id, 'method' => 'POST']) !!}
        <div class="form-group mt-3 ml-2">
            {!! Form::submit('Complete Purchase', ['class' => 'btn btn-success']) !!}
        </div>
        {!! Form::close() !!}
    </div>
@endsection
