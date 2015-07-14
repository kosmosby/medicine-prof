jQuery(document).ready(function ($) {

    // Checked all
    $("#judl-cbAll").click(function () {
        $("input.judl-cb", "form.judl-form").prop("checked", $(this).is(":checked"));
    });

    // Delete multi documents
    $("#judl-delete-documents").on("click", function (e) {
        e.preventDefault();
        var n = $(".judl-cb:checked").length;
        if (n > 0) {
            var x = confirm(Joomla.JText._('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THESE_DOCUMENTS', 'Are you sure you want to delete these documents?'));
            if (x) {
                $("form#judl-documents-form input[name='task']").val("moddocuments.delete");
                $("form#judl-documents-form").submit();
            }
        } else {
            alert(Joomla.JText._('COM_JUDOWNLOAD_NO_ITEM_SELECTED', 'No item selected!'));
        }
    });

    $("#judl-edit-document").on("click", function (e) {
        e.preventDefault();
        var n = $(".judl-cb:checked").length;
        if (n > 0) {
            $("form#judl-documents-form input[name='task']").val("form.edit");
            $("form#judl-documents-form").submit();
        } else {
            alert(Joomla.JText._('COM_JUDOWNLOAD_NO_ITEM_SELECTED', 'No item selected!'));
        }
    });

    $("#judl-publish-documents").on("click", function (e) {
        e.preventDefault();
        var n = $(".judl-cb:checked").length;
        if (n > 0) {
            var x = confirm(Joomla.JText._('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_PUBLISH_THESE_DOCUMENTS', 'Are you sure you want to publish these documents?'));
            if (x) {
                $("form#judl-documents-form input[name='task']").val("moddocuments.publish");
                $("form#judl-documents-form").submit();
            }
        } else {
            alert(Joomla.JText._('COM_JUDOWNLOAD_NO_ITEM_SELECTED', 'No item selected!'));
        }
    });

    $("#judl-unpublish-documents").on("click", function (e) {
        e.preventDefault();
        var n = $(".judl-cb:checked").length;
        if (n > 0) {
            var x = confirm(Joomla.JText._('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_UNPUBLISH_THESE_DOCUMENTS', 'Are you sure you want to unpublish documents?'));
            if (x) {
                $("#judl-documents-form input[name='task']").val("moddocuments.unpublish");
                $("form#judl-documents-form").submit();
            }
        } else {
            alert(Joomla.JText._('COM_JUDOWNLOAD_NO_ITEM_SELECTED', 'No item selected!'));
        }
    });
});