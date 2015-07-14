jQuery(document).ready(function ($) {
    function importImagesAjax(start) {
        $.ajax({
            type    : "GET",
            data    : {start: start},
            dataType: 'json',
            url     : "index.php?option=com_judownload&task=csvprocess.importProcessing",
            success : function (response) {
                var processed = parseInt(response['processed']) + parseInt(start);
                var percent = Math.floor((processed / response['total']) * 100);

                $("#processed").show();
                $("#total").show();
                $("#processed").html(processed);
                $("#total").html(response['total']);

                //display errors
                if (typeof response["errors"] != "undefined") {
                    var errors = response["error"];
                    for (i = 0; i < errors.length; i++) {
                        $("#import_messages").append('<li style="color:red;">' + errors[i] + '</li>');
                    }
                }

                //display message
                if (typeof response["message"] != "undefined") {
                    var message = response["message"];
                    for (i = 0; i < message.length; i++) {
                        $("#import_messages").append('<li style="color:#51a351;">' + message[i] + '</li>');
                    }
                }

                //display warning
                if (typeof response["warning"] != "undefined") {
                    var warning = response["warning"];
                    for (i = 0; i < warning.length; i++) {
                        $("#import_messages").append('<li style="color:#b89859;">' + warning[i] + '</li>');
                    }
                }

                if (percent >= 100) {
                    $("#process_state").html(Joomla.JText._('COM_JUDOWNLOAD_IMPORT_CSV_FINISHED', 'Finished'));
                    $("#processed").html(response['total']);
                    $("#bar").width(100 + '%');

                    return false;
                }
                else {
                    $("#bar").width(percent + '%');

                    importImagesAjax(processed);
                }
            }
        });
    }

    importImagesAjax(0);
});
