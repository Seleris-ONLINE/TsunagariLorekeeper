@php
    $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
    $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)
        ->orderBy('name')
        ->pluck('name', 'id');
@endphp

<div id="lootRowData" class="hide">
    <table class="table table-sm">
        <tbody id="lootRow">
            <tr class="loot-row">
                <td>{!! Form::select('product_type', ['Item' => 'Item', 'Currency' => 'Currency'], null, ['class' => 'form-control product-type', 'placeholder' => 'Select Product Type']) !!}</td>
                <td class="loot-row-select"></td>
                <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
            </tr>
        </tbody>
    </table>
    {!! Form::select('product_id', $items, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::select('product_id', $currencies, null, ['class' => 'form-control currency-select', 'placeholder' => 'Select Currency']) !!}
</div>
