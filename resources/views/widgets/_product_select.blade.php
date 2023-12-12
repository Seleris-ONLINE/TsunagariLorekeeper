@php
    $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
    $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)
        ->orderBy('name')
        ->pluck('name', 'id');
@endphp

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addLoot">Add Product</a>
</div>
<table class="table table-sm" id="lootTable">
    <thead>
        <tr>
            <th width="35%">Product Type</th>
            <th width="35%">Product</th>
            <th width="10%"></th>
        </tr>
    </thead>
    <tbody id="lootTableBody">
        @if ($loots)
            <tr class="loot-row">
                <td>{!! Form::select('product_type', ['Item' => 'Item', 'Currency' => 'Currency'], $loots->product_type, ['class' => 'form-control product-type', 'placeholder' => 'Select Product Type']) !!}</td>
                <td class="loot-row-select">
                    @if ($loots->product_type == 'Item')
                        {!! Form::select('product_id', $items, $loots->product_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                    @elseif($loots->product_type == 'Currency')
                        {!! Form::select('product_id', $currencies, $loots->product_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                    @endif
                </td>
                <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
            </tr>
        @endif
    </tbody>
</table>
