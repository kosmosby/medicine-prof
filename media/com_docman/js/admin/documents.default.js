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

    new DOCman.MoveDialog({
        view: '#document-move-modal',
        button: '.btn-primary',
        open_button: '#toolbar-move',
        category_selector: '#document_move_target'
    });
});

var DOCman = DOCman || {};

(function($) {

DOCman.MoveDialog = Koowa.Class.extend({
    target_category: null,

    initialize: function(options) {
        this.supr();

        options = {
            view: $(options.view),
            tree: $(options.view).find('.tree-container'),
            category_selector: $(options.category_selector),
            button: $(options.button, options.view),
            open_button: $(options.open_button)
        };

        this.setOptions(options);
        this.attachEvents();
    },
    attachEvents: function() {
        var self = this;

        if (this.options.open_button) {
            this.options.open_button.click(function(event) {
                event.preventDefault();

                self.show();
            });
        }

        if (this.options.view.find('form')) {
            this.options.view.find('form').submit(function(event) {
                event.preventDefault();

                self.submit();
            });
        }

        if (this.options.category_selector) {
            this.options.category_selector.on('change', function(e) {
                self.options.button.prop('disabled', !e.val);
            });
        }
    },
    show: function() {
        var options = this.options,
            count = Koowa.Grid.getAllSelected().length;

        if (options.open_button.hasClass('unauthorized') || !count) {
            return;
        }

        $.magnificPopup.open({
            items: {
                src: $(options.view),
                type: 'inline'
            }
        });
    },
    hide: function() {
        $.magnificPopup.close();
    },
    submit: function() {
        var controller = $('.-koowa-grid').data('controller'),
            selected = this.options.category_selector.val(),
            context = {};

        if (selected && Koowa.Grid.getAllSelected().length) {
            context.validate = true;
            context.data     = {
                docman_category_id: selected
            };
            context.data[controller.token_name] = controller.token_value;
            context.action = 'edit';

            controller.trigger('execute', [context]);
        }
    }
});

})(window.kQuery);