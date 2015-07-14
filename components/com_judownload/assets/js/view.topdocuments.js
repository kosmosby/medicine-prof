jQuery(document).ready(function ($) {

    // -------------- Clear recently view documents --------------------------------
    $('#judl-clear-recent-documents').click(function () {
        var x = confirm(Joomla.JText._('COM_JUDOWNLOAD_ARE_YOU_SURE_YOU_WANT_TO_CLEAR_ALL_RECENTLY_VIEWED_DOCUMENTS', 'Are you sure you want to clear all recently viewed documents?'));
        if (x) {
            document.cookie = 'judl_recently_viewed_documents=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';
            alert(Joomla.JText._('COM_JUDOWNLOAD_CLEAR_ALL_RECENTLY_VIEWED_DOCUMENTS_SUCCESSFULLY', 'Clear recently viewed documents successfully'));
            $(this).hide();
        }
        return false;
    });
});