jQuery(document).ready(function ($) {
    $(document).on('click', '.collection-vote button', function (e) {
        $(this).parent().hide();
        var collectionId = $(this).parent().attr('id').split('-')[2];
        var token = $('#token').val();
        var params = {
            phpPath: 'index.php?option=com_judownload&task=collection.vote&tmpl=component',
            id: collectionId,
            token: token
        };
        $(this).juvote(params);
        $(this).parent().show();
        e.preventDefault();
    });
});