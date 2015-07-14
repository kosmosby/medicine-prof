jQuery(document).ready(function ($) {
    process('cat', 0,0);
    function process(step, start, total)
    {
        $.ajax({
            type    : "GET",
            data    : { step: step, start: start, total: total},
            dataType: 'json',
            url     : "index.php?option=com_judownload&task=migration.process",
            success : function (response) {
                var step    = response['step'];
                var total   = response['total'];
                if(total == 0)
                {
                    var percent = 100;
                }
                else
                {
                    var percent = Math.floor(( (response['limit'] + start ) / total ) * 100);
                }

                // Display errors message
                if (typeof response["error"] != "undefined") {
                    var error = response["error"];
                    for (i = 0; i < error.length; i++) {
                        $("#image_errors").append('<li style="color:red;">' + error[i] + '</li>');
                    }
                }

                // Display message
                if (typeof response["message"] != "undefined") {
                    var message = response["message"];
                    for (i = 0; i < message.length; i++) {
                        $("#image_errors").append('<li style="color:#51a351;">' + message[i] + '</li>');
                    }
                }

                // Display warning message
                if (typeof response["warning"] != "undefined") {
                    var warning = response["warning"];
                    for (i = 0; i < warning.length; i++) {
                        $("#image_errors").append('<li style="color:#b89859;">' + warning[i] + '</li>');
                    }
                }

                if(typeof  response['stop'] != 'undefined' && response['stop'])
                {

                    return false;
                }

                start = response['limit'] + start;

                if (percent >= 100)
                {
                    $("#"+step+"-bar").width(100 + '%');

                    // reset for new processing
                    start = 0;
                    total = 0;
                    percent = 0;
                }
                else
                {
                    $("#"+step+"-bar").width(percent + '%');
                }

                var next_step = response['next_step'];
                if(next_step == '')
                {
                    alert(Joomla.JText._('COM_JUDOWNLOAD_FINISHED', 'Finished'));

                    return false;
                }
                else
                {
                    process(next_step, start, total);
                }
            }
        });
    }
});
