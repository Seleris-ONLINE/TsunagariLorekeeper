<div class="row no-gutters mb-2">
    <div class="col"></div>
    <div class="col-6">
        @if ($product->product->imageUrl)
            <img src="{{ $product->product->imageUrl }}" class="img-fluid mr-2 rounded" style="max-height: 25px;">
        @endif
        {!! $product->product->displayname !!}
    </div>
    <div class="col-3">
        {{ $quantity }}
    </div>
    <div class="col-2">
        <a href="#" class="remove-from-cart text-danger" data-id="{{ $product->id }}"><i class="fas fa-times"></i></a>
    </div>
</div>
