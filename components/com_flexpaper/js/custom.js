jQuery(document).ready(function() {

    var script_path = jQuery(this).attr('script_path');

    jQuery(".egitim_pasif").fadeTo(1500 , 0.60);
    jQuery(".egitim_pasif").hover(function(){

        jQuery(this).fadeTo(0 , 100);

    }, function() {

        jQuery(this).fadeTo(500 , 0.60);
    });


    jQuery('.sol_kategori').click(function(event) {

        var $target = jQuery(event.target);
        $target.parent('li').find('ul').slideToggle();

    });

    jQuery('#runtest').click(function() {
        var testid = jQuery(this).attr('testid');
        var course_id = jQuery(this).attr('course_id');

        var data = {
            testid: testid,
            course_id: course_id
        };

        var ajaxurl = 'index.php?option=com_flexpaper&task=gettest&view=questions';

        jQuery.post(ajaxurl,data, function(response) {

            //alert(response)

            put_content(response);

        });
    });

    jQuery('#finish-test').live("click",function() {
        var data = jQuery('#questions-form').serialize();
        var ajaxurl = 'index.php?option=com_flexpaper&task=finishtest&view=questions';

        jQuery.post(ajaxurl,data, function(response) {

            //alert(response)

            put_content(response);
        });
    });


});

function put_content(response) {

    jQuery('#test_content').fadeOut(1000, function() {
        jQuery('#test_content').html(response);
        jQuery('#test_content').fadeIn();
    });

    return false;
}
