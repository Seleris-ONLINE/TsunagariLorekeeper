@extends('admin.layout')

@section('admin-title') Products @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Products' => 'admin/data/products']) !!}

<h1>Products</h1>

<p>Items that can be bought in the cash-store.</p>

<div class="text-right">
    <a href="{{ url('admin/data/products/create') }}" class="btn btn-primary text-right mb-3">Make New Product</a>
</div>

@if(!count($products))
    <p>No products found.</p>
@else 
    <table class="table table-sm shop-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Bundle?</th>
                <th>Visible?</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="sortable" class="sortable">
            @foreach($products as $product)
                <tr class="sort-item" data-id="{{ $product->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        <a href="{{ $product->product->url }}">{{ $product->product->name }} ({{ $product->product_type}})</a>
                    </td>
                    <td>${{ $product->price }}</td>
                    <td>@if($product->is_limited) @if($product->quantity == 0) Out of Stock @else {{ $product->quantity }} @endif @else Unlimited @endif</td>
                    <td>@if($product->is_bundle) <div class="text-success"> Yes @else <div class="text-danger"> No @endif</div></td>
                    <td>@if(true) <div class="text-success"> Yes @else <div class="text-danger"> No @endif</div></td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/products/edit/'.$product->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <div class="mb-4">
        {!! Form::open(['url' => 'admin/data/products/sort']) !!}
        {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div>
@endif
@endsection
@section('scripts')
@parent
<script>

$( document ).ready(function() {
    $('.handle').on('click', function(e) {
        e.preventDefault();
    });
    $( "#sortable" ).sortable({
        items: '.sort-item',
        handle: ".handle",
        placeholder: "sortable-placeholder",
        stop: function( event, ui ) {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        },
        create: function() {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        }
    });
    $( "#sortable" ).disableSelection();
});
</script>
@endsection