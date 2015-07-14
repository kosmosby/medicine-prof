/**

 * version 2.4

 * package AWDwallPRO-Joomla

 * author   AWDsolution.com

 * link http://www.AWDsolution.com

 * license GNU/GPL http://www.gnu.org/copyleft/gpl.html

 * copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.

*/

// post msg with ajax

function aPostMsg(url, url1)

{ 
	//select template
	var template = document.getElementById("select_temp").value;
	jQuery('#awd_message').removeClass('invalid');
	if(document.frm_message.awd_message.value != '' || (document.frm_message.awd_message.value == '' && document.getElementById("wid_tmp").value != '')){		

		document.getElementById("msg_loader").innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

	//	document.getElementById("msg_loader").style.display='block';

		jQuery.post(url, jQuery("#frm_message").serialize(), 

		function(data){
			if(document.getElementById("div_awd_attached_file") != undefined){
				document.getElementById("div_awd_attached_file").style.display = 'none';
				document.getElementById("type").value = "";
				document.getElementById("wid_tmp").value = "";
				document.getElementById("div_awd_attached_file").className = "";
				document.getElementById("div_awd_attached_img").innerHTML = "";
				document.getElementById("awd_attached_title").innerHTML = "";
				document.getElementById("awd_attached_file").innerHTML = "";
				document.getElementById("awd_attached_des").innerHTML = "";
				document.getElementById("count_img").value = "";
				jQuery('#awd-mainarea .postButton_small').poshytip('hide');
				}
			document.frm_message.awd_message.value = '';
			jQuery('#awd_message').trigger('blur');
			document.getElementById("msg_loader").innerHTML = "";
		//	document.getElementById("msg_loader").style.display='none';
			if(url1 != ''){
				getLatestPostByUserId(url1);
			}
			//jQuery("#msg_content").prepend(data);
			jQuery(data).hide().prependTo("#msg_content").fadeIn("slow");
			document.getElementById("status_loading").style.display = 'none';
			//jQuery('.postButton_small').poshytip('hide');
		}

		, "html");

	}

}

function getrealtimecomment(msgid)
{
	var Itemid=document.getElementById("Itemid").value;
	var d = new Date();
	var time = d.getTime();
	var url='index.php?option=com_awdwall&task=getrealtimecomment&wid='+msgid+'&Itemid='+Itemid+"&timestamp="+time;
	// alert(url);
	jQuery.get(url, {}, 

			function(data){
				if(data != '')
					jQuery(data).hide().prependTo("#c_content_" + msgid).fadeIn("slow");
					
			}, "html");
	
	window.setTimeout(function() {getrealtimecomment(msgid);}, 15000);
}

function getLatestPostByUserId(url)

{

	jQuery.get(url, {}, 

			function(data){

				if(data != '')

					document.getElementById("awd_profile_status").innerHTML = data;			

			}, "html");

}

function showlinevideo(url,id)
{
	var divname="video_"+id;
	var template = document.getElementById("select_temp").value;
	document.getElementById(divname).style.display="block";
	
	document.getElementById(divname).innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';
	jQuery.get(url, {}, 

			function(data){

				if(data != '')

					document.getElementById(divname).innerHTML = data;			

			}, "html");
	
	
}

function showCommentBox(url, cid, commenter_id, wall_id)

{

	var str = document.getElementById("c_" + cid).innerHTML;

	

	if(str.toLowerCase().indexOf("textarea") == -1){		

		document.getElementById("c_loader_" + cid).style.display = 'inline-block';	

		jQuery.get(url + '&cid=' + cid + '&commenter_id=' + commenter_id + '&wall_id=' + wall_id, {}, 

		function(data){

			document.getElementById("c_loader_" + cid).style.display = 'none';

			jQuery("#c_" + cid).append(data);			

		//	document.getElementById("c_" + cid).append(data);

		}, "html");

	}else{ 

		document.getElementById("c_loader_" + cid).style.display = 'inline-block';

		var t = setTimeout("document.getElementById('c_loader_" + cid + "').style.display = 'none'", 100);

		jQuery('#frm_comment_' + cid).remove();	

	}

}



function closeCommentBox(cid)

{

	document.getElementById("c_loader_" + cid).style.display = 'inline-block';

	var t = setTimeout("document.getElementById('c_loader_" + cid + "').style.display = 'none'", 100);

	jQuery('#frm_comment_' + cid).remove();

}



function aPostComment(url, cid)

{	

	document.getElementById("c_loader_" + cid).style.display = 'inline-block';

	if(document.getElementById("awd_comment_" + cid).value != "")

		jQuery.post(url, jQuery("#frm_comment_" + cid).serialize(), 

		function(data){
			jQuery('#frm_comment_' + cid).remove();	

			document.getElementById("c_loader_" + cid).style.display = 'none';
			jQuery(data).hide().prependTo("#c_content_" + cid).fadeIn("slow");
			//jQuery("#c_content_" + cid).prepend(data);		

		}

		, "html");

}



function openMsgDeleteBox(url, block_id)

{	
	//select template
	var template = document.getElementById("select_temp").value;
	jQuery.post(url, {}, function(data){});
	jQuery('#msg_block_' + block_id).remove();					
}

function check(){
	
	var Itemid=document.getElementById("Itemid").value;
	var d = new Date();
	var time = d.getTime();
	var  animStyle = 'fade';
	var url='index.php?option=com_awdwall&task=getnotification&Itemid='+Itemid+"&timestamp="+time;
	//jQuery("#msg1").notificationmsg('hide');
   		 if (jQuery("#msg1").is(":hidden")){
			
			jQuery.get(url, function(data){
				
				if(data.length>5 && data!=0){
					jQuery('embed').remove();
        			jQuery('body').append('<embed src="'+siteUrl+'components/com_awdwall/images/alarm.wav" autostart="true" hidden="true" loop="false">');
				 	jQuery("#modalbody").load(url);
					jQuery('#msg1').notificationmsg({animation:animStyle});
					jQuery('#msg1').notificationmsg('show'); 
				}
			});
			
			
			
		}
		 window.setTimeout(function() {check();}, 9000);
	}
	
	


