(function (window) {

    function install() {

        var Vue = window.Vue, _ = Vue.util, config = window.$config;

        Vue.url.options.root = config.url;
        Vue.http.headers.common['X-XSRF-TOKEN'] = config.csrf;
        Vue.http.options.emulateHTTP = true;
        Vue.http.options.beforeSend = function (request, options) {
            options.params.p = options.url;
            options.url = config.route;
        };

        Vue.url.base = function (url, params) {
            var options = url;

            if (!_.isPlainObject(options)) {
                options = {url: url, params: params};
            }

            if (!options.root) {
                options.root = config['root.plugin'];
            }

            return Vue.url(options);
        };

        Vue.url.route = function (url, params) {
            var options = url;

            if (!_.isPlainObject(params)) {
                params = {};
            }

            if (!_.isPlainObject(options)) {
                options = {url: url, params: params};
            }

            options = {
                url: config.route,
                params: _.extend(options.params, {
                    p: options.url
                })
            };

            return Vue.url(options);
        };

    }

    if (window.Vue) {
        jQuery(install);
    }

})(this);