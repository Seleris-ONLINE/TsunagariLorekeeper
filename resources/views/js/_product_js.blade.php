<script>
    $(document).ready(function() {
        var $lootTable = $('#lootTableBody');
        var $lootRow = $('#lootRow').find('.loot-row');
        var $itemSelect = $('#lootRowData').find('.item-select');
        var $currencySelect = $('#lootRowData').find('.currency-select');

        $('#lootTableBody .selectize').selectize();
        attachRemoveListener($('#lootTableBody .remove-loot-button'));

        $('#addLoot').on('click', function(e) {
            e.preventDefault();
            // check that there is not a row within the table
            if ($lootTable.find('.loot-row').length > 0) {
                alert('You can only add one product to each entry.')
                return
            }
            var $clone = $lootRow.clone();
            $lootTable.append($clone);
            attachProductTypeListener($clone.find('.product-type'));
            attachRemoveListener($clone.find('.remove-loot-button'));
        });

        $('.product-type').on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().find('.loot-row-select');

            var $clone = null;
            if (val == 'Item') $clone = $itemSelect.clone();
            else if (val == 'Currency') $clone = $currencySelect.clone();

            $cell.html('');
            $cell.append($clone);
        });

        function attachProductTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.loot-row-select');

                var $clone = null;
                if (val == 'Item') $clone = $itemSelect.clone();
                else if (val == 'Currency') $clone = $currencySelect.clone();

                $cell.html('');
                $cell.append($clone);
                $clone.selectize();
            });
        }

        function attachRemoveListener(node) {
            node.on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });
        }

    });
</script>
