var Logman = {
    Files: {
        init: function (config) {
            // Initialize variables
            if (!config) var config = {};
            if (!config.selector) config.selector = '.logman-file';

            $$(config.selector).addEvent('click', function (e) {
                e.preventDefault();

                var data = {};

                data.name = this.get('data-name');
                data.size = this.get('data-size');
                data.url = this.get('href');

                if (this.get('data-width') && this.get('data-height')) {
                    data.width = this.get('data-width');
                    data.height = this.get('data-height');
                    data.image = true;
                }

                var element = new Element('div', {html: new EJS({element: $('logman-file-template')}).render(data)});
                element.setStyle('display', 'inline-block').addClass('com_logman');
                var output = element.inject($('logman-file-tmp'));

                var display = function () {
                    var size = output.measure(function () {
                        return this.getSize();
                    });

                    SqueezeBox.open(output, {handler: 'adopt', size: {x: size.x, y: size.y}});
                };

                display.delay(100);
            });
        }
    },

    Message: {
        Parameters: {
            init: function (config) {
                // Initialize variables.
                if (!config) var config = {};
                this.container =  config.container ? config.container : '.message';
                this.content = config.content ? config.content : this.container + ' .text .parameter .content';
                this.text_length = config.text_length ? config.text_length : 50;
                this.word_length = config.word_length ? config.word_length : 25;

                var obj = this;

                $$(this.content).each(function (el) {
                        var text = el.innerHTML;
                        obj.truncate(el, (text.search(' ') === -1) ? obj.word_length : obj.text_length);
                    }
                );

                // Load tips lib.
                new Tips($$('.truncated'), {className: 'logman-tip tip-wrap'});
            },

            truncate: function (content, length) {
                var container_size = $$(this.container).pop().getSize();
                var content_size = $(content).getSize();
                var max_width = parseInt(container_size.x * length / 100);
                if (content_size.x > max_width ) {
                    var text = content.innerHTML;
                    var max_chars = parseInt(text.length * max_width / content_size.x);
                    $(content).addClass('truncated').set('title', ' ').set('rel', text);
                    if (text.length > max_chars) content.innerHTML = text.substr(0, max_chars) + '&hellip;';
                }
            }
        }
    },

    Export: function (config) {
        return (function ($) {
            var my = {
                init: function (config) {
                    config = config ? config : {};
                    this.container = config.container ? config.container : '#logman-export';
                    this.init_offset = config.init_offset ? config.init_offset : 0;
                    this.url = config.url;
                    this.timeout = config.timeout ? config.timeout : 30000;
                    this.exported = 0;

                    this.callbacks = {};

                    this.callbacks.success = function (data) {
                        // Update progress bar.
                        my.update(data);

                        if (data.remaining) {
                            my.request(my.url + '&offset=' + data.last);
                        } else {
                            // Export completed.
                            $(my.container).trigger('exportComplete', $.extend({}, data));
                        }
                    };

                    this.callbacks.error = function () {
                        alert('Export failed');
                    };
                },

                update: function (data) {
                    // Update total exported amount.
                    this.exported += parseInt(data.exported);
                    var completed = 100;
                    if (data.remaining) {
                        completed = parseInt(this.exported * 100 / (this.exported + parseInt(data.remaining)));
                    }
                    $(this.container).trigger('exportUpdate', $.extend({completed: completed}, data));
                },

                start: function () {
                    this.request(this.url + '&offset=' + this.init_offset);
                },

                request: function (url) {
                    $.ajax(url, {type: 'get', timeout: this.timeout, success: this.callbacks.success, error: this.callbacks.error});
                },

                bind: function (event, callback) {
                    $(this.container).on(event, callback);
                }
            };

            my.init(config);

            return my;
        })(jQuery);
    }
};
