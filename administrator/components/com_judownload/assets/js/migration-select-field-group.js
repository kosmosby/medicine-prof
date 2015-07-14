jQuery(document).ready(function ($) {
    $("select").change(function () {
        var value = $(this).val();
        var select_id = $(this).attr('id');

        if(value === '')
        {
            $('#text_value_' + select_id).show();
        }
        else
        {
            $('#text_value_' + select_id).hide();
        }
    });

    Joomla.submitbutton = function (task) {
        if (task == 'migration.getMappedExtraFieldGroups') {
            var is_empty = false;
                $('input[type="text"]').each(function(){
                var value = $(this).val();
                if($(this).is(':visible') && value == '')
                {
                    alert(Joomla.JText._('COM_JUDOWNLOAD_FIELD_GROUP_NAME_IS_EMPTY', 'Field group name is empty'));

                    $(this).css('border-color', 'red');

                    is_empty = true;
                }
                else
                {
                    $(this).css('border-color', '');
                }
            });

            if(!is_empty)
            {
                Joomla.submitform(task, document.getElementById('adminForm'));
            }
        }
        else
        {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    }
});
