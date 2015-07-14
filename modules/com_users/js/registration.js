jQuery(document).ready(function() {
   jQuery('.submission').submit(function() {

       jQuery('#jform_name').val(jQuery('#jform_first_name').val()+' '+jQuery('#jform_last_name').val());
       jQuery('#jform_email2').val(jQuery('#jform_email1').val());
       jQuery('#jform_username').val(jQuery('#jform_email1').val());

       return true;
   });
});