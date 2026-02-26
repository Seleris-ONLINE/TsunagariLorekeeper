@if ($product)
    {!! Form::open(['url' => 'admin/data/products/delete/' . $product->id]) !!}

    <p>You are about to delete the product <strong>{{ $product->product->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $product->product->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Product', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid products selected.
@endif
