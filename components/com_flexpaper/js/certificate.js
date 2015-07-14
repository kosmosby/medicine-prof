jQuery(document).ready(function() { 

	jQuery('#sertificate_click').click(function() {

		var cert_number = jQuery('#sertifika_no').val();

		if (jQuery.trim(jQuery('#sertifika_no').val()) == '' || jQuery('#sertifika_no').val() == 'Sertifika Numarası' )
	    {
	        alert('Sertifika Numarasını Girin');
	    }
	    else
	    {
	    	
			var site_url = jQuery('#live_site').html();


	    	var data = {
				certificate: cert_number
			};      

	    	var ajaxurl = site_url + 'index.php?option=com_flexpaper&task=viewcertificate&view=certificate';

	         jQuery.post(ajaxurl,data , function(response) {

	            if (response)
	            {
	                //jQuery.colorbox({width:"50%",response});
	                jQuery.colorbox({href:response, width:"50%", transition: "elastic"});
	            }   
	           	return false;
	        });
	 
	    }    

	});

});