<div class="col-sm-3 col-6 text-center inventory-item" {{ $product->isOutOfStock <= 0 ? 'style="opacity: 50%"' : null }}>
    {!! Form::open(['url' => 'shops/products/purchase/' . $product->id]) !!}
    {{-- make card touch bottom of container --}}
    <div class="container d-flex flex-column justify-content-between">
        <h3>
            {!! $product->product->displayname !!}
            @if (Auth::check() && Auth::user()->isStaff)
            {{-- make smaller --}}
                <small>
                    <a href="{{ url('admin/data/products/edit/' . $product->id) }}" class="float-right">
                        <i class="fas fa-pencil-alt fa-fw text-muted fa-sm"></i>
                    </a>            
                </small>
            @endif
        </h3>
        @if ($product->product->imageurl)
            <div class="mb-2">
                <img style="width: 50%;" src="{{ $product->product->imageurl }}">
            </div>
        @endif
        <span>
            @if ($product->discount)
                {{-- danger pill --}}
                <span class="badge badge-danger">Discounted</span><br />
            @endif
            <b>Cost:</b>
            {!! $product->discount ? '<span class="text-danger"><del>' . $product->price . '</del></span> ' : null !!}
            {{ $product->discount ? $product->price - $product->discount : $product->price }}
            {{ Config::get('paypal.currency')}}
        </span>
        @if ($product->is_limited_stock)
            <div style="color: {{$product->stockColour}} !important;"> Stock: {{ $product->remaining_stock }} / {{ $product->total_stock }}</div>
            @if ($product->purchase_limit)
                <div class="text-danger"> Purchase Limit: {{ $product->purchase_limit }} per User</div>
            @endif
        @else
            <div class="text-success"> Stock: &infin;</div>
        @endif
        @if ($product->isOutOfStock)
            <div class="text-danger"> Out of Stock </div>
        @else
            {!! Form::number('quantity', 1, ['class' => 'form-control mb-2 text-center w-50 mx-auto', 'min' => 1]) !!}
            {!! Form::button('<i class="fas fa-shopping-cart"></i> Add to Cart', ['class' => 'btn btn-warning text-white btn-block cart', 'style' => 'background-color: #ed9c3c !important;']) !!}
            {!! Form::button('<i class="fab fa-paypal"></i> Buy Now', ['class' => 'btn btn-primary btn-block', 'type' => 'submit']) !!}
        @endif
    </div>
    {!! Form::close() !!}
</div>
