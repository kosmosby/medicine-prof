function awdsignout()
{
	document.awdlogoutfrm.submit();
}

function check(){
	
	var Itemid=document.getElementById("Itemid").value;
	var d = new Date();
	var time = d.getTime();
	var  animStyle = 'fade';
	var url='index.php?option=com_awdwall&task=getnotification&Itemid='+Itemid+"&timestamp="+time;
	//jQuery("#msg1").notificationmsg('hide');
	jQuery("#msg1").hide('fade');
   		 if (jQuery("#msg1").is(":hidden")){
			
			jQuery.get(url, function(data){//alert(data);
				//if(data!="0"){
				if(data.length>5 && data!=0){
					jQuery('embed').remove();
        			jQuery('body').append('<embed src="'+siteUrl+'components/com_awdwall/images/alarm.wav" autostart="true" hidden="true" loop="false">');
				 	jQuery("#modalbody").load(url);
					//jQuery('#msg1').notificationmsg({animation:animStyle});
					jQuery('#msg1').show('fade'); 
				}
			});
			
			
			
		}
		 window.setTimeout(function() {check();}, 9000);
	}
	


jQuery(document).ready(function(){

	jQuery("#search_user").autocomplete("index.php?option=com_awdwall&view=awdwall&task=auto&tmpl=component", { minChars:1, matchSubset:1, matchContains:1, cacheLength:10, onItemSelect:selectItem, formatItem:formatItem, selectOnly:1 });
	
	jQuery("#awdnoticealert").click(function () {
    	jQuery("#dropAlerts").toggle();
    });

		checktotalnotification();	
		
	//jQuery('#msg1').notificationmsg({period: 2500});	
	jQuery('#awd-mainarea').append('<div id="msg1"><div id="modal"><div class="modaltop"><div class="modaltitle">Notification Message</div><span id="closebutton" ><img src="' +siteUrl+'components/com_awdwall/images/purrClose.png" /></span></div><div class="modalbody"><span id="modalbody"></span></div><div class="notice-bottom"></div></div></div>');

	if(jQuery("#msg1").length>0)
	{
		//jQuery("#msg1").notificationmsg('hide');
		jQuery("#msg1").hide('fade');
	}
	
	jQuery("#closebutton").click(function () {
    	jQuery("#msg1").hide('fade');
    });
	
	if(jQuery("#msg1").length>0)
	{
			check();
	}
	// search


});







function navigateurl(id,url)

{

	// first send request to delete the notification.

	var urll='index.php?option=com_awdwall&task=delnotification&nid='+id;

//	alert(urll);

	jQuery.post(urll, function(data){

		window.location.href=url;

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
