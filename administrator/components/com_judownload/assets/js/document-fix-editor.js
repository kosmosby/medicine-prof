function insertReadmore(editorId)
{
   jInsertEditorText('insert-readmore', editorId);
}

function jInsertEditorText( text, editorId )
{
    var editor = 'none';
    var hasTinyMce = jQuery('#'+editorId).hasClass('mce_editable');
    if(hasTinyMce){
         editor = 'tinymce';
    }else{
         var hasCodeMirror = jQuery('#'+editorId).siblings('.CodeMirror');
         if(hasCodeMirror.length > 0){
            editor = 'codemirror';
         }
    }

    if(text == 'insert-readmore'){
        var content = '';
        if(editor == 'tinymce'){
            content = tinyMCE.get(editorId).getContent();
        }else if(editor == 'codemirror'){
            content = Joomla.editors.instances[editorId].getValue();
        }else{
            content = document.getElementById(editorId).value;
        }
        if (content.match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i))
        {
            alert(Joomla.JText._("PLG_READMORE_ALREADY_EXISTS", "There is already a Read more... link that has been inserted. Only one such link is permitted. Use {pagebreak} to split the page up further."));
            return false;
        }
        text =  '<hr id="system-readmore" />';
    }

    if(editor == 'tinymce'){
        if (isBrowserIE())
        {
            if (window.parent.tinyMCE)
            {
                window.parent.tinyMCE.selectedInstance.selection.moveToBookmark(window.parent.global_ie_bookmark);
            }
        }
        tinyMCE.get(editorId).execCommand('mceInsertContent', false, text);
    }else if(editor == 'codemirror'){
        Joomla.editors.instances[editorId].replaceSelection(text);
    }else{
        insertAtCursor(document.getElementById(editorId), text);
    }
}

function jSelectArticle(id, title, catid, object, link, lang)
{
    var hreflang = '';
    if (lang !== '')
    {
        var hreflang = ' hreflang = "' + lang + '"';
    }
    var tag = '<a' + hreflang + ' href="' + link + '">' + title + '</a>';
    jInsertEditorText(tag, jQuery('#judl_add_article').val());
    SqueezeBox.close();
}

jQuery('.modal-button').on('click',function(){
   var hasAddArticle  = jQuery(this).find('.icon-file-add').length;
    if(hasAddArticle == 1){
        var textareaId = jQuery(this).closest('.controls').find('textarea').attr('id');
        var hasInput   = jQuery('#adminForm').find('#judl_add_article');

        if(hasInput.length == 0){
            var input = '<input type="hidden" id="judl_add_article" value="'+textareaId+'">';
            jQuery('#adminForm').append(input);
        }else {
            jQuery('#judl_add_article').val(textareaId)
        }
    }
});