<script>
    $(document).ready(function() {
        var $lootTable = $('#lootTableBody');
        var $lootRow = $('#lootRow').find('.loot-row');
        var $itemSelect = $('#lootRowData').find('.item-select');
        var $currencySelect = $('#lootRowData').find('.currency-select');

        $('#lootTableBody .selectize').selectize();

        $('.product-type').on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().parent().find('.loot-row-select');

            var $clone = null;
            if (val == 'Item') $clone = $itemSelect.clone();
            else if (val == 'Currency') $clone = $currencySelect.clone();

            $cell.html('');
            $cell.append($clone);
        });
    });
</script>
