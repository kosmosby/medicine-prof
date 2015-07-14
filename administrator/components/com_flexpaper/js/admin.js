jQuery(document).ready(function($) {

	$('.get_certificate').click(function(){

        var type = jQuery(this).attr('cert_type');
        var user_id = jQuery(this).attr('user_id');
        var cert_id = jQuery(this).attr('cert_id');
        var recipient_admin = jQuery(this).attr('recipient_admin');

        var script_path = jQuery(this).attr('script_path');

        if(!cert_id) {
            alert('Create certificate first');
            return false;
        }

		var data = {
			user_id: user_id,
			type: type,
            cert_id: cert_id,
            recipient_admin: recipient_admin
		};

        var ajaxurl = script_path + '/index.php?option=com_flexpaper&task=sendcert&view=certificate';

		jQuery.post(ajaxurl,data, function(response) {

			jQuery('#response_message').html(response);

		return false;
        });
	});

    $('.get_results').click(function(){

        var user_id = jQuery(this).attr('user_id');
        var cert_id = jQuery(this).attr('cert_id');

        var script_path = jQuery(this).attr('script_path');

        var data = {
            user_id: user_id,
            cert_id: cert_id
        };

        var ajaxurl = script_path + '/index.php?option=com_flexpaper&task=sendresults&view=certificate';

        jQuery.post(ajaxurl,data, function(response) {

            jQuery('#response_message').html(response);

            return false;
        });
    });

    $('.delete_certificate').click(function(){
        var user_id = jQuery(this).attr('user_id');
        var cert_id = jQuery(this).attr('cert_id');

        var script_path = jQuery(this).attr('script_path');

        var data = {
            user_id: user_id,
            cert_id: cert_id
        };

        var ajaxurl = script_path + '/index.php?option=com_flexpaper&task=deletecertificate&view=certificate';

        jQuery.post(ajaxurl,data, function(response) {
            jQuery('#response_message').html(response);
            location.reload();
            return false;
        });
    });

    $('.create_certificate').click(function(){

        var user_id = jQuery(this).attr('user_id');
        var course_id = jQuery(this).attr('course_id');
        var passed = jQuery(this).attr('passed');
        var testid = jQuery(this).attr('testid');

        var script_path = jQuery(this).attr('script_path');

 	
        var data = {
            user_id: user_id,
            course_id: course_id,
            passed: passed,
            testid: testid
         };

        var ajaxurl = script_path + '/index.php?option=com_flexpaper&task=createcertificateAJAX&view=certificate';

        jQuery.post(ajaxurl,data, function(response) {
            jQuery('#response_message').html(response);
            location.reload();
            return false;
        });
    });




});
