@extends('admin.layout')

@section('admin-title')
    Products
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Products' => 'admin/data/products']) !!}

    <h1>
        Products
    </h1>

    <p>Items that can be bought in the product store.</p>

    <div class="text-right">
        <a href="{{ url('admin/data/products/invoices') }}" class="btn btn-primary text-right mb-3"><i class="fas fa-file-invoice fa-fw"></i> View Invoices</a>
        <a href="{{ url('admin/data/products/create') }}" class="btn btn-primary text-right mb-3"><i class="fas fa-plus fa-fw"></i> Create New Product</a>
    </div>

    @if (!count($products))
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
                @foreach ($products as $product)
                    <tr class="sort-item" data-id="{{ $product->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            <a href="{{ $product->product->url }}">{{ $product->product->name }} ({{ $product->product_type }})</a>
                        </td>
                        <td>
                            {{ $product->price }}
                            {{ Config::get('paypal.currency') }}
                        </td>
                        <td>
                            @if ($product->is_limited_stock)
                                @if ($product->remaining_stock == 0)
                                    Out of Stock
                                @else
                                    {{ $product->remaining_stock }} / {{ $product->total_stock }}
                                @endif
                            @else
                                Unlimited
                            @endif
                        </td>
                        <td>
                            @if ($product->product_type == 'Item' && $product->product->tag('box'))
                                <div class="text-success">Yes</div>
                            @else
                                <div class="text-danger">No</div>
                            @endif

                        </td>
                        <td>
                            @if ($product->is_visible)
                                <div class="text-success">Yes</div>
                            @else
                                <div class="text-danger">No</div>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/products/edit/' . $product->id) }}" class="btn btn-primary">Edit</a>
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
        $(document).ready(function() {
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortable").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
