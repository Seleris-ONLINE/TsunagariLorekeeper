@extends('layouts.app')

@section('title') {{ Config::get('lorekeeper.cash_shop.shop_title') }} @endsection

@section('content')

<div class="container">
    @if($products->count() > 0)
        <h3 class="text-center mb-4"> {{ Config::get('lorekeeper.cash_shop.shop_title') }} </h3>
        <div class="text-center">
            {!! Config::get('lorekeeper.cash_shop.shop_description') !!}
        </div>
        <hr>
        {{-- chunk products on product_type --}}
        @foreach($products->groupBy('product_type') as $product_type => $products)
            <div class="container mb-2">
                <h4 class="text-center mb-2">{{ $product_type }}</h4>
                <hr class="w-50 ml-auto mr-auto" />
                @include('cash_shop._products', ['products' => $products])
            </div>
        @endforeach
    @else
        <p>No products found! Come back soon.</p>
    @endif
</div>
@endsection