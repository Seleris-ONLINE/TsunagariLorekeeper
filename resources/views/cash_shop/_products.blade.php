<div class="row justify-content-center text-center">
    @foreach($products as $product)
        <div class="col-md-3 profile-inventory-item h-100" {{ ($product->is_limited && $product->quantity <= 0) ? 'style="opacity: 50%"' : null}} >
            {!! Form::open(['url' => 'cash-shop/purchase/'.$product->id]) !!}
                <div class="card p-3 mb-2">
                    <div class="text-center">
                        <h3><strong>{!! $product->product->displayname !!}</strong></h3>
                    </div>
                    @if($product->product->category) 
                        <h6>
                            <div class="text-muted text-center">
                                <a href="{{ $product->product->category->url }}">
                                    {!! $product->product->category->name !!}
                                </a>
                            </div>
                        </h6>
                    @endif
                    <div class="text-center inventory-character">
                        <div class="mb-1">
                            <img style="width: 50%;" src="{{ $product->product->imageurl }}">
                        </div>
                            <br>
                            <strong>Cost:</strong>
                            <br>
                            ${{ $product->price }}
                            <br>
                        @if($product->is_limited)
                            <div class="text-danger"> Stock: {{ $product->quantity }}/{{ $product->max }}</div>
                        @else 
                            <div class="text-success"> Stock: &infin;</div>
                        @endif
                        @if($product->is_limited)
                            @if($product->quantity > 0) 
                                {{ Form::number('amount', 1, [ 'class' => 'form-control mb-1', 'min' => 1,]) }}
                                {{ Form::submit('Pay via Paypal', array('class' => 'btn-info btn-sm btn text-white')) }} 
                            @else <div class="text-danger"> Out of Stock </div>
                                {{ Form::number('amount', 1, ['disabled' => 'disabled', 'class' => 'form-control mb-1', 'min' => 1,]) }}
                                {{ Form::submit('Pay via Paypal', array('disabled' => 'disabled', 'class' => 'btn-info btn text-white')) }}
                            @endif
                        @else
                            {{ Form::number('amount', 1, [ 'class' => 'form-control mb-1', 'min' => 1,]) }}
                            {{ Form::submit('Pay via Paypal', array('class' => 'btn-info btn-sm btn text-white')) }}
                        @endif
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    @endforeach
</div>