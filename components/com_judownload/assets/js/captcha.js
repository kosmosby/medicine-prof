jQuery(document).ready(function ($) {
//    var systemMessage = $('#system-message-container').text();
//    var length = systemMessage.replace(/^\s+|\s+$/gm,'').length;
//   if(length > 0){
//       setTimeout(function(){
//           $(".reload-captcha").trigger('click');
//       },6000)
//   }

    $(".reload-captcha").on('click', function (e) {
        var src = $(".captcha-image").attr("src");
        var patt = /&reload=\d+/g;

        if (patt.test(src)) {
            src = src.replace(/&reload=\d+/, "&reload=" + new Date().getTime());
        } else {
            src += "&reload=" + new Date().getTime();
        }

        $(".captcha-image").attr("src", src);
        $(this).parent().find('.security_code').focus();
        e.preventDefault();
    });
});