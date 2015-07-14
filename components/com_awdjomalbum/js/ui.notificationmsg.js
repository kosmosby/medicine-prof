/*
 * jQuery UI Notification Message
 *
 * Depends:
 *	    ui.core.js
 */
 
(function(jQuery) {
jQuery.widget("ui.notificationmsg", {
    
    init: function() {
        jQuery.ui.notificationmsg._bottompost=this.element.css("bottom");
        jQuery.ui.notificationmsg._height=this.element.css("height");  
    },
    
    show: function(){
        var o = this.options;
        if(this.element.is(":hidden")){
            this.element.queue(function(){jQuery.ui.notificationmsg.animations[o.animation](this, o);}).dequeue();
        }            
    },
    
    hide: function(){
        this.element.stop(true);
        var o = this.options;
        if(this.element.is(":visible")){
            this.element.queue(function(){jQuery.ui.notificationmsg.animations[o.animation](this, o);}).dequeue();
        }
    }
});    
jQuery.ui.notificationmsg._bottompost = "0px";
jQuery.ui.notificationmsg._css;
jQuery.extend(jQuery.ui.notificationmsg, {
    defaults: {
        // provide a speed for the animation
        speed: 1000,
        // provide a period for the popup to keep showing
        period: 2000, 
        // default the animation algorithm to the basic slide
        animation:'slide'
    },
    animations: {
        slide: function(e, options) {
            if(jQuery(e).is(":hidden")){
                
                //  animate
                $anim = jQuery(e).animate({height: "show"}, options.speed)
                
                if(options.period && options.period > 0){
                    $anim.animate({opacity: 1.0}, options.period)
                        .animate({height: "hide"}, options.speed);
                }
            }
            else{
                jQuery(e).animate({height: "hide"}, options.speed)
            }
            
            jQuery(e).css("height",jQuery.ui.notificationmsg._height);
        },
        fade: function(e, options) {
            if(jQuery(e).is(":hidden")){
                //  animate
                $anim = jQuery(e).animate({opacity: "show"}, options.speed);
                
                if(options.period && options.period > 0){
                    $anim.animate({opacity: 1.0}, options.period)
                        .animate({opacity: "hide"}, options.speed);
                }
            }
            else{
                jQuery(e).animate({opacity: "hide"}, options.speed);
            }
            
            jQuery(e).css("opacity",1.0);
        },
        slidethru: function(e, options) {
            //  set the position and left
            var b = jQuery.ui.notificationmsg._bottompost;
            var h = jQuery.ui.notificationmsg._height;
            if(jQuery(e).is(":hidden")){
                //  animate
                $anim = jQuery(e).animate({height: "show"}, options.speed);
                
                if(options.period && options.period > 0){                       
                    $anim.animate({opacity: 1.0}, options.period)
                        .animate({height: "hide", bottom: h}, options.speed)
                        .animate({bottom: b}, 1);
                }
            }
            else{
                jQuery(e).css({height:h,bottom:b});
                jQuery(e).animate({height: "hide", bottom: h}, options.speed)
                    .animate({bottom: b}, 1);
            }
            jQuery(e).css({height:h,bottom:b});
                           
        }
    }
});
})(jQuery);