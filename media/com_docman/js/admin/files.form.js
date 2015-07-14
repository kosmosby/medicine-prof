kQuery(function($) {
    var requests = [],
        flattenArray = function(items){
            var object = {};
            $.each(items, function(i, item){
                object[item.name] = item.value;
            });
            return object;
        },
        getQueryString = function(form, defaults) {
            var values  = flattenArray($(form).serializeArray());

            $.each(defaults, function(key, value) {
                if (!values[key] || values[key] === '') {
                    if (typeof value !== 'undefined') {
                        values[key] = value;
                    }
                }
            });

            values['storage_type'] = 'file';

            return values;
        },
        humanizeString = function(string) {
            string = string.substring(0, string.lastIndexOf('.'));

            var last_slash = string.lastIndexOf('/');
            if (last_slash) {
                string = string.substring(last_slash+1);
            }

            string = string.replace(/[_\-\.]/g, ' ');

            string = string[0].toUpperCase()+string.substr(1);

            return string;
        },
        dehumanizeString = function(string) {
            var last_slash = string.lastIndexOf('/');
            if (last_slash) {
                string = string.substring(last_slash+1);
            }

            return string;
        },
        humanize = function() {
            $.each($('.document-form'), function(i, el) {
                var $el = $(el),
                    filename = $el.find('.file-name-input').val(),
                    input    = $el.find('input[name=title]');

                if (input.val() === dehumanizeString(filename)) {
                    input.val(humanizeString(filename));
                }
            });
        },
        dehumanize = function() {
            $.each($('.document-form'), function(i, el) {
                var $el = $(el),
                    filename = $el.find('.file-name-input').val(),
                    input    = $el.find('input[name=title]');

                if (input.val() === humanizeString(filename)) {
                    input.val(dehumanizeString(filename));
                }
            });
        };

    $('#humanized_titles').change(function() {
        var value = $(this).prop('checked');

        value ? humanize() : dehumanize();
    });

    requests.chain = function(callback){
        this.unshift(callback);
    };
    requests.callChain = function() {
        if (this.length) {
            this.pop().call();
        }
    };

    $('#document_list').find('.cancel').on('click', function(e) {
        e.preventDefault();

        var el = $(e.currentTarget).closest('.document-form');
        el.animate({
            opacity: 0.2,
            height: 0
        }, 300, function(){
            el.remove();
        });
    });

    $('#document-batch').data('controller').implement({
        '_actionApply': function() {
            var batch  = $('#document-batch');
                select = batch.find('select[name=docman_category_id]');

            if (!select.val()) {
                select.closest('.control-group').addClass('error');
                return;
            }

            var defaults = flattenArray(batch.serializeArray());

            requests.chain(function() {
                $('#toolbar-apply').find('a').addClass('disabled');
                $('select[name="docman_category_id"]').select2('readonly', true);
                var enabled = batch.find('.btn-group');
                enabled.addClass('disabled');
                $('label', enabled).unbind('click');
                $('input', enabled).attr('disabled', 'disabled');
                $('#humanized_titles').attr('disabled', 'disabled');
                $('.document-form').each(function(idx, el) {
                    $('input[name="title"]', el).attr('readonly', 'readonly');
                    $('textarea', el).attr('readonly', 'readonly');
                    $('select[name="docman_category_id"]').select2('readonly', true);
                    $('select[name="enabled"]').select2('readonly', true);
                });
            });

            requests.callChain();

            $('form.document-form').each(function(i, form) {
                requests.chain(function() {
                    $.ajax({
                        url: 'index.php?option=com_docman&view=document&format=json',
                        type: 'POST',
                        dataType: 'json',
                        data: getQueryString(form, defaults)
                    }).fail(function(){
                        if(window.console) console.error(this, arguments, form);
                    }).done(function(response, eventStatus, xhr){
                        if (xhr.status == 201) {
                            var item = response.entities[0],
                                form = $('form.document-form[data-path="'+item.storage_path+'"]'),
                                text = Koowa.translate('Continue editing this document: {document}');
                            form.empty();
                            form.append($('<p />').append($('<a />', {
                                'href': 'index.php?option=com_docman&view=document&id='+item.id,
                                'text': text.replace('{document}', item.title),
                                'target': '_blank'
                            })));
                        }
                    }).always(function(){
                        requests.callChain();
                    });
                });
            });

            requests.callChain();

            return requests;
        },
        '_actionSave': function() {
            var self = this;
            requests = this._actionApply();

            requests.chain(function() {
                self._actionCancel();
            });

            return requests;
        },
        '_actionCancel': function() {
            if (!$('form.document-form input[name=title]').length || confirm(Koowa.translate('You will lose all unsaved data. Are you sure?'))) {
                window.location = 'index.php?option=com_docman&view=files';
            }
        }
    });
});