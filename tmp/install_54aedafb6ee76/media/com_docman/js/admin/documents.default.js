kQuery(function ($) {
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