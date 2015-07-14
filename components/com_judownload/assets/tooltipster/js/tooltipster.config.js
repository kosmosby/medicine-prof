jQuery(document).ready(function ($) {
	$('.judl-tooltip').tooltipster({
		animation    : 'fade',
		interactive  : 'true',
		contentAsHTML: true,
		delay        : 200,
		position     : 'top',
		speed        : 300,
		theme        : 'tooltipster-shadow',
		functionInit : function (origin, content) {
			return origin.next('.judl-tooltip-content').children().addClass('jubootstrap').end().html();
		}
	});
});