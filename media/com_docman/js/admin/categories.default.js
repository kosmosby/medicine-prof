kQuery(function($){
    var grid = $('.categories-grid'),
        controller = grid.data('controller'),
        delete_button = $('#toolbar-delete').find('a'),
        message = Koowa.translate('You cannot delete a category while it still has documents'),
        countDocuments = function() {
            var count = 0;

            Koowa.Grid.getAllSelected().each(function() {
                count += parseInt($(this).data('document-count'), 10);
            });

            return count;
        };

    controller.toolbar.find('a.toolbar').tooltip({
        placement: 'bottom'
    });

    grid.on('koowa:afterValidate', function() {
        if (countDocuments()) {
            delete_button.addClass('disabled');
            delete_button.tooltip('destroy');
            delete_button.tooltip({title: message, placement: 'bottom'});
        }
    });

    $('.footable tbody tr td').on('click', 'span.footable-toggle', function(event){
        event.stopPropagation();
    });
    /** Footable fix for Koowa selectables, preventing open/close to toggle select/deselect on table row */
    $('.footable').footable({
        toggleSelector: ' > tbody > tr:not(.footable-row-detail) .footable-toggle',
        breakpoints: {
            phone: 480,
            phablet: 600,
            tablet: 900
        }
    });

});