jQuery(document).ready(function(){
	
	
	jQuery('#msg1').notificationmsg({period: 2500});	
	jQuery('#awd-mainarea').append('<div id="msg1"><div id="modal"><div class="modaltop"><div class="modaltitle">Notification Message</div><span id="closebutton" ><img src="' +siteUrl+'components/com_awdwall/images/purrClose.png" /></span></div><div class="modalbody"><span id="modalbody"></span></div><div class="notice-bottom"></div></div></div>');

	if(jQuery("#msg1").length>0)
	{
		//jQuery("#msg1").notificationmsg('hide');
	}
	
	jQuery("#closebutton").click(function () {
    	jQuery("#msg1").notificationmsg('hide');
    });
	
	if(jQuery("#msg1").length>0)
	{
			check();
	}
	jQuery("#awdnoticealert").click(function () {
    	jQuery("#dropAlerts").toggle();
    });

	checktotalnotification();
	jQuery('#dialog_msg_delete_box').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{

				Cancel: function(){

					jQuery(this).dialog('close');

				},

				'Ok': function(){
				
					//select template
					var template = document.getElementById("select_temp").value;
					document.getElementById("msg_delete_loader").innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';
					jQuery.post(document.getElementById("msg_delete_url").value, {}, function(data){});
					document.getElementById("msg_delete_loader").innerHTML = '';
					jQuery('#msg_block_' + document.getElementById("msg_delete_block_id").value).remove();					

					jQuery(this).dialog('close');

				}

		}

	});

	// comment

	jQuery('#dialog_c_delete_box').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{

				Cancel: function(){

					jQuery(this).dialog('close');

				},

				'Ok': function(){
					//select template
					var template = document.getElementById("select_temp").value;
					document.getElementById("c_delete_loader").innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

					jQuery.post(document.getElementById("c_delete_url").value, {}, function(data){});

					document.getElementById("c_delete_loader").innerHTML = '&nbsp;';

					jQuery('#c_block_' + document.getElementById("c_delete_block_id").value).remove();					

					jQuery(this).dialog('close');

				}

		}

	});

	// like

	jQuery('#dialog_like_box').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{

				Cancel: function(){

					jQuery(this).dialog('close');

				},

				'Ok': function(){
				
					//select template
					var template = document.getElementById("select_temp").value;

					document.getElementById("like_loader").innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

					jQuery.post(document.getElementById("like_url").value, {}, function(data){});

					document.getElementById("like_loader").innerHTML = '&nbsp;';					

					jQuery(this).dialog('close');

					getWhoLikeMsg('index.php?option=com_awdwall&amp;view=awdwall&amp;task=getlikemsg&amp;wid='+ document.getElementById("who_like_wid").value +'&amp;tmpl=component',document.getElementById("who_like_wid").value);
					// get who likes it data too

					jQuery.get(document.getElementById("who_like_url").value, {}, 

						function(data){	

							document.getElementById("wholike_box_" + document.getElementById("who_like_wid").value).innerHTML = data;

						}, "html");

				}

		}

	});

	// pm

	jQuery('#dialog_pm_delete_box').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{

				Cancel: function(){

					jQuery(this).dialog('close');

				},

				'Ok': function(){

					//select template
					var template = document.getElementById("select_temp").value;

					document.getElementById("pm_delete_loader").innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

					jQuery.post(document.getElementById("pm_delete_url").value, {}, function(data){});

					document.getElementById("pm_delete_loader").innerHTML = '&nbsp;';

					jQuery('#c_block_' + document.getElementById("pm_delete_block_id").value).remove();					

					jQuery(this).dialog('close');

				}

		}

	});

	

	// add as friend

	jQuery('#dialog_add_as_friend').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{

				Cancel: function(){

					jQuery(this).dialog('close');

				},

				'Ok': function(){
				
					//select template
					var template = document.getElementById("select_temp").value;

					document.getElementById("add_as_friend_loader").innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

					jQuery.post(document.getElementById("add_as_friend_url").value, {}, function(data){

						document.getElementById("add_as_friend").innerHTML = '';

					});

					document.getElementById("add_as_friend_loader").innerHTML = '&nbsp;';							

					jQuery(this).dialog('close');

					jQuery('#dialog_add_as_friend_msg').dialog().dialog('open');

				}

		}

	});

	// invite member

	jQuery('#dialog_awd_invite').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{

				Cancel: function(){

					jQuery(this).dialog('close');

				},

				'Ok': function(){
				
					//select template
					var template = document.getElementById("select_temp").value;

					document.getElementById("awd_invite_loader").innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

					jQuery.post(document.getElementById("awd_invite_url").value, {}, function(data){

						document.getElementById("awd_invite_div_" + document.getElementById("awd_invite_eid").value).innerHTML = data;

					});

					document.getElementById("awd_invite_loader").innerHTML = '&nbsp;';							

					jQuery(this).dialog('close');

					jQuery('#dialog_awd_invite_confirm').dialog().dialog('open');

				}

		}

	});

	// invite group confirm

	jQuery('#dialog_awd_invite_confirm').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{				

				'Close': function(){										

					jQuery(this).dialog('close');					

				}

		}

	});

	// joinn group

	jQuery('#dialog_join_group').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{

				Cancel: function(){

					jQuery(this).dialog('close');

				},

				'Ok': function(){
				
					//select template
					var template = document.getElementById("select_temp").value;

					document.getElementById("join_group_loader").innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

					jQuery.post(document.getElementById("join_group_url").value, {}, function(data){

						document.getElementById("awd_join_group").innerHTML = '';

					});

					document.getElementById("join_group_loader").innerHTML = '&nbsp;';							

					jQuery(this).dialog('close');

					jQuery('#dialog_join_group_msg').dialog().dialog('open');

				}//window.location.reload();

		}

	});

	// join group confirm

	jQuery('#dialog_join_group_msg').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{				

				'Close': function(){										

					jQuery(this).dialog('close');

					window.location.reload();

				}

		}

	});

	// delete friend

	

	jQuery('#dialog_friend_delete_box').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{

				Cancel: function(){

					jQuery(this).dialog('close');

				},

				'Ok': function(){ 

					//select template
					var template = document.getElementById("select_temp").value;
				
					//document.getElementById("friend_delete_loader").innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

					jQuery.post(document.getElementById("friend_delete_url").value, {}, function(data){

				jQuery('#msg_block_' + document.getElementById("friend_delete_block_id").value).remove();	

					});

					//document.getElementById("friend_delete_loader").innerHTML = '&nbsp;';							

					jQuery(this).dialog('close');

					

				}

		}

	});

	// add as friend

	jQuery('#dialog_add_as_friend_msg').dialog({ autoOpen: false, width: 400, resizable: false,

	buttons:{				

				'Close': function(){										

					jQuery(this).dialog('close');

				}

		}

	});

	

	// video

	jQuery('#dialog_video').dialog({ autoOpen: false, width: 450, height: 300, resizable: false});

	// image

	jQuery('#dialog_image').dialog({ autoOpen: false, width: 430, height: 250, resizable: false});

	jQuery("a[rel^='prettyPhoto']").prettyPhoto();

	// search

	jQuery("#search_user").autocomplete("index.php?option=com_awdwall&view=awdwall&task=auto&tmpl=component", { minChars:1, matchSubset:1, matchContains:1, cacheLength:10, onItemSelect:selectItem, formatItem:formatItem, selectOnly:1 });

	// control height of textarea

	// jQuery('#awd_message').css('width', '97%');

				// Google Chrome doesn't return correct outerWidth() else things would be nicer.

				// css('width', width()*2 - outerWidth(true));

	/*jQuery('#awd_message').css('width', jQuery('#awd_message').width() - parseInt(jQuery('#awd_message').css('borderLeftWidth'))

				                     - parseInt(jQuery('#awd_message').css('borderRightWidth'))

				                     - parseInt(jQuery('#awd_message').css('padding-left'))

				                     - parseInt(jQuery('#awd_message').css('padding-right')));
	*/
	//jQuery('#awd_message').css('width',582); 

	

	var	options = {};

	options.lineHeight = 0;

	options.minHeight = 50;

	options.maxHeight = options.maxHeight || 300;

	jQuery('#awd_message').autogrow(options);

	getNewMsg();

	

});



function openCommentDeleteBox(url, block_id)

{
	var template = document.getElementById("select_temp").value;
	jQuery.post(url, {}, function(data){});
	jQuery('#c_block_' + block_id).remove();					

}

function deleteLikeCommentBox(url, url1, wid)
{
var template = document.getElementById("select_temp").value;
var cloader='commentlike_'+wid;
document.getElementById(cloader).innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';
jQuery.post(url, {}, function(data){});
 window.setTimeout(getWhoLikeComment(url1,wid), 2000);
}

function openLikeMsgBox(url, url1, wid)
{
var template = document.getElementById("select_temp").value;
jQuery.post(url, {}, function(data){});
//jQuery(this).dialog('close');
var like_wid=wid;
 window.setTimeout(getWhoLikeMsg(url1,like_wid), 2000);
}
function openLikeCommentBox(url, url1, wid)
{
var template = document.getElementById("select_temp").value;
var cloader='commentlike_'+wid;
document.getElementById(cloader).innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';
jQuery.post(url, {}, function(data){});
 window.setTimeout(getWhoLikeComment(url1,wid), 2000);
}

function getWhoLikeComment(url, wid)
{
	
var template = document.getElementById("select_temp").value;
var cloader='commentlike_'+wid;
document.getElementById(cloader).innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';
	
			jQuery.get(url, {}, 

		function(data){	

			document.getElementById(cloader).innerHTML = data;		

		}, "html");

}

function getWhoLikeMsg(url, wid)

{	

	//var str = document.getElementById("like_" + wid).innerHTML;

	//select template
	var template = document.getElementById("select_temp").value;

	//if(str.toLowerCase().indexOf("whitebox") == -1){
	url=siteUrl+url;
		document.getElementById("like_" + wid).innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

		jQuery.get(url, {}, 

		function(data){	

			document.getElementById("like_" + wid).innerHTML = data;		

		}, "html");

	//}else{

		//document.getElementById("like_" + wid).innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

		//document.getElementById("like_" + wid).innerHTML = '';

	//}

}



function showPMBox(url, cid, commenter_id, wall_id)

{

	var str = document.getElementById("pm_" + cid).innerHTML;

	

	if(str.toLowerCase().indexOf("textarea") == -1){

		document.getElementById("pm_loader_" + cid).style.display = 'inline-block';	

		jQuery.get(url + '&cid=' + cid + '&commenter_id=' + commenter_id + '&wall_id=' + wall_id, {}, 

		function(data){

			document.getElementById("pm_loader_" + cid).style.display = 'none';

			jQuery("#pm_" + cid).append(data);

		//	document.getElementById("pm_" + cid).innerHTML = data;			

		}, "html");

	}else{ 		

		document.getElementById("pm_loader_" + cid).style.display = 'inline-block';

		var t = setTimeout("document.getElementById('pm_loader_" + cid + "').style.display = 'none'", 100);

		jQuery('#frm_pm_' + cid).remove();	

	}

}



