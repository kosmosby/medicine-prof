jQuery(document).ready(function ($) {
    $('select#report-subject').change(function () {
        $('#other').hide();
        if ($(this).val() == 'other') {
            $('#other').show();
            $('#other .other-subject').addClass('required').attr('minlength', '10');
        }
    });
});