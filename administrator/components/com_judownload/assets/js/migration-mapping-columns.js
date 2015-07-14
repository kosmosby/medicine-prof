jQuery(document).ready(function ($) {

    $("select").each(function(){
        var value = $(this).val();
        var select_id = $(this).attr('id');
        if( $('#text_value_' + select_id).length != 0)
        {
            if(value === '')
            {
                $('#text_value_' + select_id).css('display', 'block');
            }
            else
            {
                $('#text_value_' + select_id).css('display', 'none');
            }
        }
    });

    $("select").change(function () {
        var value = $(this).val();
        var select_id = $(this).attr('id');
        if( $('#text_value_' + select_id).length != 0)
        {
            if(value === '')
            {
                $('#text_value_' + select_id).css('display', 'block');
            }
            else
            {
                $('#text_value_' + select_id).css('display', 'none');
            }
        }
    });

    Joomla.submitbutton = function (task) {
        if (task != 'migration.back') {
            var duplicated = false;
            var assigned = [];
            var currentTabIndex = null;
            var errorTabIndex = null;
            $('select').each(function () {
                var value = $(this).val();
                var name  = $(this).attr('name');
                if (typeof assigned[name] != 'undefined' && jQuery.inArray(value, assigned[name]) > -1 && value != "")
                {
                    $("#duplicated_mapping").show();
                    $(this).css('border-color', 'red');

                    var id = $(this).attr('id');
                    if(id.indexOf("_") > -1)
                    {
                        id = id.split('_');
                        id = id[0];

                        // view has tab
                        if(typeof $("#custom-field-mappingContent") != 'undefined' && typeof $("div#"+id) != 'undefined')
                        {
                            currentTabIndex = $("#custom-field-mappingTabs li").index($("li.active"));
                            errorTabIndex   = $("#custom-field-mappingContent div").index($("div#" + id));
                        }
                    }

                    duplicated = true;
                } else if(value != -1 && value != -2 && value != 'param_value')
                {
                    if(typeof assigned[name] != 'undefined')
                    {
                        assigned[name].push(value);
                    }
                    else
                    {
                        assigned.push(name);
                        assigned[name] = [value];
                    }

                    $(this).css('border-color', '');
                }
            });

            if($('input[type="text"]').length != 0)
            {
                $('input[type="text"]').each(function(){
                    var value = $(this).val();

                    if($(this).css("display") == 'block' && value == '')
                    {
                        alert(Joomla.JText._('COM_JUDOWNLOAD_FIELD_NAME_IS_EMPTY', 'Field name is empty'));

                        $(this).css('border-color', 'red');
                        $(this).focus();

                        var id = $(this).attr('id');
                        if(id.indexOf("_") > -1)
                        {
                            id = id.split('_');
                            id = id[id.length -2];
                            // view has tab
                            if(typeof $("#custom-field-mappingContent") != 'undefined' && typeof $("div#"+id) != 'undefined')
                            {
                                currentTabIndex = $("#custom-field-mappingTabs li").index($("li.active"));
                                errorTabIndex   = $("#custom-field-mappingContent div").index($("div#" + id));
                            }
                        }

                        duplicated = true;
                    }
                    else
                    {
                        $(this).css('border-color', '');

                    }
                });
            }

            if(!duplicated)
            {
                Joomla.submitform(task, document.getElementById('adminForm'));
            }
            else
            {
                if(
                    errorTabIndex != null
                &&
                    currentTabIndex != null
                &&
                    errorTabIndex != currentTabIndex)
                {
                    $("#custom-field-mappingTabs li:eq("+currentTabIndex+")").removeClass("active");
                    $("#custom-field-mappingContent div:eq("+currentTabIndex+")").removeClass("active");

                    $("#custom-field-mappingTabs li:eq("+errorTabIndex+")").addClass('active');
                    $("#custom-field-mappingContent div:eq("+errorTabIndex+")").addClass("active");
                }
            }
        }
        else
        {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    }
});
