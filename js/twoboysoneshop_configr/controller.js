Event.observe(window, 'load', function() {
    /*
    $$('tr[data-field]').each(function(item) {
       var key = item.readAttribute('data-field');
       item.observe('click', function(event) {
          var storeId = event.originalTarget.readAttribute('data-store-id');

          console.log(key, storeId);

            new Ajax.Request('/configr/config/edit', {
                parameters: {
                    'configKey': key,
                    'storeId': storeId
                },
                evalJS: true,
                onSuccess: function(transport) {
                    console.log(transport.responseText);
                }
            });
       });
    });
    */

    $('applyFilter').observe('click', function() {
        $$('.store-select').each(function(item) {
            var store = item.readAttribute('rel');
            if ($F(item) !== null) {
                $$('.store.store-' + store).invoke('show');
            } else {
                $$('.store.store-' + store).invoke('hide');
            }
        });
    });

    $('select-all').observe('click', function() {
        $$('.store-select').each(function(item) {
            item.checked = 1;
        });
    });

    $('select-none').observe('click', function() {
        $$('.store-select').each(function(item) {
            item.checked = 0;
        });
    });

    $$('.configr-wrapper > h1').each(function(item) {
        item.observe('click', function() {

            var tabId = item.readAttribute('rel');

            $('div-tab-' + tabId).toggle();
        });
    });

    $$('.configr-wrapper h2').each(function(item) {
        item.observe('click', function() {

            var sectionId = item.readAttribute('rel');

            $('table-section-' + sectionId).toggle();
        });
    });

    $('typeadhead').observe('keyup', function(event) {
        $$('tr[data-field]').invoke('hide');
        $$('div.div-tab').invoke('hide');
        $$('table.table-section').invoke('hide');

        var searchValue = $F('typeadhead');

        var foundFields = $$('tr[data-field*=' + searchValue + ']');

        foundFields.invoke('show');
        foundFields.each(function(item) {
            item.up(1).show();
            item.up(2).show();
        });

    });
});