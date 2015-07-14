function submitbutton(action){
	if (typeof tinyMCE  != 'undefined') tinyMCE.execCommand('mceFocus', false,'mailbody');
    frm=document.adminForm;
	submitform(action);

}
function toggleMail(){
    frm=document.adminForm;
    el=document.getElementById("mailbody-tr");
    if (el.style.display=="none"){
	    frm.subject.disabled =false;
	    el.style.display="block";
    }else{
	    frm.subject.disabled =true;
	    el.style.display="none";
    }

}
function ClickShortcut(shortcut)
{
    if (typeof tinyMCE  == 'undefined') return;
    if (shortcut.indexOf('LINK%')>0)
        shortcut='<a href="'+shortcut+'">'+shortcut+'</a>';
    tinyMCE.execCommand("mceInsertContent",false,shortcut);
    return false;
}