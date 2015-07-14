kQuery(function($) {
    var grid   = $('.-koowa-grid'),
        controller = grid.data('controller'),
        buttons  = controller.buttons,
        move_button = $('#toolbar-move');

    if (move_button.length) {
        buttons.push(move_button);
    }

    controller.toolbar.find('a.toolbar').tooltip({
        placement: 'bottom',
        container: '.koowa'
    });

    grid.on('koowa:afterValidate', function() {
        var message  = 'You are not authorized to perform the %s action on these items',
            selected = Koowa.Grid.getAllSelected(),
            actions  = {
                'delete': 'core.delete',
                'edit': 'core.edit'
            },
            checkAction = function(action, selected) {
                var result = true;

                if (selected.length === 0) {
                    return false;
                }

                selected.each(function() {
                    var permissions = $(this).data('permissions'),
                        joomla_action = actions[action];

                    if (result == false) {
                        return;
                    }

                    result = permissions[joomla_action] || true;
                });

                return result;
            };

        buttons.each(function() {
            var button = $(this),
                action = button.data('action');

            /*if (button.hasClass('unauthorized')) {
                button.addClass('disabled');
                button.attr('data-original-title', message.replace('%s', action));
                return;
            }*/

            if (checkAction(action, selected) && selected.length > 0) {
                button.removeClass('disabled');
                button.attr('data-original-title', '');
            } else {
                button.addClass('disabled');
                if(selected.length > 0) {
                    button.attr('data-original-title', message.replace('%s', action));
                }
            }
        });

        return true;
    });

    grid.trigger('koowa:afterValidate');
});