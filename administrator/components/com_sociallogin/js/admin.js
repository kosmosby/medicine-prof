jQuery(document).ready(function($) {

//    var random_value = randomString();
//
//    function randomString() {
//        var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
//        var string_length = 10;
//        var randomstring = '';
//        for (var i=0; i<string_length; i++) {
//            var rnum = Math.floor(Math.random() * chars.length);
//            randomstring += chars.substring(rnum,rnum+1);
//        }
//        return randomstring;
//    }


	$('#oa_social_login_test_api_settings').click(function(){
		var subdomain = jQuery('#oa_social_login_settings_api_subdomain').val();
		var key = jQuery('#oa_social_login_settings_api_key').val();
		var secret = jQuery('#oa_social_login_settings_api_secret').val();
				
		var data = {
			action: 'check_api_settings',
			api_subdomain: subdomain,
			api_key: key,
			api_secret: secret
		};

        var ajaxurl = 'index.php?option=com_sociallogin&task=check_api_settings';


		jQuery.post(ajaxurl,data, function(response) {	
			var message;		
			var success;
	
			if (response == 'error_not_all_fields_filled_out'){
				success = false;
				message = 'Please fill out each of the fields above'
			}
			else if (response == 'error_subdomain_wrong'){
				success = false;
				message = 'The subdomain does not exist. Have you filled it out correctly?'
			}
			else if (response == 'error_subdomain_wrong_syntax'){
				success = false;
				message = 'The subdomain has a wrong syntax!'				
			}
			else if (response == 'error_communication'){
				success = false;
				message = 'Could not contact API. Are outoing CURL requests allowed?'				
			}
			else if (response == 'error_authentication_credentials_wrong'){
				success = false;
				message = 'The API credentials are wrong';
			}
			else {
				success = true;
				message = 'The settings are correct - do not forget to save your changes!';
			}
		
			jQuery('#oa_social_login_api_test_result').html(message);
		
			if (success){
				jQuery('#oa_social_login_api_test_result').removeClass('error_message');
				jQuery('#oa_social_login_api_test_result').addClass('success_message');
			} else {
				jQuery('#oa_social_login_api_test_result').removeClass('success_message');
				jQuery('#oa_social_login_api_test_result').addClass('error_message');
			}		
			
		});
		return false;
	});
});