@extends('layouts.app')

@section('title') Cash Store @endsection

@section('content')
<div class="container">

    @if (Session::has('message'))
     <div class="alert alert-{{ Session::get('code') }}">
      {{ Session::get('message') }}
     </div>
    @endif
    
    @if($products->count() > 0)
        @if(count($products->where('is_bundle', 0)))
            <h3 class="text-center mb-4"> {{ $shop->title }} </h3>
                <div class="text-center">
                    {!! $shop->desc !!}
                </div>
            <hr>
            @include('paypal._products', ['products' => $products->where('is_bundle', 0)])
        @endif
        @if(count($products->where('is_bundle', 1)))
            <br>
            <h3 class="text-center mb-4"> {{ $shop->btitle }} </h3>
                <div class="text-center">
                    {!! $shop->bdesc !!}
                </div>
            <hr>
                @include('paypal._products', ['products' => $products->where('is_bundle', 1)])
        @endif
    @else
        <p>No products found! Come back soon.</p>
    @endif
</div>
@endsection