function closePMBox(cid)

{

	document.getElementById("pm_loader_" + cid).style.display = 'inline-block';

	var t = setTimeout("document.getElementById('pm_loader_" + cid + "').style.display = 'none'", 100);

	jQuery('#frm_pm_' + cid).remove();

}



function aPostPM(url, cid)

{	

	document.getElementById("pm_loader_" + cid).style.display = 'inline-block';

	if(document.getElementById("awd_pm_" + cid).value != "")

		jQuery.post(url, jQuery("#frm_pm_" + cid).serialize(), 

		function(data){

			jQuery('#frm_pm_' + cid).remove();	

			document.getElementById("pm_loader_" + cid).style.display = 'none';

			jQuery("#c_content_" + cid).prepend(data);		

		}

		, "html");

}



function openPMDeleteBox(url, block_id)

{
//select template
var template = document.getElementById("select_temp").value;
jQuery.post(url, {}, function(data){});
jQuery('#c_block_' + block_id).remove();					
	

	//jQuery('#dialog_pm_delete_box').dialog().dialog('open');

}





function checkImageForm(){

	var frm = document.getElementById('frmUpuloadImage');	

	var flag = true;

	if(trim(frm.image_name.value) == ''){

		el = $('image_name');

		var labels = $$('label');

		labels.each(function(label){

			if (label.getProperty('for') == el.getProperty('id')) {

				label.addClass('invalid');

			}

		});	

		flag = false;

	}

	

	if(trim(frm.main_image.value) == ''){

		el = $('main_image');

		var labels = $$('label');

		labels.each(function(label){

			if (label.getProperty('for') == el.getProperty('id')) {

				label.addClass('invalid');

			}

		});	

		flag = false;

	}

	

	if(trim(frm.image_description.value) == ''){

		el = $('image_description');

		var labels = $$('label');

		labels.each(function(label){

			if (label.getProperty('for') == el.getProperty('id')) {

				label.addClass('invalid');

			}

		});	

		flag = false;

	}

	return flag;

}



function trim(str) {

	return str.replace(/^\s+/g, '').replace(/\s+$/g, '');

}



