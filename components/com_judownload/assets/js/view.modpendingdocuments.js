jQuery(document).ready(function ($) {

    // Checked all
    $("#judl-cbAll").click(function () {
        $("input.judl-cb", "form.judl-form").prop("checked", $(this).is(":checked"));
    });

    // Approval multi documents
    $("#judl-approve-pdocuments").on("click", function (e) {
        e.preventDefault();
        var n = $(".judl-cb:checked").length;
        if (n > 0) {
            $("form#judl-documents-form input[name='task']").val("modpendingdocuments.approve");
            $("form#judl-documents-form").submit();
        } else {
            alert(Joomla.JText._('COM_JUDOWNLOAD_NO_ITEM_SELECTED', 'No item selected!'));
        }
    });

    // Reject multi documents
    $("#judl-reject-pdocuments").on("click", function (e) {
        e.preventDefault();
        var n = $(".judl-cb:checked").length;
        if (n > 0) {
            var x = confirm(Joomla.JText._('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THESE_DOCUMENTS', 'Are you sure you want to delete these documents?'));
            if (x) {
                $("form#judl-documents-form input[name='task']").val("modpendingdocuments.delete");
                $("form#judl-documents-form").submit();
            }
        } else {
            alert(Joomla.JText._('COM_JUDOWNLOAD_NO_ITEM_SELECTED', 'No item selected!'));
        }
    });
});