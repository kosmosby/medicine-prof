// Remove and add inherit option to access list based on the parent category
kQuery(function($) {
    var access    = $('#access_raw'),
        category  = $('#category'),
        inherit   = access.children('option').first(),
        access_box = $('.current-access span').first(),
        default_access = access.data('default'),
        use_default = Koowa.translate('- Use default -'),
        use_inherit = inherit.text(),
        change_category = function(e) {
            inherit.text(e.val ? use_inherit : use_default);

            change_access();
        },
        change_access = function() {
            var value = access.val(),
                catid = category.val();

            if (value == -1) {
                if (catid) {
                    $.getJSON('?option=com_docman&view=category&format=json&id='+catid, function(data) {
                        $('.current-access').css('display', 'block');
                        access_box.text(data.entities[0].access_title);
                    });
                }
                else {
                    $('.current-access').css('display', 'block');
                    access_box.text(default_access);
                }
            }
            else {
                $('.current-access').css('display', 'none');
                access_box.text('');
            }
        };

    change_category({val: category.val()});

    category.on('change', change_category);
    access.on('change', change_access);
});