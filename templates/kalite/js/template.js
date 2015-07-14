(function($){

	$(document).ready(function() {

		var config = $('body').data('config') || {};

		// Accordion menu
		$('.menu-sidebar').accordionMenu({ mode:'slide' });

		// Dropdown menu
		$('#menu').droplineMenu({fancy:{mode:'move'}});

		// Smoothscroller
		$('a[href="#page"]').smoothScroller({ duration: 500 });

		// Fix Browser Rounding
		$('.grid-block').matchWidth('.grid-h');
		
		// Social buttons
		$('article[data-permalink]').socialButtons(config);

		var matchHeight = function() {
			// Match height of div tags
			$('#top-a .grid-h').matchHeight('.deepest');
			$('#top-b .grid-h').matchHeight('.deepest');
			$('#bottom-a .grid-h').matchHeight('.deepest');
			$('#bottom-b .grid-h').matchHeight('.deepest');
			$('#innertop .grid-h').matchHeight('.deepest');
			$('#innerbottom .grid-h').matchHeight('.deepest');
			$('#maininner, #sidebar-a, #sidebar-b').matchHeight();
			$('.wrapper').css("min-height", $(window).height());
		};
		
		matchHeight();
		
		$(window).bind('load',function(){
			$('#menu').trigger("menu:fixfancy");
			matchHeight();
		});
		
	});
	
	$.fn.droplineMenu = function(options){
		
		var defaults = {
			fancy: null
		};
		
		return this.each(function(){
			
			var ele    = $(this),
				level1 = ele.find("li.level1"),
				level2 = ele.find("li.level2"),
				ul2s   = ele.find("ul.level2").hide();
				dropdowns = ele.find("div.dropdown"),
				
				options = $.extend({}, defaults, options);
			
			level1.each(function(index){
				var li1 = $(this),
					ul2 = li1.find("ul.level2:first");
					
				
				if(li1.hasClass("active") && li1.hasClass("active")){
					ul2.show().addClass("remain");	
				}
				
				li1.bind("mouseenter", function(){
						
						if(ul2.hasClass("remain")) return;
						
						ul2s.stop().hide().removeClass("remain");
						
						if (ul2.length) {
							if ($.support.opacity) {
								ul2.stop().css("opacity",0).show().animate({opacity:1}).addClass("remain");
							} else {
								ul2.show().addClass("remain");
							}
						}
						
						ele.trigger("menu:enter", [li1, index]);
				});				
			});
			
			
			level2.each(function(i){
				var li2 = $(this),
					dropdown = li2.find("div.dropdown");
					
				li2.bind("mouseenter", function(){
						
						dropdowns.stop().hide();
						
						if (dropdown.length) {
							if ($.support.opacity) {
								dropdown.stop().show().css("opacity",0).animate({opacity:1});
							} else {
								dropdown.show();
							}
						}
				}).bind("mouseleave", function(){
					dropdowns.stop().hide();
				});				
			});
			
			
			if (options.fancy) {
	
				var fancyoptions = $.extend({
					mode: 'move',
					transition: 'easeOutExpo',
					duration: 500,
					onEnter: null,
					onLeave: null
				}, options.fancy)
				
				var fancy  = ele.append('<div class="fancy bg1"><div class="fancy-1"><div class="fancy-2"><div class="fancy-3"></div></div></div></div>').find(".fancy:first").hide();
				var start  = ele.find('.active:first');
				var fancycurrent = null;
				
				var fancyMove = function(to, show, index) {
					
					if (show && fancycurrent && to.get(0) == fancycurrent.get(0)) return;
					
					fancy.stop().show().css('visibility', 'visible');
					
					if(fancyoptions.mode == 'move') {
						
						if(!start.length && !show){
							fancy.hide();
						} else {
							fancy.animate({
									left : to.position().left+"px",
									width: to.width()+"px"
							}, fancyoptions.duration, fancyoptions.transition);
						}
					} else {
						
						if(show){
							fancy.css({
									opacity: start ? 0:1,
									left : to.position().left+"px",
									width: to.width()+"px"
							}).animate({opacity: 1},fancyoptions.duration);
						}else{
							fancy.animate({opacity: 0},fancyoptions.duration);
						}
					}
					
					fancycurrent = show ? to : null;
				};
				
				ele.bind({
					'menu:enter': function(e, current, index) { 
						fancyMove(current, true);

						if( fancyoptions.onEnter ) {
							fancyoptions.onEnter(current, index, fancy);
						}
						
					},
					'menu:leave': function(e, current, index) { 
						fancyMove(start, false);
						
						if( fancyoptions.onLeave ) {
							fancyoptions.onLeave(current, index, fancy);
						}
					},
					'menu:fixfancy': function(e) { 
						
						if(!fancycurrent) return;
						
						fancy.stop().show().css({
									left : fancycurrent.position().left+"px",
									width: fancycurrent.width()+"px"
						});
					}
				});
				
				if(start.length && fancyoptions.mode == 'move') fancyMove(start, true);
			}						
		});
	}
		
})(jQuery);