@extends('admin.layout')

@section('admin-title')
    Products
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Products' => 'admin/data/products', ($product->id ? 'Edit' : 'Create') . ' Products' => $product->id ? 'admin/data/products/edit/' . $product->id : 'admin/data/products/create']) !!}

    <h1>{{ $product->id ? 'Edit' : 'Create' }} Product
        @if ($product->id)
            <a href="#" class="btn btn-outline-danger float-right delete-products-button">Delete Product</a>
        @endif
    </h1>

    {!! Form::open(['url' => $product->id ? 'admin/data/products/edit/' . $product->id : 'admin/data/products/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Price') !!} {!! add_help('Do not include the $ / €') !!}
                {!! Form::text('price', $product->price ?? null, ['class' => 'form-control stock-field', 'min' => 1, 'placeholder' => 'Example: 5 or 5.00']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Discount (Optional)') !!} {!! add_help('Should be the set $ / €, not percentage discount') !!}
                {!! Form::text('discount', $product->discount ?? null, ['class' => 'form-control stock-field', 'min' => 1, 'max' => 100, 'placeholder' => 'Example: 5 or 5.00']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $product->is_visible ?? 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Should this product be buyable / visible yet?', ['class' => 'form-check-label ml-2 mb-2']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_limited_stock', 1, $product->is_limited_stock ?? 0, ['class' => 'is-limited-class form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_limited_stock', 'Does this product have limited stock?', ['class' => 'is-limited-label ml-2 form-check-label mb-2']) !!}
    </div>
    {{-- hidden form --}}
    <div class="row stock-check {{ $product->is_limited_stock ? '' : 'hide' }}">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('total_stock', 'Stock', ['class' => 'form-check-label mb-2']) !!}
                {!! Form::number('total_stock', $product->total_stock ?? null, ['class' => 'form-control', 'data-name' => 'quantity', 'min' => 0, 'placeholder' => 'Stock']) !!}
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('purchase_limit', 'Purchase Limit', ['class' => 'form-check-label mb-2']) !!}
                {!! Form::number('purchase_limit', $product->purchase_limit ?? null, ['class' => 'form-control', 'data-name' => 'purchase_limit', 'min' => 0, 'placeholder' => 'Purchase Limit']) !!}
            </div>
        </div>
    </div>

    @include(
        'widgets._product_select',
        $product->id
            ? [
                'loots' => (object) [
                    'product_id' => $product->product_id ?? null,
                    'product_type' => $product->product_type ?? null,
                ],
            ]
            : ['loots' => []]
    )

    <div class="text-right">
        {!! Form::submit($product->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._product_select_row')
@endsection

@section('scripts')
    @parent
    @include('js._product_js', ['showLootTables' => true, 'showRaffles' => true])
    <script>
        $(document).ready(function() {
            $('.is-limited-class').change(function(e) {
                // if checked unhide the stock fields
                if ($(this).prop('checked')) {
                    $('.stock-check').removeClass('hide');
                } else {
                    $('.stock-check').addClass('hide');
                }
            });

            $('.delete-products-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/products/delete') }}/{{ $product->id }}", 'Delete Product');
            });
        });
    </script>
@endsection
