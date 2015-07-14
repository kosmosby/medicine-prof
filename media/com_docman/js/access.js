(function($) {

/** @namespace Docman */
if (typeof DOCman === 'undefined') { //noinspection JSUndeclaredVariable
    DOCman = {};
}

// TODO translations

DOCman.Usergroups = Koowa.Class.extend({
    active: null,
    previous: null,
    getOptions: function() {
        return {
            category: '#category',
            form:     '.-koowa-form',
            entity:   'category'
        };
    },
    initialize: function(element, options) {
        var self = this;

        this.supr();
        this.setOptions(options);

        this.element = element = $(element);

        this.elements = {
            access_container:  element.find('.access_choices_container'),
            access:  element.find('.access_selector'),
            groups:  element.find('.group_selector'),
            inherit: element.find('input[name="inherit"]'),
            who_can_see: element.find('ul.who-can-see'),
            who_can_see_container: element.find('.who-can-see-container'),
            category: $(self.options.category),
            form: $(self.options.form)
        };

        var selected = element.data('selected');

        if (selected == 0) {
            this.switchTo('inherit');
        } else if (selected > 0) {
            this.switchTo('presets');
        }
        else {
            this.switchTo('groups');
        }

        this.elements.category.on('change', $.proxy(self.updateWhoCanSee, self));
        this.elements.access.on('change', $.proxy(self.updateWhoCanSee, self));

        this.elements.groups.on('change', function () {
            if (!$(this).select2('val').length) self.switchTo('inherit');
        });

        this.elements.inherit.on('click', function() {
            self.switchTo(self.elements.inherit.prop('checked') ? 'inherit' : (self.previous || 'groups'));
        });

        this.element.find('a[data-toggle]').click(function(event) {
            self.switchTo($(this).data('pane'), event);
        });

        if (this.elements.form) {
            var beforeSend = function() {
                var value  = self.getValue(),
                    form   = $(this);

                if (typeof value !== 'object') {
                    $('<input type="hidden" name="groups" />').val(value).appendTo(form);
                } else if (value) {
                    $.each(value, function(i, group) {
                        $('<input type="hidden" name="groups[]" />').val(group).appendTo(form);
                    });
                }
            };

            this.elements.form.on('koowa:beforeApply', beforeSend)
                             .on('koowa:beforeSave', beforeSend)
                             .on('koowa:beforeSave2new', beforeSend);
        }
    },
    setGroupsFromList: function(list) {
        var self = this;

        this.elements.who_can_see.empty();

        $.each(list, function(id, title) {
            self.elements.who_can_see.append($('<li>', {html: title}));
        });
    },
    updateWhoCanSee: function() {
        var self = this,
            is_inherited   = self.elements.inherit.prop('checked'),
            category_id    = self.elements.category.val(),
            inherit_label  = self.elements.inherit.siblings('span'),
            inherit_string = Koowa.translate('Use default');

        if (category_id) {
            if (this.options.entity === 'document') {
                inherit_string = Koowa.translate('Inherit from selected category');
            } else {
                inherit_string = Koowa.translate('Inherit from parent category');
            }
        }

        inherit_label.text(inherit_string);

        if (is_inherited && category_id) {

            self.setGroupsFromList(['<em>'+Koowa.translate('Calculating')+'</em>']);

            $.getJSON('?option=com_docman&view=category&format=json&id='+category_id)
                .then(function(data) {
                    var entity = data.entities[0];

                    if (entity) {
                        if (entity.access > 0) {
                            var level = DOCman.viewlevels[entity.access];

                            return $.Deferred().resolve({entities: [DOCman.viewlevels[entity.access]]});
                        }
                        else {
                            return $.getJSON('?option=com_docman&view=level&format=json&id='+Math.abs(entity.access));
                        }
                    }
                }).then(function(data) {
                    var level = data && data.entities ? data.entities[0] : {};

                    self.setGroupsFromList(level && level.group_list ? level.group_list : {});
                });
        }
        else {
            var level = is_inherited ? self.element.data('default-id') : self.elements.access.val();

            self.setGroupsFromList(DOCman.viewlevels[level] ? DOCman.viewlevels[level].group_list : []);
        }
    },
    switchTo: function(active, event) {
        this.previous = self.active;
        this.active   = active;

        if (active === 'inherit') {
            this.switchToInherit(event);
        }
        else if (active === 'presets') {
            this.switchToPresets(event);
        }
        else if (active === 'groups') {
            this.switchToGroups(event);
        }
    },
    switchToInherit: function() {
        this.elements.inherit.prop('checked', true);
        this.elements.access.prop('disabled', true);
        this.elements.groups.select2('enable', false);

        this.elements.who_can_see_container.css('display', 'block');

        this.element.find('li').removeClass('active');
        this.element.find('.tab-pane').removeClass('active');

        this.updateWhoCanSee();
        this.elements.access_container.hide();
    },
    switchToGroups: function(event) {
        this.elements.inherit.prop('checked', false);
        this.elements.access.prop('disabled', true);
        this.elements.groups.select2('enable', true);

        this.elements.who_can_see_container.css('display', 'none');

        if (!event) {
            this.element.find('a[data-pane=groups]').trigger('click');
        }

        this.updateWhoCanSee();
        this.elements.access_container.show();
    },
    switchToPresets: function(event) {
        this.elements.inherit.prop('checked', false);
        this.elements.access.prop('disabled', false);
        this.elements.groups.select2('enable', false);

        this.elements.who_can_see_container.css('display', 'block');

        if (!event) {
            this.element.find('a[data-pane=presets]').trigger('click');
        }

        this.updateWhoCanSee();
        this.elements.access_container.show();
    },
    getValue: function() {
        var value = null;

        if (this.active === 'inherit') {
            value = 0;
        }
        else if (this.active === 'groups') {
            value = this.elements.groups.val();
        }
        else if (this.active === 'presets') {
            value = this.elements.access.val();
        }

        return value;
    }
});

})(window.kQuery);