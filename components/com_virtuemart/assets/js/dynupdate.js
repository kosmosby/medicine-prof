/**
 * dynupdate.js: Dynamic update of product content for VirtueMart
 *
 * @package	VirtueMart
 * @subpackage Javascript Library
 * @author Max Galt
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

if (typeof Virtuemart === "undefined")
	var Virtuemart = {};
jQuery(function($) {
    // Add to cart and other scripts may check this variable and return while
    // the content is being updated.
    Virtuemart.isUpdatingContent = false;
    Virtuemart.updateContent = function(url) {
        if(Virtuemart.isUpdatingContent) return false;
        Virtuemart.isUpdatingContent = true;
        url += url.indexOf('&') == -1 ? '?tmpl=component' : '&tmpl=component';
        console.log("UpdateContent URI "+url);
        $.ajax({
            url: url,
            dataType: 'html',
            success: function(data) {
                var el = $(data).find(Virtuemart.containerSelector);
				if (! el.length) el = $(data).filter(Virtuemart.containerSelector);
				if (el.length) {
					Virtuemart.container.html(el.html());
                    Virtuemart.updateCartListener();
                    Virtuemart.updateDynamicUpdateListeners();
                    //Virtuemart.updateCartListener();

					if (Virtuemart.updateImageEventListeners) Virtuemart.updateImageEventListeners();
					if (Virtuemart.updateChosenDropdownLayout) Virtuemart.updateChosenDropdownLayout();
				}
				Virtuemart.isUpdatingContent = false;
            }
        });
        Virtuemart.isUpdatingContent = false;
    }

    // GALT: this method could be renamed into more general "updateEventListeners"
    // and all other VM init scripts placed in here.
    Virtuemart.updateCartListener = function() {
        // init VM's "Add to Cart" scripts
		Virtuemart.product(jQuery(".product"));
        //Virtuemart.product(jQuery("form.product"));
		jQuery('body').trigger('updateVirtueMartProductDetail');
        //jQuery('body').trigger('ready');
    }

    Virtuemart.updateDynamicUpdateListeners = function() {
        var elements = jQuery('*[data-dynamic-update=1]');
        elements.each(function(i, el) {

            var nodeName = el.nodeName;
            el = $(el);
            switch (nodeName) {
                case 'A':
					el[0].onclick = null;
                    el.click(function(event) {
                        event.preventDefault();
                        var url = el.attr('href');
                        setBrowserNewState(url);
                        Virtuemart.updateContent(url);
                    });
                   // console.log('updateDynamicUpdateListeners click ');//+i, el);
                    break;
                default:
					el[0].onchange = null;
                    el.change(function(event) {
                        event.preventDefault();
                        var url = jQuery(el).attr('url');
                        console.log('updateDynamicUpdateListeners found URL attri ',url,el);
                        if (typeof url === typeof undefined || url === false) {
                            url = el.val();
                            console.log('updateDynamicUpdateListeners URL attrib empty '+url);
                        }
                        if(url!=null){
                            console.log('updateDynamicUpdateListeners onchange set URL '+url);
                            setBrowserNewState(url);
                            Virtuemart.updateContent(url);
                        }

                    });
                   // console.log('updateDynamicUpdateListeners change '+i, el);
            }
        });

    }

    var everPushedHistory = false;
    var everFiredPopstate = false;
    function setBrowserNewState(url) {
        if (typeof window.onpopstate == "undefined")
            return;
        var stateObj = {
            url: url
        }
        everPushedHistory = true;
        console.log('setBrowserNewState '+url);
        history.pushState(stateObj, "", url);
    }

    var browserStateChangeEvent = function(event) {
        //console.log(event);
        // Fix. Chrome and Safari fires onpopstate event onload.
        // Also fix browsing through history when mixed with Ajax updates and
        // full updates.
        if (!everPushedHistory && event.state == null && !everFiredPopstate)
            return;

        everFiredPopstate = true;
        var url;
        if (event.state == null) {
            url = window.location.href;
        } else {
            url = event.state.url;
        }
        console.log('browserStateChangeEvent '+url);
        Virtuemart.updateContent(url);
    }
    window.onpopstate = browserStateChangeEvent;

});
