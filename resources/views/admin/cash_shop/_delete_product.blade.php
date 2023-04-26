@if($products)
    {!! Form::open(['url' => 'admin/data/products/delete/'.$products->id]) !!}

    <p>You are about to delete the product <strong>{{ $products->item->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $products->item->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Product', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid products selected.
@endif