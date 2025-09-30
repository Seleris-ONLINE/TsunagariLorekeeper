@php
    $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
    $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)
        ->orderBy('name')
        ->pluck('name', 'id');
@endphp

<table class="table table-sm" id="lootTable">
    <thead>
        <tr>
            <th width="35%">Product Type</th>
            <th width="35%">Product</th>
        </tr>
    </thead>
    <tbody id="lootTableBody">
        <tr class="loot-row">
            <td>
                {!! Form::select(
                    'product_type[]',
                    ['Item' => 'Item', 'Currency' => 'Currency'],
                    $product->product_type ?? null,
                    [
                        'class' => 'form-control product-type',
                        'placeholder' => 'Select Product Type'
                    ]
                ) !!}
            </td>
            <td class="loot-row-select">
                @if ($product->product_type == 'Item')
                    {!! Form::select('product_id[]', $items, $product->product_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                @elseif($product->product_type == 'Currency')
                    {!! Form::select('product_id[]', $currencies, $product->product_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                @endif
            </td>
        </tr>
    </tbody>
</table>

<div id="lootRowData" class="hide">
    {!! Form::select('product_id[]', $items, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::select('product_id[]', $currencies, null, ['class' => 'form-control currency-select', 'placeholder' => 'Select Currency']) !!}
</div>
