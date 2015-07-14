// JavaScript Document
jQuery(document).ready(function() {
	
	var awdindex = 10000;
	
	jQuery('select.awd_Selectprivacy').each(function() {
		var tmpHTML = "";
		var currValue;
		
		jQuery(this).find('option').each(function() {
			if(jQuery(this).attr('selected')) {
				currValue = jQuery(this).val();
			}
		});
		tmpHTML += "<dl class='awd_dropDownMain'>\n";
		tmpHTML += "<dt name=" + currValue + " class='js_dropDown js_dropSelect-" + currValue + "'><strong>" + jQuery(this).find('option[selected="selected"]').text() + "</strong><span></span></dt>\n";
		tmpHTML += "<dd>\n<ul class='js_dropDownParent'>\n";
		
		jQuery(this).find('option').each(function() {
			var currOptVal = jQuery(this).val();			
			
			if(currOptVal == currValue) {
				tmpHTML += "<li class='js_dropDownCurrent'>";
			} else {
				tmpHTML += "<li>";
			}
			
			tmpHTML += "<a href='javascript:void()' name='" + currOptVal + "' class='js_dropDownChild js_dropDown-" + currOptVal + "'>" + jQuery(this).text() + "</a></li>\n";
		});
		
		tmpHTML += "</ul>\n</dd>\n</dl>";
		
		jQuery(this).parent().prepend(tmpHTML);
		
		jQuery(this).hide();

	});
	
	jQuery('.js_PriContainer').each(function() {
		jQuery(this).css('z-index', awdindex);
		awdindex -= 20;
	});
	
	jQuery('.js_dropDownChild').live('click',function(e) {
		e.preventDefault();
		var selectedVal = jQuery(this).attr('name'); 
		var selectedText = "";
		jQuery('#post_privacy').val(selectedVal);
		jQuery(this).closest('.js_PriContainer').find('option').each(function() {
			if(jQuery(this).val() == selectedVal) {
				jQuery(this).attr('selected', 'selected');
				selectedText = jQuery(this).text();
			} else {
				jQuery(this).attr('selected', false);
			}
			
		});
		
		var dropDownObj = jQuery(this).parent().parent().parent().parent().find('dt');
		var currShowVal = dropDownObj.attr('name');
		dropDownObj.removeClass('js_dropSelect-' + currShowVal).addClass('js_dropSelect-' + selectedVal).attr('name', selectedVal).html('<strong>' + selectedText + '</strong><span></span>');
		jQuery(this).parent().parent().parent().parent().data('state',0).removeClass('awd_Current').find('dd').hide();
	});
	
	jQuery('.awd_dropDownMain dt').live('click', function(e) {
		e.preventDefault();
		if (jQuery(this).parent().data('state')) {
			jQuery(this).parent().data('state',0).removeClass('awd_Current').find('dd').hide();
		} else {
			jQuery(this).parent().data('state',1).addClass('awd_Current').find('dd').show();
		}	
	})
});