function isUrl(s){

	var regexp = /^(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/

	return regexp.test(s);

}



function isUrl1(s){

	var regexp = 

/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/

	return regexp.test(s);

}



// auto complete

function selectItem(li) {

	if (li.extra) {

		alert("That's '" + li.extra + "' you picked.")

	}

}

function formatItem(row) {

	//return row[0] + "<br><i>" + row[1] + "</i>";

	return row[0];

}



function getOlderPosts(url)

{

	var page = document.getElementById("awd_page").value;

	var task = document.getElementById("task").value;

	document.getElementById("older_posts_loader").style.display = 'inline-block';

	jQuery.get(url + '&awd_page=' + page + '&type=' + task, {}, 

	function(data){

		document.getElementById("older_posts_loader").style.display = 'none';

		page = parseInt(page)+1;

		document.getElementById("awd_page").value = page;		

		jQuery("#msg_content").append(data);

	}, "html");

}



function getOlderComments(url, cid)

{

	var page = document.getElementById("awd_c_page_" + cid).value;

	document.getElementById("older_comments_loader_" + cid).style.display = 'inline-block';

	jQuery.get(url + '&awd_c_page=' + page  + '&cid=' + cid, {}, 

	function(data){

		document.getElementById("older_comments_loader_" + cid).style.display = 'none';

		page = parseInt(page)+1;

		document.getElementById("awd_c_page_" + cid).value = page;		

		jQuery("#c_content_" + cid).append(data);

	}, "html");

}



function fbs_click(u, t) 

{	

	var width = 626;

    var height = 436;

    var left = parseInt((screen.availWidth/2) - (width/2));

    var top = parseInt((screen.availHeight/2) - (height/2));

	window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&t='+encodeURIComponent(t),' sharer', 'toolbar=0, status=0, width=' + width + ",height=" + height + ",status,resizable,left=" + left + ",top=" + top + "screenX=" + left + ",screenY=" + top); 

	return false; 

}



/* 

 * Auto Expanding Text Area (1.2.2)

 * by Chrys Bader (www.chrysbader.com)

 * chrysb@gmail.com

 *

 * Special thanks to:

 * Jake Chapa - jake@hybridstudio.com

 * John Resig - jeresig@gmail.com

 *

 * Copyright (c) 2008 Chrys Bader (www.chrysbader.com)

 * Licensed under the GPL (GPL-LICENSE.txt) license. 

 *

 *

 * NOTE: This script requires jQuery to work.  Download jQuery at www.jquery.com

 *

 */

 





// upload with ajax

jQuery.extend({

    createUploadIframe: function(id, uri)

	{

			//create frame

            var frameId = 'jUploadFrame' + id;

            

            if(window.ActiveXObject) {

              //  var io = document.createElement('<iframe id="' + frameId + '" name="' + frameId + '" />');
               var io = document.createElement('iframe');
				io.setAttribute("id",frameId);
				io.setAttribute("name",frameId);
				
                if(typeof uri== 'boolean'){

                    io.src = 'javascript:false';

                }

                else if(typeof uri== 'string'){

                    io.src = uri;

                }

            }

            else {

                var io = document.createElement('iframe');

                io.id = frameId;

                io.name = frameId;

            }

            io.style.position = 'absolute';

            io.style.top = '-1000px';

            io.style.left = '-1000px';



            document.body.appendChild(io);



            return io			

    },

    createUploadForm: function(id, fileElementId, fileTitle, fileDesc)

	{

		//create form	

		var formId = 'jUploadForm' + id;

		var fileId = 'jUploadFile' + id;

		var titleId = 'jTitle' + id;

		var descId = 'jDesc' + id;

		var form = jQuery('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');	

		

		var oldElement = jQuery('#' + fileElementId);

		var newElement = jQuery(oldElement).clone();

		jQuery(oldElement).attr('id', fileId);

		jQuery(oldElement).before(newElement);

		jQuery(oldElement).appendTo(form);

		

		var oldTElement = jQuery('#' + fileTitle);

		var newTElement = jQuery(oldTElement).clone();

		jQuery(oldTElement).attr('id', titleId);

		jQuery(oldTElement).before(newTElement);

		jQuery(oldTElement).appendTo(form);

		

		var oldDElement = jQuery('#' + fileDesc);

		var newDElement = jQuery(oldDElement).clone();

		jQuery(oldDElement).attr('id', descId);

		jQuery(oldDElement).before(newDElement);

		jQuery(oldDElement).appendTo(form);

		

		//set attributes

		jQuery(form).css('position', 'absolute');

		jQuery(form).css('top', '-1200px');

		jQuery(form).css('left', '-1200px');

		jQuery(form).appendTo('body');		

		return form;

    },



    ajaxFileUpload: function(s) {

        // TODO introduce global settings, allowing the client to modify them for all requests, not only timeout		

        s = jQuery.extend({}, jQuery.ajaxSettings, s);

        var id = new Date().getTime(); 

		var form = jQuery.createUploadForm(id, s.fileElementId, s.fileTitle, s.fileDesc);

		var io = jQuery.createUploadIframe(id, s.secureuri);

		var frameId = 'jUploadFrame' + id;

		var formId = 'jUploadForm' + id;		

        // Watch for a new set of requests

        if ( s.global && ! jQuery.active++ )

		{

			jQuery.event.trigger( "ajaxStart" );

		}            

        var requestDone = false;

        // Create the request object

        var xml = {}   

        if ( s.global )

            jQuery.event.trigger("ajaxSend", [xml, s]);

        // Wait for a response to come back

        var uploadCallback = function(isTimeout)

		{			

			var io = document.getElementById(frameId);

            try 

			{				

				if(io.contentWindow)

				{

					 xml.responseText = io.contentWindow.document.body?io.contentWindow.document.body.innerHTML:null;

                	 xml.responseXML = io.contentWindow.document.XMLDocument?io.contentWindow.document.XMLDocument:io.contentWindow.document;

					 

				}else if(io.contentDocument)

				{

					 xml.responseText = io.contentDocument.document.body?io.contentDocument.document.body.innerHTML:null;

                	xml.responseXML = io.contentDocument.document.XMLDocument?io.contentDocument.document.XMLDocument:io.contentDocument.document;

				}						

            }catch(e)

			{

				jQuery.handleError(s, xml, null, e);

			}

            if ( xml || isTimeout == "timeout") 

			{				

                requestDone = true;

                var status;

                try {

                    status = isTimeout != "timeout" ? "success" : "error";

                    // Make sure that the request was successful or notmodified

                    if ( status != "error" )

					{

                        // process the data (runs the xml through httpData regardless of callback)

                        var data = jQuery.uploadHttpData( xml, s.dataType );    

                        // If a local callback was specified, fire it and pass it the data

                        if ( s.success )

                            s.success( data, status );

    

                        // Fire the global callback

                        if( s.global )

                            jQuery.event.trigger( "ajaxSuccess", [xml, s] );

                    } else

                        jQuery.handleError(s, xml, status);

                } catch(e) 

				{

                    status = "error";

                    jQuery.handleError(s, xml, status, e);

                }



                // The request was completed

                if( s.global )

                    jQuery.event.trigger( "ajaxComplete", [xml, s] );



                // Handle the global AJAX counter

                if ( s.global && ! --jQuery.active )

                    jQuery.event.trigger( "ajaxStop" );



                // Process result

                if ( s.complete )

                    s.complete(xml, status);



                jQuery(io).unbind()



                setTimeout(function()

									{	try 

										{

											jQuery(io).remove();

											jQuery(form).remove();	

											

										} catch(e) 

										{

											jQuery.handleError(s, xml, null, e);

										}									



									}, 100)



                xml = null



            }

        }

        // Timeout checker

        if ( s.timeout > 0 ) 

		{

            setTimeout(function(){

                // Check to see if the request is still happening

                if( !requestDone ) uploadCallback( "timeout" );

            }, s.timeout);

        }

        try 

		{

           // var io = jQuery('#' + frameId);

			var form = jQuery('#' + formId);

			jQuery(form).attr('action', s.url);

			jQuery(form).attr('method', 'POST');

			jQuery(form).attr('target', frameId);

            if(form.encoding)

			{

                form.encoding = 'multipart/form-data';				

            }

            else

			{				

                form.enctype = 'multipart/form-data';

            }			

            jQuery(form).submit();



        } catch(e) 

		{			

            jQuery.handleError(s, xml, null, e);

        }

        if(window.attachEvent){

            document.getElementById(frameId).attachEvent('onload', uploadCallback);

        }

        else{

            document.getElementById(frameId).addEventListener('load', uploadCallback, false);

        } 		

        return {abort: function () {}};	



    },



    uploadHttpData: function( r, type ) {

        var data = !type;

        data = type == "xml" || data ? r.responseXML : r.responseText;

        // If the type is "script", eval it in global context

        if ( type == "script" )

            jQuery.globalEval( data );

        // Get the JavaScript object, if JSON is used.

        if ( type == "json" )

            eval( "data = " + data );

        // evaluate scripts within html

        if ( type == "html" )

            jQuery("<div>").html(data).evalScripts();

			//alert(jQuery('param', data).each(function(){alert(jQuery(this).attr('value'));}));

        return data;

    }

})

function openMp3Box()

{

	document.getElementById("awd_image_form").style.display = 'none';
	document.getElementById("awd_trail_form").style.display = 'none';
	document.getElementById("awd_video_form").style.display = 'none';

	document.getElementById("awd_link_form").style.display = 'none';

	document.getElementById("awd_file_form").style.display = 'none';

	document.getElementById("awd_pm_form").style.display = 'none';
	document.getElementById("awd_jing_form").style.display = 'none';
	document.getElementById("awd_event_form").style.display = 'none';
	document.getElementById("awd_article_form").style.display = 'none';
	jQuery('#awd_mp3_form').slideToggle('normal');

}

function ajaxMp3Upload(postUrl)

{ 

	if(!checkMp3Frm())

		return false;

	/*	

	jQuery("#mp3_loading")

	.ajaxStart(function(){

		

	})

	.ajaxComplete(function(){

		

	});

	*/

	document.getElementById("mp3_loading").style.display = 'block';

	jQuery.ajaxFileUpload

	(

		{

			url:postUrl,

			secureuri:false,

			fileElementId:'awd_link_mp3',

			fileTitle:'awd_mp3_title',

			fileDesc:'awd_mp3_desc',

			dataType: 'json',

			success: function (data, status)

			{	

				if(typeof(data.error) != 'undefined')

				{	

					if(data.error != '')

					{

					//	alert(data.error);

					}else

					{						

						document.getElementById("mp3_loading").style.display = 'none';

						closeMp3Box();

						document.getElementById("wid_tmp").value = data.wid_tmp;

						document.getElementById("type").value = 'mp3';

					    document.getElementById("awd_attached_file").innerHTML = data.file;

						document.getElementById("div_awd_attached_file").style.display = 'block';
						jQuery('.postButton_small').poshytip('show');
					}

				}

			},

			error: function (data, status, e)

			{

			 	//alert(e);

			}

		}

	)

	

	return false;

}



function checkMp3Frm()

{

	var frm = document.getElementById('frm_message');	

	var flag = true;

	if(frm.awd_mp3_title.value == ''){

		jQuery('#awd_mp3_title').addClass('invalid');

		flag = false;

	}

	if(frm.awd_link_mp3.value == ''){

		jQuery('#awd_link_mp3').addClass('invalid');

		flag = false;

	}

	if(flag)

		return true;

	else

		return false;

}



function closeMp3Box()

{

	jQuery('#awd_link_mp3').removeClass('invalid');

	jQuery('#awd_link_mp3').val('');

	jQuery('#awd_mp3_title').removeClass('invalid');

	jQuery('#awd_mp3_title').val('');

	jQuery('#awd_mp3_form').slideToggle('normal');

}

function openImageBox()

{
	document.getElementById("awd_mp3_form").style.display = 'none';
	document.getElementById("awd_video_form").style.display = 'none';
	document.getElementById("awd_link_form").style.display = 'none';
	document.getElementById("awd_file_form").style.display = 'none';
	document.getElementById("awd_trail_form").style.display = 'none';
	document.getElementById("awd_pm_form").style.display = 'none';
	document.getElementById("awd_jing_form").style.display = 'none';
	document.getElementById("awd_event_form").style.display = 'none';
	document.getElementById("awd_article_form").style.display = 'none';
	jQuery('#awd_image_form').slideToggle('normal');
}

function ajaxImageUpload(postUrl)

{

	if(!checkImageFrm())

		return false;

	/*

	jQuery("#image_loading")

	.ajaxStart(function(){

				

	})

	.ajaxComplete(function(){

				

	});*/

	document.getElementById("image_loading").style.display = 'block';

	jQuery.ajaxFileUpload

	(

		{

			url:postUrl,

			secureuri:false,

			fileElementId:'awd_link_image',

			fileTitle:'awd_image_title',

			fileDesc:'awd_image_description',

			dataType: 'json',

			success: function (data, status)

			{	

				if(typeof(data.error) != 'undefined')

				{

					if(data.error != '')

					{

					//	alert(data.error);

					}else

					{

						document.getElementById("image_loading").style.display = 'none';

						closeImageBox();

						document.getElementById("wid_tmp").value = data.wid_tmp;

						document.getElementById("type").value = 'image';

					    document.getElementById("awd_attached_file").innerHTML = data.file;

						document.getElementById("div_awd_attached_file").style.display = 'block';
						jQuery('.postButton_small').poshytip('show');
					}

				}

			},

			error: function (data, status, e)

			{

			//	alert(e);

			}

		}

	)

	

	return false;

}

function checkImageFrm()

{

	var frm = document.getElementById('frm_message');	

	var flag = true;

	if(frm.awd_image_title.value == ''){

		jQuery('#awd_image_title').addClass('invalid');

		flag = false;

	}

	if(frm.awd_link_image.value == ''){

		jQuery('#awd_link_image').addClass('invalid');

		flag = false;

	}

	if(frm.awd_image_description.value == ''){

		jQuery('#awd_image_description').addClass('invalid');

		flag = false;

	}

	if(flag)

		return true;

	else

		return false;

}

function closeImageBox()

{

	jQuery('#awd_image_title').removeClass('invalid');

	jQuery('#awd_image_title').val('');

	jQuery('#awd_link_image').removeClass('invalid');

	jQuery('#awd_link_image').val('');

	jQuery('#awd_image_description').removeClass('invalid');

	jQuery('#awd_image_description').val('');

	jQuery('#awd_image_form').slideToggle('normal');

}

function openVideoBox()
{
	document.getElementById("awd_mp3_form").style.display = 'none';
	document.getElementById("awd_image_form").style.display = 'none';
	document.getElementById("awd_link_form").style.display = 'none';
	document.getElementById("awd_file_form").style.display = 'none';
	document.getElementById("awd_trail_form").style.display = 'none';
	document.getElementById("awd_pm_form").style.display = 'none';
	document.getElementById("awd_jing_form").style.display = 'none';
	document.getElementById("awd_event_form").style.display = 'none';
	document.getElementById("awd_article_form").style.display = 'none';
	jQuery('#awd_video_form').slideToggle('normal');
}

function ajaxVideoUpload(postUrl)
{
	if(!checkVideoFrm())
		return false;
	document.getElementById("video_loading").style.display = 'block';
	jQuery.ajaxFileUpload
	(
		{
			url:postUrl,
			secureuri:false,
			fileElementId:'vLink',
			fileTitle:'awd_video_title',
			fileDesc:'awd_video_description',
			dataType: 'json',
			success: function (data, status)
			{	
				if(typeof(data.error) != 'undefined')
				{
					if(data.error != '')
					{
					//	alert(data.error);
					}else
					{
						document.getElementById("video_loading").style.display = 'none';
						closeVideoBox();
						document.getElementById("wid_tmp").value = data.wid_tmp;
						document.getElementById("type").value = 'video';
					    document.getElementById("awd_attached_file").innerHTML = data.file;
						document.getElementById("div_awd_attached_file").style.display = 'block';
						jQuery('.postButton_small').poshytip('show');
					}
				}
			},
			error: function (data, status, e)
			{
			//	alert(e);
			}
		}
	)
	return false;
}

function ajaxsoundcloudUpload(postUrl)
{
	//if(!checksoundcloudFrm())
	//	return false;
	document.getElementById("mp3_loading").style.display = 'block';
	jQuery.ajaxFileUpload
	(
		{
			url:postUrl,
			secureuri:false,
			fileElementId:'awd_soundcloudurl',
			fileTitle:'awd_mp3_title',
			fileDesc:'awd_mp3_desc',
			dataType: 'json',
			success: function (data, status)
			{	
				if(typeof(data.error) != 'undefined')
				{
					if(data.error != '')
					{
					//	alert(data.error);
					}else
					{
						document.getElementById("mp3_loading").style.display = 'none';
						closeMp3Box();
						document.getElementById("wid_tmp").value = data.wid_tmp;
						document.getElementById("type").value = 'mp3';
					    document.getElementById("awd_attached_file").innerHTML = data.file;
						document.getElementById("div_awd_attached_file").style.display = 'block';
						jQuery('.postButton_small').poshytip('show');
					}
				}
			},
			error: function (data, status, e)
			{
			//	alert(e);
			}
		}
	)
	return false;
}


function checksoundcloudFrm()
{
	var frm = document.getElementById('frm_message');	
	var flag = true;

	if(frm.awd_soundcloudurl.value == ''){

		jQuery('#awd_soundcloudurl').addClass('invalid');

		flag = false;

	}
	
}
function checkVideoFrm()

{

	var frm = document.getElementById('frm_message');	

	var flag = true;

	if(frm.vLink.value == ''){

		jQuery('#vLink').addClass('invalid');

		flag = false;

	}

	

	if(flag)

		return true;

	else

		return false;

}

function closeVideoBox()

{

	jQuery('#vLink').removeClass('invalid');

	jQuery('#vLink').val('');

	jQuery('#awd_video_form').slideToggle('normal');

}

//link form

function openLinkBox()

{
	document.getElementById("awd_mp3_form").style.display = 'none';
	document.getElementById("awd_trail_form").style.display = 'none';
	document.getElementById("awd_image_form").style.display = 'none';
	document.getElementById("awd_video_form").style.display = 'none';
	document.getElementById("awd_file_form").style.display = 'none';
	document.getElementById("awd_pm_form").style.display = 'none';
	document.getElementById("awd_jing_form").style.display = 'none';
	document.getElementById("awd_event_form").style.display = 'none';
	document.getElementById("awd_article_form").style.display = 'none';
	jQuery('#awd_link_form').slideToggle('normal');

}

//dangcv upload status
function ajaxStatusUpload(value)
{	
	document.getElementById("status_loading").style.display = 'block';
	jQuery.post("index.php?option=com_awdwall&task=ajaxstatuslink", { txt: value },
	function(data) {		
		var data = jQuery.parseJSON(data);
		var xmlHttp=GetXmlHttpObject();		
		if(data.url){
			document.getElementById("awd_link").value = data.url; 
			ajaxLinkStatusUpload('index.php?option=com_awdwall&task=addlink&wuid='+data.wuid);				
		}else{
			document.getElementById("status_loading").style.display = 'none';
		}
	}, "html");	
	
	return false;
}
function ajaxLinkStatusUpload(postUrl)
{
	if(!checkLinkFrm())
		return false;
	/*
	jQuery("#link_loading")
	.ajaxStart(function(){
	})
	.ajaxComplete(function(){
	});*/
	document.getElementById("link_loading").style.display = 'block';
	jQuery.post(postUrl, jQuery("#frm_message").serialize(), 
	function(data){
		document.getElementById("link_loading").style.display = 'none';
		jQuery('#awd_link').removeClass('invalid');	
		jQuery('#awd_link').val('');	
		var data = jQuery.parseJSON(data);
		var xmlHttp=GetXmlHttpObject();
		document.getElementById("wid_tmp").value = data.wid_tmp;
		document.getElementById("type").value = data.type;
		document.getElementById("awd_attached_title").innerHTML = data.title;
		document.getElementById("awd_attached_file").innerHTML = data.file;		
		document.getElementById("awd_attached_des").innerHTML = data.foo;
		document.getElementById("div_awd_attached_img").innerHTML = data.img;
		document.getElementById("count_img").value = data.count_img;		
		document.getElementById("sum_img").innerHTML = data.count_img;
		if(data.count_img == 0){
			document.getElementById("count_img_active").innerHTML = 0;
		}
		document.getElementById("div_awd_attached_file").className = 'class_div_awd_attached_file';
		document.getElementById("div_awd_attached_file").style.display = 'block';	
		document.getElementById("awd_link_form").style.display = 'none';	
		document.getElementById("status_loading").style.display = 'none';
		jQuery('#awd_link_form').hide();
	}
	, "html");	
	return false;
}

function ajaxLinkUpload(postUrl)

{

	if(!checkLinkFrm())

		return false;

	/*

	jQuery("#link_loading")

	.ajaxStart(function(){

		

	})

	.ajaxComplete(function(){

		

	});*/

	document.getElementById("link_loading").style.display = 'block';

	jQuery.post(postUrl, jQuery("#frm_message").serialize(), 

	function(data){
		document.getElementById("link_loading").style.display = 'none';
		//alert(data);
		closeLinkBox();
		var data = jQuery.parseJSON(data);
		var xmlHttp=GetXmlHttpObject();
		document.getElementById("wid_tmp").value = data.wid_tmp;
		document.getElementById("type").value = data.type;
		document.getElementById("awd_attached_title").innerHTML = data.title;
		document.getElementById("awd_attached_file").innerHTML = data.file;		
		document.getElementById("awd_attached_des").innerHTML = data.foo;
		document.getElementById("div_awd_attached_img").innerHTML = data.img;
		document.getElementById("count_img").value = data.count_img;		
		document.getElementById("sum_img").innerHTML = data.count_img;
		if(data.count_img == 0){
			document.getElementById("count_img_active").innerHTML = 0;
		}
		document.getElementById("div_awd_attached_file").className = 'class_div_awd_attached_file';
		document.getElementById("div_awd_attached_file").style.display = 'block';	
	}

	, "html");	

			

	return false;

}

function checkLinkFrm()

{	

	var frm = document.getElementById('frm_message');	

	var flag = true;

	if(frm.awd_link.value == ''){

		jQuery('#awd_link').addClass('invalid');

		flag = false;

	}

	

	if(flag)

		return true;

	else

		return false;

}

function closeLinkBox()

{

	jQuery('#awd_link').removeClass('invalid');	

	jQuery('#awd_link').val('');	

	jQuery('#awd_link_form').slideToggle('normal');

}



function openFileBox()

{
	document.getElementById("awd_image_form").style.display = 'none';
	document.getElementById("awd_video_form").style.display = 'none';
	document.getElementById("awd_link_form").style.display = 'none';
	document.getElementById("awd_trail_form").style.display = 'none';
	document.getElementById("awd_mp3_form").style.display = 'none';
	document.getElementById("awd_pm_form").style.display = 'none';
	document.getElementById("awd_jing_form").style.display = 'none';
	document.getElementById("awd_event_form").style.display = 'none';
	document.getElementById("awd_article_form").style.display = 'none';
	jQuery('#awd_file_form').slideToggle('normal');

}

function ajaxAwdFileUpload(postUrl)

{

	if(!checkFileFrm())

		return false;

	/*

	jQuery("#file_loading")

	.ajaxStart(function(){

		

	})

	.ajaxComplete(function(){

		

	});*/

	document.getElementById("file_loading").style.display = 'block';

	jQuery.ajaxFileUpload

	(

		{

			url:postUrl,

			secureuri:false,

			fileElementId:'awd_file_link',

			fileTitle:'awd_file_title',

			fileDesc:'awd_file_desc',

			dataType: 'json',

			success: function (data, status)

			{	

				if(typeof(data.error) != 'undefined')

				{

					if(data.error != '')

					{

					//	alert(data.error);

					}else

					{

						document.getElementById("file_loading").style.display = 'none';

						closeFileBox();

						document.getElementById("wid_tmp").value = data.wid_tmp;

						document.getElementById("type").value = 'file';

					    document.getElementById("awd_attached_file").innerHTML = data.file;

						document.getElementById("div_awd_attached_file").style.display = 'block';
						jQuery('.postButton_small').poshytip('show');
					}

				}

			},

			error: function (data, status, e)

			{

			//	alert(e);

			}

		}

	)

	

	return false;

}



function checkFileFrm()

{

	var frm = document.getElementById('frm_message');	

	var flag = true;

	if(frm.awd_file_title.value == ''){

		jQuery('#awd_file_title').addClass('invalid');

		flag = false;

	}

	if(frm.awd_file_link.value == ''){

		jQuery('#awd_file_link').addClass('invalid');

		flag = false;

	}
	

	if(flag)

		return true;

	else

		return false;

}



function closeFileBox()

{

	jQuery('#awd_file_link').removeClass('invalid');

	jQuery('#awd_file_link').val('');	

	jQuery('#awd_file_form').slideToggle('normal');

}

function openPmBox()

{

	document.getElementById("awd_mp3_form").style.display = 'none';

	document.getElementById("awd_video_form").style.display = 'none';

	document.getElementById("awd_link_form").style.display = 'none';

	document.getElementById("awd_file_form").style.display = 'none';

	document.getElementById("awd_image_form").style.display = 'none';

	jQuery('#awd_pm_form').slideToggle('normal');

}

function ajaxPmUpload(postUrl)

{

	if(!checkPmFrm())

		return false;

	jQuery("#pm_loading")

	.ajaxStart(function(){

		jQuery(this).show();

	})

	.ajaxComplete(function(){

		jQuery(this).hide();

	});

	

	if(document.frm_message.awd_pm_description.value != '' || (document.frm_message.awd_pm_description.value == '' && document.getElementById("wid_tmp").value != '')){		

		

		jQuery.post(postUrl, jQuery("#frm_message").serialize(), 

		function(data){

			if(document.getElementById("div_awd_attached_file") != undefined)

				document.getElementById("div_awd_attached_file").style.display = 'none';		

			document.frm_message.awd_pm_description.value = '';			

			

			jQuery("#msg_content").prepend(data);

			closePmBox();

		}

		, "html");

	}

	return false;

}

function checkPmFrm()

{

	var frm = document.getElementById('frm_message');	

	var flag = true;

	if(frm.awd_pm_description.value == ''){

		jQuery('#awd_pm_description').addClass('invalid');

		flag = false;

	}

	

	if(flag)

		return true;

	else

		return false;

}

function closePmBox()

{

	jQuery('#awd_pm_description').removeClass('invalid');

	jQuery('#awd_pm_description').val('');

	

	jQuery('#awd_pm_form').slideToggle('normal');

}



var i = 0;

function getNewMsg()

{
	if(document.getElementById("posted_wid") != null)
	var posted_wid = document.getElementById("posted_wid").value;

	var group_id = 0;

	if(document.getElementById("group_id") != undefined)

		group_id = document.getElementById("group_id").value;
		
	if(document.getElementById("wall_last_time") != null)
	var wall_last_time = document.getElementById("wall_last_time").value;

	var wuid = 0;

	if(document.getElementById("wuid") != undefined)

		wuid = document.getElementById("wuid").value;

	var task = 0;

	if(document.getElementById("awd_task") != undefined)

		task = document.getElementById("awd_task").value;

	if(document.getElementById("layout") != null)
	var layout = document.getElementById("layout").value;

	var end = parseInt(wall_last_time) + 15;

	var url = 'index.php?option=com_awdwall&view=awdwall&task=getlastestmsg&layout=' + layout + '&start=' + wall_last_time + '&end=' + end + '&posted_wid=' + posted_wid + '&groupid=' + group_id + '&wuid=' + wuid + '&type=' + task;

	if(i != 0){

		jQuery.get(url, {}, 

		function(data){//alert(data);
				if(document.getElementById("wall_last_time") != null)
				document.getElementById("wall_last_time").value = parseInt(wall_last_time) + 15;		
			if(data.length>2){
			//jQuery("#msg_content").prepend(data);
			jQuery(data).hide().prependTo("#msg_content").fadeIn("slow");
			}
		}, "html");

	}

	// get latest post

	i++;

	var t = setTimeout('getNewMsg()', 15000);

}

function openInviteBox(url, msg, confirm, eid)

{

	document.getElementById("awd_invite_url").value = url;

	document.getElementById("awd_invite_info").innerHTML = msg;

	document.getElementById("awd_invite_confirm").innerHTML = confirm;

	document.getElementById("awd_invite_eid").value = eid;

	

	jQuery('#dialog_awd_invite').dialog().dialog('open');

}

function openAddFriendBox(url)

{

	document.getElementById("add_as_friend_url").value = url;

	jQuery('#dialog_add_as_friend').dialog().dialog('open');

}

function openJoinGroupBox(url)

{

	document.getElementById("join_group_url").value = url;

	jQuery('#dialog_join_group').dialog().dialog('open');

}



function acceptInvite(url, uid)

{	 
	jQuery.get(url, {}, 

	function(data){

			document.getElementById("invite_" + uid).innerHTML = '';		

	}, "html");

}

function denyInvite(url, uid)

{	

	jQuery.get(url, {}, 

	function(data){

			jQuery('#invite_block_' + uid).remove();			

	}, "html");

}



function acceptFriend(url, uid)

{	

	jQuery.get(url, {}, 

	function(data){

			document.getElementById("friend_" + uid).innerHTML = '';		

	}, "html");

}



function denyFriend(url, uid)

{	

	jQuery.get(url, {}, 

	function(data){

			jQuery('#msg_block_' + uid).remove();			

	}, "html");

}



function openFriendDeleteBox(url, block_id)

{

	document.getElementById("friend_delete_url").value = url;

	document.getElementById("friend_delete_block_id").value = block_id;	

	jQuery('#dialog_friend_delete_box').dialog().dialog('open');

}

function openJingBox()
{
	document.getElementById("awd_mp3_form").style.display = 'none';
	document.getElementById("awd_image_form").style.display = 'none';
	document.getElementById("awd_video_form").style.display = 'none';
	document.getElementById("awd_file_form").style.display = 'none';
	document.getElementById("awd_pm_form").style.display = 'none';
	document.getElementById("awd_link_form").style.display = 'none';
	document.getElementById("awd_event_form").style.display = 'none';
	document.getElementById("awd_trail_form").style.display = 'none';
	document.getElementById("awd_article_form").style.display = 'none';
	jQuery('#awd_jing_form').slideToggle('normal');
}

function closeJingBox()
{
	jQuery('#awd_jing_title').removeClass('invalid');	
	jQuery('#awd_jing_title').val('');	
	jQuery('#awd_jing_link').removeClass('invalid');	
	jQuery('#awd_jing_link').val('');	
	jQuery('#awd_jing_description').removeClass('invalid');	
	jQuery('#awd_jing_description').val('');	
	jQuery('#awd_jing_form').slideToggle('normal');
}

function ajaxJingUpload(postUrl)
{
	if(!checkJingFrm())
		return false;
	document.getElementById("jing_loading").style.display = 'block';
	jQuery.post(postUrl, jQuery("#frm_message").serialize(), 
	function(data){
		var data1 = jQuery.parseJSON(data);
	awd_jing_title=document.getElementById("awd_jing_title").value;
	awd_jing_link=document.getElementById("awd_jing_link").value;
	document.getElementById("jing_loading").style.display = 'none';
	closeJingBox();
	document.getElementById("wid_tmp").value = data1.wid_tmp;
	document.getElementById("type").value = 'jing';
	document.getElementById("awd_attached_file").innerHTML='<a href="'+awd_jing_link+'" target="_blank">'+awd_jing_title+'</a>';
	document.getElementById("div_awd_attached_file").style.display = 'block';
	}
	, "html");	
	return false;
}
function checkJingFrm()
{	
	var frm = document.getElementById('frm_message');	
	var flag = true;
	if(frm.awd_jing_title.value == ''){
		jQuery('#awd_jing_title').addClass('invalid');
		flag = false;
	}
	if(frm.awd_jing_link.value == ''){
		jQuery('#awd_jing_link').addClass('invalid');
		flag = false;
	}
	if(frm.awd_jing_description.value == ''){
		jQuery('#awd_jing_description').addClass('invalid');
		flag = false;
	}
	
	if(flag)
		return true;
	else
		return false;
}
function getJingData(id)
{	
	url=siteUrl+"index.php?option=com_awdwall&task=getjincontent&id="+id;
	//alert(url);
	var DivId='#jing'+id;
	jQuery.post(url, function(data){
	var mainDiv=jQuery(data).find(".embeddedObject");
	  jQuery(DivId).html(mainDiv).find("iframe").remove();
	   jQuery(".embeddedObject").height(200);
	   jQuery(".embeddedObject").width(455);
	}, "html");
}

function showjing(jingid)
{
	var DivId='#jingp_'+jingid;
	
	url=siteUrl+"index.php?option=com_awdwall&task=getjincontent&id="+jingid;
	//alert(url);
	
	jQuery.post(url, function(data){
	var mainDiv=jQuery(data).find(".embeddedObject");
	  jQuery(DivId).html(mainDiv).find("iframe").remove();
	   jQuery(".embeddedObject").height(200);
	   jQuery(".embeddedObject").width(455);
	}, "html");
	
jQuery(DivId).toggle();
}

function getJingThumbData(jingid)
{
	var DivId='#sceenthumb_'+jingid;	
	url=siteUrl+"index.php?option=com_awdwall&task=getjinthumb&id="+jingid;
	
	//alert(DivId);
	jQuery.post(url, function(data){
 jQuery(DivId).html(jQuery(data));

}, "html");
}


function attendEvent(url,urll,wid)
{
var attend_event=document.getElementById("attend_event").value;
//alert(attend_event);
	jQuery.post(url+"&attend_event="+attend_event,{},
				function(data){
					 //alert(data);
				});
	
 window.setTimeout(getWhoAttendEvent(urll,wid), 2000);
}

function getWhoAttendEvent(url, wid)

{	

	var str = document.getElementById("event_" + wid).innerHTML;
	//select template
	var template = document.getElementById("select_temp").value;
//alert(template);
	//if(str.toLowerCase().indexOf("whitebox") == -1){
	url=siteUrl+url;
//alert(url);
		document.getElementById("event_" + wid).innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';
		jQuery.get(url, {}, 
				function(data){
					 document.getElementById("event_" + wid).innerHTML = data;	
				});

	//}else{

		//document.getElementById("event_" + wid).innerHTML = '<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';

		//document.getElementById("event_" + wid).innerHTML = '';

	//}

}


function openEventBox()

{
	document.getElementById("awd_mp3_form").style.display = 'none';
	document.getElementById("awd_image_form").style.display = 'none';
	document.getElementById("awd_trail_form").style.display = 'none';
	document.getElementById("awd_link_form").style.display = 'none';
	document.getElementById("awd_file_form").style.display = 'none';
	document.getElementById("awd_pm_form").style.display = 'none';
	document.getElementById("awd_video_form").style.display = 'none';
	document.getElementById("awd_jing_form").style.display = 'none';
	document.getElementById("awd_article_form").style.display = 'none';
//alert('ffff');
	jQuery('#awd_event_form').slideToggle('normal');

}

function closeEventBox()

{
	jQuery('#awd_event_title').removeClass('invalid');


	jQuery('#awd_event_title').val('');

	jQuery('#awd_event_location').removeClass('invalid');

	jQuery('#awd_link_location').val('');

	jQuery('#awd_event_description').removeClass('invalid');

	jQuery('#awd_event_description').val('');

	jQuery('#startdate').removeClass('invalid');

	jQuery('#startdate').val('');

	jQuery('#enddate').removeClass('invalid');

	jQuery('#enddate').val('');

	jQuery('#starttime-ampm').removeClass('invalid');

	jQuery('#starttime-ampm').val('');

	jQuery('#endtime-ampm').removeClass('invalid');

	jQuery('#endtime-ampm').val('');

	jQuery('#starttime-hour').removeClass('invalid');

	jQuery('#starttime-hour').val('');

	jQuery('#endtime-hour').removeClass('invalid');

	jQuery('#endtime-hour').val('');

	jQuery('#starttime-minute').removeClass('invalid');

	jQuery('#starttime-minute').val('');

	jQuery('#endtime-minute').removeClass('invalid');

	jQuery('#endtime-minute').val('');

	jQuery('#event_mail').removeClass('invalid');

	jQuery('#event_mail').val('');

	jQuery('#awd_event_image').removeClass('invalid');

	jQuery('#awd_event_image').val('');

	jQuery('#awd_event_form').slideToggle();
}

function ajaxEventUpload(postUrl)

{
//alert(postUrl);
	if(!checkEventFrm())
	
		return false;

	awd_event_title=document.getElementById("awd_event_title").value;
	awd_event_location=document.getElementById("awd_event_location").value;
	startdate=document.getElementById("startdate").value;
	enddate=document.getElementById("enddate").value;
	awd_event_starttime_ampm=document.getElementById("starttime-ampm").value;
	awd_event_endtime_ampmn=document.getElementById("endtime-ampm").value;
	awd_event_starttime_hour=document.getElementById("starttime-hour").value;
	awd_event_endtime_hour=document.getElementById("endtime-hour").value;
	awd_event_mail=document.getElementById("event_mail").value;
	awd_event_starttime_minute=document.getElementById("starttime-minute").value;
	awd_event_endtime_minute=document.getElementById("endtime-minute").value;
	//alert(awd_event_starttime_minute);
	document.getElementById("event_loading").style.display = 'block';
//alert( document.frm_message.event_mail.length);
	for (i=0; i < document.frm_message.event_mail.length; i++) {
	if (document.frm_message.event_mail[i].checked) {
	awd_event_mail=document.frm_message.event_mail[i].value;
	}
	}


 var temurl="&awd_event_location="+awd_event_location+"&startdate="+startdate+"&enddate="+enddate+"&starttimeampm="+awd_event_starttime_ampm+"&endtimeampm="+awd_event_endtime_ampmn+"&starttimehour="+awd_event_starttime_hour+"&endtimehour="+awd_event_endtime_hour+"&awd_event_mail="+awd_event_mail+"&starttimeminute="+awd_event_starttime_minute+"&endtimeminute="+awd_event_endtime_minute;
 var posturlnew=postUrl+temurl;
 
	jQuery.ajaxFileUpload

	(

		{

			url:posturlnew,

			secureuri:false,

			fileElementId:'awd_event_image',

			fileTitle:'awd_event_title',

			fileDesc:'awd_event_description',

			dataType: 'json',
			

			success: function (data, status)

			{	

						document.getElementById("event_loading").style.display = 'none';
						
						closeEventBox();
						
						document.getElementById("wid_tmp").value = data.wid_tmp;
						
						document.getElementById("type").value = 'event';
						
						document.getElementById("awd_attached_file").innerHTML =awd_event_title;
						
						document.getElementById("div_awd_attached_file").style.display = 'block';
						jQuery('.postButton_small').poshytip('show');


			},

			error: function (data, status, e)

			{

		//	alert(e);

			}

		}

	)

	

	return false;

}

function checkEventFrm()

{

	var frm = document.getElementById('frm_message');	

	var flag = true;

	if(frm.awd_event_title.value == ''){
		//alert('title blank');
		jQuery('#awd_event_title').addClass('invalid');

		flag = false;

	}

	if(frm.awd_event_location.value == ''){
//alert('location blank');
		jQuery('#awd_event_location').addClass('invalid');

		flag = false;

	}

	if(frm.startdate.value == ''){
//alert('start-time blank');
		jQuery('#startdate').addClass('invalid');

		flag = false;

	}

	if(frm.enddate.value == ''){
//alert('end-time blank');
		jQuery('#enddate').addClass('invalid');

		flag = false;

	}
	
	if(frm.awd_event_description.value == ''){
//alert('description blank');
		jQuery('#awd_event_description').addClass('invalid');

		flag = false;

	}
	
	else if(frm.startdate.value > frm.enddate.value) {
	alert("Event start date should not greater than end date.");	
		flag = false;
	}

	if(flag)

		return true;

	else

		return false;

}
function openTrailBox()
{
	document.getElementById("awd_mp3_form").style.display = 'none';
	document.getElementById("awd_image_form").style.display = 'none';
	document.getElementById("awd_video_form").style.display = 'none';
	document.getElementById("awd_file_form").style.display = 'none';
	document.getElementById("awd_pm_form").style.display = 'none';
	document.getElementById("awd_link_form").style.display = 'none';
	document.getElementById("awd_event_form").style.display = 'none';
	document.getElementById("awd_jing_form").style.display = 'none';
	document.getElementById("awd_article_form").style.display = 'none';
	jQuery('#awd_trail_form').slideToggle('normal');
}

function closeTrailBox()
{
	jQuery('#awd_trail_title').removeClass('invalid');	
	jQuery('#awd_trail_title').val('');	
	jQuery('#awd_trail_link').removeClass('invalid');	
	jQuery('#awd_trail_link').val('');	
	jQuery('#awd_trail_form').slideToggle('normal');
}

function ajaxTripUpload(postUrl)
{
	if(!checkTrailFrm())
		return false;
	document.getElementById("trail_loading").style.display = 'block';
	jQuery.post(postUrl, jQuery("#frm_message").serialize(), 
	function(data){
		var data1 = jQuery.parseJSON(data);
	awd_trail_title=document.getElementById("awd_trail_title").value;
	awd_trail_link=document.getElementById("awd_trail_link").value;
	document.getElementById("trail_loading").style.display = 'none';
	closeTrailBox();
	document.getElementById("wid_tmp").value = data1.wid_tmp;
	document.getElementById("type").value = 'trail';
	document.getElementById("awd_attached_file").innerHTML='<a href="'+awd_trail_link+'" target="_blank">'+awd_trail_title+'</a>';
	document.getElementById("div_awd_attached_file").style.display = 'block';
	}
	, "html");	
	return false;
}
function checkTrailFrm()
{	
	var frm = document.getElementById('frm_message');	
	var flag = true;
	if(frm.awd_trail_title.value == ''){
		jQuery('#awd_trail_title').addClass('invalid');
		flag = false;
	}
	if(frm.awd_trail_link.value == ''){
		jQuery('#awd_trail_link').addClass('invalid');
		flag = false;
	}
	if(flag)
		return true;
	else
		return false;
}


function openArticleBox()

{
	document.getElementById("awd_mp3_form").style.display = 'none';
	document.getElementById("awd_image_form").style.display = 'none';
	document.getElementById("awd_link_form").style.display = 'none';
	document.getElementById("awd_file_form").style.display = 'none';
	document.getElementById("awd_pm_form").style.display = 'none';
	document.getElementById("awd_video_form").style.display = 'none';
	document.getElementById("awd_jing_form").style.display = 'none';
	document.getElementById("awd_event_form").style.display = 'none';
//alert('ffff');
	jQuery('#awd_article_form').slideToggle('normal');

}

function closeArticleBox()

{
	jQuery('#awd_article_title').removeClass('invalid');

	jQuery('#awd_article_title').val('');

	jQuery('#awd_article_description').removeClass('invalid');

	jQuery('#awd_article_description').val('');

	jQuery('#awd_article_image').val('');

	jQuery('#awd_article_form').slideToggle();
}

function ajaxArticleUpload(postUrl)

{

	if(!checkArticleFrm())
	
		return false;

	awd_article_title=document.getElementById("awd_article_title").value;
	loadjomwall=document.getElementById("loadjomwall").value;
	catid=document.getElementById("catid").value;
	document.getElementById("article_loading").style.display = 'block';
	var postUrlnew=postUrl+'&loadjomwall='+loadjomwall+'&catid='+catid;

	jQuery.ajaxFileUpload

	(

		{

			url:postUrlnew,

			secureuri:false,

			fileElementId:'awd_article_image',

			fileTitle:'awd_article_title',

			fileDesc:'awd_article_description',

			dataType: 'json',
			
			success: function (data, status)

			{	//alert(data);

						document.getElementById("article_loading").style.display = 'none';
						
						closeArticleBox();
						
						document.getElementById("wid_tmp").value = data.wid_tmp;
						
						document.getElementById("type").value = 'article';
						
						document.getElementById("awd_attached_file").innerHTML =awd_article_title;
						
						document.getElementById("div_awd_attached_file").style.display = 'block';

						category=document.getElementById('catid');
						
						category.selectedIndex=0;
						
						loadjomwall_new=document.getElementById("loadjomwall");
						
						loadjomwall_new.selectedIndex=0;
						jQuery('.postButton_small').poshytip('show');
			},

			error: function (data, status, e)

			{

			//	alert(e);

			}

		}

	)

	

	return false;

}

function checkArticleFrm()

{

	var frm = document.getElementById('frm_message');	

	var flag = true;

	if(frm.awd_article_title.value == ''){
		//alert('title blank');
		jQuery('#awd_article_title').addClass('invalid');

		flag = false;

	}
	
	else if(frm.awd_article_description.value == ''){
//alert('description blank');
		jQuery('#awd_article_description').addClass('invalid');

		flag = false;

	}
	

	else if(frm.catid.value == 0){
		
		alert('Please select category');

		flag = false;
	}

	if(flag)

		return true;

	else

		return false;

}

function navigateurl(id,url,type)
{
	var urll='index.php?option=com_awdwall&task=delnotification&nid='+id;
	
	jQuery.post(urll, function(data){
	   if(type!='tag')
	   {
		window.location.href=url;
	   }
	   else
	   {
			jQuery.fn.colorbox({
				href:url,
				iframe:true, 
				width:"990px", 
				height:"550px", 
				scrolling: false,
				onLoad:function() {
					jQuery('html, body').css('overflow', 'hidden'); // page scrollbars off
				}, 
				onClosed:function() {
					jQuery('html, body').css('overflow', ''); // page scrollbars on
				}
			});
	   }
	});

}
function checktotalnotification()
{
	var Itemid=document.getElementById("Itemid").value;
	var d = new Date();
	var time = d.getTime();
	var  animStyle = 'fade';
	var url='index.php?option=com_awdwall&task=gettotalnotification&Itemid='+Itemid+'&timestamp='+time;
			jQuery.get(url, function(data){
				if(data.length>5 && data!=0){
					if (jQuery("#notifications-popover .content")[0]){
							jQuery("#notifications-popover .content").load(url);
					}
					else
					{	
						 jQuery("#alertimg").attr("src", siteUrl+'components/com_awdwall/images/alertnoticeon.png');
						jQuery("#dropAlerts").load(url);
					}
				}
			});
		 window.setTimeout(function() {checktotalnotification();}, 9000);
}


function showallfriends(id)
{
	
	var url='index.php?option=com_awdwall&task=seetotalfriends&id='+id;
	//alert(url);
	var Itemid=document.getElementById("Itemid").value;
		jQuery.ajax(
		{
			type: "POST",
			url: url,
			dataType: "json",
			success: function(data)
			{
				
				if(data)
				{
					var divcomment='#awdlightcontentbox';
					jQuery(divcomment).html(data.message);
					jQuery("#awdlightbox, #awdlightbox-panel").fadeIn(300);
				}
			}
		});
	return false;
}

function getCommentlikeList(wid)
{
	
	var url='index.php?option=com_awdwall&task=getCommentlikeList&wid='+wid;
	//alert(url);
	var Itemid=document.getElementById("Itemid").value;
		jQuery.ajax(
		{
			type: "POST",
			url: url,
			dataType: "json",
			success: function(data)
			{
				
				if(data)
				{
					var divcomment='#awdlightcontentbox';
					jQuery(divcomment).html(data.message);
					jQuery("#awdlightbox, #awdlightbox-panel").fadeIn(300);
				}
			}
		});
	return false;
}

function closelistbox()
{
	document.getElementById("awdlightbox").style.display="none";
	document.getElementById("awdlightbox-panel").style.display="none";
}
    function insertSmiley(smiley,txtid)
    {
		var TextArea = document.getElementById(txtid);
		var val = TextArea.value;
		var before = val.substring(0, TextArea.selectionStart);
		var after = val.substring(TextArea.selectionEnd, val.length);
		var smileyWithPadding = " " + smiley + " ";
		TextArea.value = before + smileyWithPadding + after;
    }
	function smilyshow(txtid)
	{
		var divid='#smilycontainer_'+txtid;
		jQuery(divid).slideToggle("slow");
	}


jQuery.extend({
	handleError: function( s, xhr, status, e ) {
		// If a local callback was specified, fire it
		if ( s.error )
			s.error( xhr, status, e );
		// If we have some XML response text (e.g. from an AJAX call) then log it in the console
		else if(xhr.responseText)
			console.log(xhr.responseText);
	}
});
