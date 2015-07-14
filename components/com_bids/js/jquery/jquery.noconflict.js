jQuery.noConflict();
//jQueryBids is created only once, so it holds all the registered plugins
var jQueryBids =  typeof(jQueryBids)=='undefined' ? jQuery.noConflict(true) : jQueryBids;

//needed for the plugins to work ???
jQuery = jQueryBids;