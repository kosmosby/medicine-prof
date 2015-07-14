<?php
/**
 * @version 3.0
 * @package Jomwall-Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
// include js and css
require_once(JPATH_COMPONENT . DS . 'js' . DS . 'include.php');
// get user object
$user = &JFactory::getUser();
$Itemid = AwdwallHelperUser::getComItemId();
//$Itemid = $_REQUEST['Itemid'];
$cbItemid = AwdwallHelperUser::getJsItemId();
$friendJsUrl = 'index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid;
$groupsUrl = JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);
$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
{
	$showalbumlink=true;
	$infolink="index.php?option=com_awdjomalbum&view=userinfo&wuid=".$user->id."&Itemid=".$Itemid;
	$albumlink="index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$user->id."&Itemid=".$Itemid;
}
else
{
	$showalbumlink=false;
	$infolink='';
	$albumlink="";
}

//$config 		= &JComponentHelper::getParams('com_awdwall');

$app = JFactory::getApplication('site');
 
$config =  & $app->getParams('com_awdwall');

$width 		= $config->get('width', 725);
$fb_id		 = $config->get('fb_id', '');
$fb_key		 = $config->get('fb_key', '');
$fb_secret	 = $config->get('fb_secret', '');
$termsconditiontxt	 = $config->get('termsconditiontxt', JText::_('COM_COMAWDWALL_TERMS_CONDITION_DESC'));
$showterms	 = $config->get('showterms', 1);
$display_reglink	 = $config->get('display_reglink', 1);
$display_forgotlink	 = $config->get('display_forgotlink', 1);
$display_cbfacebook	 = $config->get('display_cbfacebook', 1);

$awdloginurl = JRoute::_('index.php?option=com_awdwall&task=login&Itemid=' . $Itemid, false);
$return=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);
$awdreturnurl=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);
$return=base64_encode($return);
$return_decode = base64_decode($return);
// for cb facebook login button starts here **********************
	include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
	global $_CB_framework;
	$absolute_path		=	$_CB_framework->getCfg( 'absolute_path' );
	$cblogin_live_site	=	$_CB_framework->getCfg( 'live_site' );
	include_once( $absolute_path . "/administrator/components/com_comprofiler/plugin.class.php");
	global $_PLUGINS,$ueConfig;
    $module = JModuleHelper::getModule('cblogin');
    $moduleParams = new JRegistry();
    $moduleParams->loadString($module->params);
	$message_login 	= $moduleParams->get( 'login_message', 0 );
	$message_logout = $moduleParams->get( 'logout_message', 0 );
	$pretext 		= $moduleParams->get( 'pretext' );
	$posttext 		= $moduleParams->get( 'posttext' );
	$logoutpretext 	= $moduleParams->get( 'logoutpretext' );
	$logoutposttext = $moduleParams->get( 'logoutposttext' );
	$login 			= $moduleParams->get( 'login', $return );
	$logout 		= $moduleParams->get( 'logout', "index.php" );
	if ( $logout == '#' ) {
		$logout		= $return;
	}
	$name 			= $moduleParams->get( 'name', 0 );
	$greeting 		= $moduleParams->get( 'greeting', 1 );
	$class_sfx		= $moduleParams->get( 'moduleclass_sfx', "");
	$horizontal		= $moduleParams->get( 'horizontal', 0);
	$show_avatar	= $moduleParams->get( 'show_avatar', 0);
	$avatar_position = $moduleParams->get( 'avatar_position', "default");
	$text_show_profile = $moduleParams->get( 'text_show_profile', "");
	$text_edit_profile = $moduleParams->get( 'text_edit_profile', "");
	$pms_type		= $moduleParams->get( 'pms_type', 0);
	$show_pms		= $moduleParams->get( 'show_pms', 0);
	$remember_enabled = $moduleParams->get( 'remember_enabled', 1);
	$https_post		= $moduleParams->get( 'https_post', 0);
	$showPendingConnections = $moduleParams->get( 'show_connection_notifications', 0);
	$show_newaccount = $moduleParams->get( 'show_newaccount', 1 );
	$show_lostpass 	= $moduleParams->get( 'show_lostpass', 1 );
	$name_lenght 	= $moduleParams->get( 'name_lenght', "14" );
	$pass_lenght 	= $moduleParams->get( 'pass_lenght', "14" );
	$compact 		= $moduleParams->get( 'compact', 0 );
	$cb_plugins		= $moduleParams->get( 'cb_plugins', 0 );
	$show_username_pass_icons	=	$moduleParams->get( 'show_username_pass_icons', 0 );
	$show_buttons_icons			=	$moduleParams->get( 'show_buttons_icons', 0 );
	$show_remind_register_icons	=	$moduleParams->get( 'show_remind_register_icons', 0 );
		
	$_PLUGINS->loadPluginGroup('user');
	$pluginsResults	=	$_PLUGINS->trigger( 'onAfterLoginForm', array( $name_lenght, $pass_lenght, $horizontal, $class_sfx, &$moduleParams ) );
	if ( count( $pluginsResults ) > 0 ) {
		foreach ( $pluginsResults as $pR ) {
			if ( is_array( $pR ) ) {
				foreach ($pR as $pK => $pV ) {
					$pluginDisplays[$pK][]			=	$pV;
				}
			} elseif ( $pR != '' ) {
				$pluginDisplays['beforeButton'][]	=	$pR;
			}
		}
		
	}
	foreach ( $pluginDisplays as $pK => $pV ) {
		$divHtml				=	( $horizontal ? '<span' : '<div' ) . ' class="mod_login_plugin'.$class_sfx.' mod_login_plugin_' . $pK . '">';
		$sldivHtml				=	( $horizontal ? '</span>' : '</div>' );
		$pluginDisplays[$pK]	=	$divHtml . implode( $sldivHtml . $divHtml, $pV ) . $sldivHtml;
	}
	
	$registration_enabled	=	$_CB_framework->getCfg( 'allowUserRegistration' );
	if ( ! $registration_enabled ) {
		if ( isset($ueConfig['reg_admin_allowcbregistration']) && $ueConfig['reg_admin_allowcbregistration'] == '1' ) {
			$registration_enabled = true;
		}
	}
	
	
	
		$urlRegister			=	$_CB_framework->viewUrl( 'registers' );
		if ( $https_post ) {
			if ( ( substr($urlRegister, 0, 5) != "http:" ) && ( substr($urlRegister, 0, 6) != "https:" ) ) {
				$urlRegister = $cblogin_live_site."/".$urlRegister;
			}
			$urlRegister = str_replace("http://","https://",$urlRegister);
		}
	
		$urlLostPassword			=	$_CB_framework->viewUrl( 'lostpassword' );
		if ( $https_post /* && ! $isHttps */ ) {
			if ( ( substr($urlLostPassword, 0, 5) != "http:" ) && ( substr($urlLostPassword, 0, 6) != "https:" ) ) {
				$urlLostPassword = $cblogin_live_site."/".$urlLostPassword;
			}
			$urlLostPassword = str_replace("http://","https://",$urlLostPassword);
		}


// cb facebook login button end here *******************************
?>
<style type="text/css">
#awd-mainarea .wallheadingRight ul li a, #awd-mainarea .wallheadingRight ul li.separator {
 color:#<?php echo $this->color[1];
?>;
}
#awdloginwrapper a.awdsignup{
<?php if($template=='default') {?>
 color:#2CBBE2!important;
<?php }else{ ?>
 color:#<?php echo $this->color[2];?>!important;
 <?php } ?>
}

#awd-mainarea .profileLink a, #awd-mainarea .profileMenu .ppm a, #awd-mainarea #msg_content .rbroundboxleft .mid_content a, #awd-mainarea .commentinfo a, #awd-mainarea .user_place ul.profileMenu li a {
 color:#<?php echo $this->color[2];
?>;
}
#awd-mainarea #awd_message, #awd-mainarea .right_mid_content .walltowall li {
 color:#<?php echo $this->color[3];
?>!important;
}
#awd-mainarea .wall_date {
 color:#<?php echo $this->color[4];
?>;
}
#awd-mainarea .rbroundboxright, #awd-mainarea .fullboxtop, #awd-mainarea .rbroundboxleft_user, #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop {
 background-color:#<?php echo $this->color[5];
?>;
}
 <?php if($template!='default') {
?>  #awd-mainarea .mid_content_top {
 background-color:#<?php echo $this->color[5];
?>;
}
 <?php
}
else {
?>  #awd-mainarea .mid_content_top {
 background-color:#<?php echo $this->color[1];
?>;
}
#awd-mainarea .rbroundboxright {
 background-color:#<?php echo $this->color[1];
?>;
}
#awd-mainarea .whitebox, #awd-mainarea .blog_content, #awd-mainarea .about_me {
 background-color:#<?php echo $this->color[14];
?>;
}
 <?php
}
?>  #awd-mainarea .rbroundboxleft .mid_content a, #awd-mainarea .blog_groups, #awd-mainarea .blog_groups a, #awd-mainarea .blog_friend, #awd-mainarea .blog_friend a, #awd-mainarea a.authorlink {
 color:#<?php echo $this->color[6];
?>;
}
#msg_content .rbroundboxleft, #msg_content .awdfullbox {
 background-color:#<?php echo $this->color[7];
?>;
}
#awd-mainarea .walltowall li a, #msg_content .maincomment_noimg_right h3 a {
 color:#<?php echo $this->color[8];
?>;
}
#awd-mainarea ul.tabProfile li a {
 background-color:#<?php echo $this->color[9];
?>;
}
#awd-mainarea ul.tabProfile li a:hover, #awd-mainarea ul.tabProfile li.active a {
 background-color:#<?php echo $this->color[10];
?>;
}
#awd-mainarea ul.tabProfile li a {
 color:#<?php echo $this->color[11];
?>;
}
#awd-mainarea .whitebox, #awd-mainarea .blog_content, #awd-mainarea .about_me {
 background-color:#<?php echo $this->color[12];
?>;
}
 <?php if($template!='default') {
?>  #awd-mainarea .wallheading, #awd-mainarea .wallheadingRight {
 background-color:#<?php echo $this->color[13];
?>;
}
 #awd-mainarea .round, #awd-mainarea .search_user {
 background-color:#<?php echo $this->color[14];
?>;
}
 <?php
}
?> #awd-mainarea .notiItemsWrap {
background-color:#<?php echo $this->color[12];
?>;
color:#<?php echo $this->color[3];
?>;
}
#awd-mainarea .notiItemsWrapLast {
background-color:#<?php echo $this->color[12];
?>;
color:#<?php echo $this->color[3];
?>;
}
#awd-mainarea #dropAlerts .notiItem a {
color:#<?php echo $this->color[2];
?>!important;
}
#awd-mainarea #awdloginwrapper p.login input {
background: #<?php echo $this->color[8];?>!important;
border: 1px solid #<?php echo $this->color[2];?>!important;
	display:inline-block!important;
	float:none!important;
}
#awd-mainarea #awdloginwrapper p.login input:hover {
 background: #<?php echo $this->color[2];
?>!important;
}
#awdloginwrapper span.awdwelcome {
color:#<?php echo $this->color[8];
?> !important;
}
<?php if($template=='default') {?>
 #awd-mainarea #awdloginwrapper #awdloginfrm, #awd-mainarea #awdloginwrapper #awdreminderfrm, #awd-mainarea #awdloginwrapper #awdregisterfrm {
 background: #fff!important;
 border: 1px solid #fff!important;
}
<?php
}
?>
 .awdfrmdiv {
display: none;
}
.btl-error{
	color: #FF0000;
	border:1px dotted;
	padding:4px;
}

.btl-error{
	display:none;	
}
.btl-error-detail{
	display:none;
	float: right;
	color: #FF0000;
	margin-bottom:4px;
}
.btl-field,#register-link,.btl-error-detail,.btl-error,.btl-note{
	overflow:hidden;
}
#btl-register-in-process,#btl-login-in-process,#btl-forgot-in-process{
	display: none;
	background: url("<?php echo JURI::base();?>components/com_awdwall/images/loading.gif") no-repeat #000 50%;
	opacity: 0.4;
	width: 100%;
	height: 100%;
	position: absolute;
	z-index: 9999;
	top:-1px;
	left:-1px;
	padding-top:1px;
	padding-left:1px;
}

/* style panel when register success */
#btl-success{
	display: none;
	margin: 20px 0 30px 0;
	display: none;
	color:#90B203;
	border: 1px dotted #90B203;
	padding:5px;
}
#captcha-wrap{
<?php if($template=='default') {?>
	border:solid #<?php echo $this->color[5];?> 1px!important;
	background:#<?php echo $this->color[5];?> 1px!important;
<?php } else {?>
	border:solid #<?php echo $this->color[8];?> 1px!important;
	background:#<?php echo $this->color[8];?> 1px!important;
<?php } ?>

}
#awdloginwrapper .facebookconnect_button{
margin:0px!important;
padding:3px 0px !important;
}


</style>
<script type="text/javascript">


jQuery(document).ready(function() {

<?php if($showterms==1){?>
jQuery('.awdtermscondition').poshytip({
	className: 'tip-twitter',
	showTimeout: 4,
	showOn: 'onclick',
	alignTo: 'target',
	alignX: 'center',
	offsetX: 0,
	offsetY: 5
});
<?php } ?>

 // refresh captcha
 jQuery('img#captcha-refresh').click(function() {  
		change_captcha();
 });
 
 function change_captcha()
 {
	document.getElementById('captcha').src="index.php?option=com_awdwall&tmpl=component&task=ajaxcaptcha&rnd=" + Math.random();
 }

change_captcha();

jQuery(".awdanimateform").click(function() {
    var $toggled = jQuery(this).attr('href');

   jQuery($toggled).siblings(':visible').fadeOut(50);
   // jQuery($toggled).toggle("slide", {direction: 'up'}, 50);
   jQuery($toggled).fadeIn(2000);
    return false;
});

});

function ajaxregister()
{
	var token = jQuery('#awdregisterfrm .login input:last').attr("name");
	var value_token = jQuery('#awdregisterfrm .login input:last').val(); 
	var datasubmit= "task=ajaxregister&name="+jQuery("#fullname").val()
			+"&username="+jQuery("#rusername").val()
			+"&passwd1=" + jQuery("#rpassword").val()
			+"&passwd2=" + jQuery("#rcpassword").val()
			+"&email1=" + jQuery("#remailaddress").val()
			+"&captchacode=" + jQuery("#captchacode").val()
			+ "&"+token+"="+value_token;
			
			jQuery("#btl-registration-error").hide();
			jQuery("#btl-success").hide();	
	jQuery.ajax({
		   type: "POST",
		   beforeSend:function(){
			   jQuery("#btl-register-in-process").show();			   
		   },
		   url: 'index.php?option=com_awdwall&tmpl=component',
		   data: datasubmit,
		   success: function(html){				  
			   //if html contain "Registration failed" is register fail
			  jQuery("#btl-register-in-process").hide();
			 // alert(html);	
			  if(html.indexOf('$error$')!= -1){
				  jQuery("#btl-registration-error").html(html.replace('$error$',''));  
				  jQuery("#btl-registration-error").show();
			   }
			   else if(html.indexOf('$errorcaptcha$')!= -1)
			   {
				  jQuery("#btl-registration-error").html(html.replace('$errorcaptcha$',''));  
				  jQuery("#btl-registration-error").show();
				  document.getElementById('captcha').src="index.php?option=com_awdwall&tmpl=component&task=ajaxcaptcha&rnd=" + Math.random();
			   }
			   else{	
			   //alert(html);
			  	   jQuery("#awdregisterdiv").hide();			   
				   jQuery("#btl-success").html(html);	
				   jQuery("#btl-success").show();	
				  setTimeout(function() {window.location.reload();},5000);

			   }
		   },
		   error: function (XMLHttpRequest, textStatus, errorThrown) {
				alert(textStatus + ': <?php echo JText::_('COM_COMAWDWALL_LOGIN_AJAX_FAIL_TEXT');?>');
		   }
		});
			
			

}
function showLoginError(notice,reload){
	jQuery("#btl-login-in-process").hide();
	jQuery("#btl-login-error").html(notice);
	jQuery("#btl-login-error").show();
	if(reload){
		setTimeout(function() {window.location.reload();},5000);
	}
}

function checkfrm(frm_name)
{
		var flag =true;
	
	if(frm_name=='awdloginform')
	{
		if(awdloginform.username.value == ''){
			jQuery('#username').addClass('invalid');
			flag = false;
		}
		if(awdloginform.password.value == ''){
			jQuery('#password').addClass('invalid');
			flag = false;
		}
		
		if(flag)
		{
			var token = jQuery('#awdloginform .login input:last').attr("name");
			var value_token = jQuery('#awdloginform .login input:last').val(); 
			
			var datasubmit= "task=ajaxlogin&username="+jQuery('#username').val()
			+"&passwd=" + jQuery('#password').val()
			+ "&"+token+"="+value_token
			+"&return="+ jQuery("#awdreturn").val();
			
			if(jQuery("#loginkeeping").is(":checked")){
				datasubmit += '&remember=yes';
			}
			
			jQuery("#btl-login-error").hide();
			
			var msgloginfail='<?php echo JText::_('COM_COMAWDWALL_LOGIN_FAIL_MSG');?>';
			
			jQuery.ajax({
				   type: "POST",
				   beforeSend:function(){
					   jQuery("#btl-login-in-process").show();
					   jQuery("#btl-login-in-process").css('height',jQuery('#btl-content-login').outerHeight()+'px');
					   
				   },
				   url: 'index.php?option=com_awdwall&tmpl=component',
				   data: datasubmit,
				   success: function (html){
					  if(html == "1" || html == 1){
						 window.location.href='<?php echo $return_decode; ?>';
					   }else{
							   showLoginError(msgloginfail);
					   }
				   },
				   error: function (XMLHttpRequest, textStatus, errorThrown) {
						alert(textStatus + ': <?php echo JText::_('COM_COMAWDWALL_LOGIN_AJAX_FAIL_TEXT');?>');
				   }
				});			
			return false;
			// jQuery('#awdloginform').submit();
		}
		else
		{
			return false;
		}
	}
	
	
	
	if(frm_name=='awdregisterform')
	{
			jQuery('#fullname').removeClass('invalid');
			jQuery('#rusername').removeClass('invalid');
			jQuery('#remailaddress').removeClass('invalid');
			jQuery('#rpassword').removeClass('invalid');
			jQuery('#rcpassword').removeClass('invalid');
			jQuery('#captchacode').removeClass('invalid');

		
		if(awdregisterform.fullname.value == ''){
			jQuery('#fullname').addClass('invalid');
			flag = false;
		}
		if(awdregisterform.rusername.value == ''){
			jQuery('#rusername').addClass('invalid');
			flag = false;
		}
		if(awdregisterform.remailaddress.value == ''){
			jQuery('#remailaddress').addClass('invalid');
			flag = false;
		}
		else
		{
			if(!validEmail(awdregisterform.remailaddress.value))
			{
				jQuery('#remailaddress').addClass('invalid');
				flag = false;
			}
		}
		
		if(awdregisterform.rpassword.value == ''){
			jQuery('#rpassword').addClass('invalid');
			flag = false;
		}
		if(awdregisterform.rcpassword.value == ''){
			jQuery('#rcpassword').addClass('invalid');
			flag = false;
		}
		if(awdregisterform.rpassword.value!=awdregisterform.rcpassword.value)
		{
			jQuery('#rcpassword').addClass('invalid');
			jQuery('#rpassword').addClass('invalid');
			flag = false;
		}
		
        <?php if($showterms==1){?>
		if(jQuery("#termscondition").is(":checked")){
			flag = true;
		}
		else
		{
			alert('<?php echo JText::_('COM_COMAWDWALL_REGISTRATION_TERM__NOT_SELECTED_TEXT');?>');
			flag = false;
		}
		<?php } ?>
		
		if(flag)
		{ 	ajaxregister();
			
			}
		else
		{
			return false;
			}
	}
	
	
	if(frm_name=='awdforgotform')
	{
		if(awdforgotform.femailaddress.value == ''){
			jQuery('#femailaddress').addClass('invalid');
			flag = false;
		}
		else
		{
			if(!validEmail(awdforgotform.femailaddress.value))
			{
				jQuery('#femailaddress').addClass('invalid');
				flag = false;
			}
		}
		if(flag)
		{
			
			var token = jQuery('#awdforgotform .login input:last').attr("name");
			var value_token = jQuery('#awdforgotform .login input:last').val(); 
			
			var datasubmit= "task=ajaxforgot&email="+jQuery('#femailaddress').val()
			+ "&"+token+"="+value_token;
			jQuery("#btl-forgot-error").hide();
			jQuery("#btl-resetsuccess").hide();	
			var msgloginfail='<?php echo JText::_('COM_COMAWDWALL_LOGIN_FAIL_MSG');?>';
			
			jQuery.ajax({
				   type: "POST",
				   beforeSend:function(){
					   jQuery("#btl-forgot-in-process").show();
					   jQuery("#btl-forgot-in-process").css('height',jQuery('#btl-content-login').outerHeight()+'px');
					   
				   },
				   url: 'index.php?option=com_awdwall&tmpl=component',
				   data: datasubmit,
				   success: function (html, textstatus, xhrReq){
				   
					   //if html contain "Registration failed" is register fail
					  jQuery("#btl-forgot-in-process").hide();
					  if(html.indexOf('$error$')!= -1){
						  jQuery("#btl-forgot-error").html(html.replace('$error$',''));  
						  jQuery("#btl-forgot-error").show();
					   }else{	
					   //alert(html);
						   jQuery("#awdforgotdiv").hide();			   
						   jQuery("#btl-resetsuccess").html(html);	
						   jQuery("#btl-resetsuccess").show();	
						   setTimeout(function() {window.location.reload();},5000);
		
					   }
				   },
				   error: function (XMLHttpRequest, textStatus, errorThrown) {
						alert(textStatus + ': <?php echo JText::_('COM_COMAWDWALL_LOGIN_AJAX_FAIL_TEXT');?>');
				   }
				});			
			
			return false;
			
			}
		else
		{
			return false;
			}
	}

}
function validEmail(email){
    var atpos = email.indexOf("@");
    var dotpos = email.lastIndexOf(".");
    if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length){
        return false;
    }
    return true
}

</script>
<div  id="awd-mainarea" style="width:100%">
  <div class="wallheading">
    <div class="wallheadingRight">
      <ul>
        <li><a href="<?php echo $awdloginurl;?>" class="active"><?php echo JText::_('COM_COMAWDWALL_LOGIN');?></a></li>
      </ul>
    </div>
  </div>
  <div class="awdfullbox fullboxtop "> <span class="bl"></span>
    <div style="width:100%;padding:20px;">
      <div id="awdloginwrapper">
        <div id="awdloginfrm" class="awdfrmdiv" style="display:block;">
          <form  autocomplete="off" name="awdloginform" id="awdloginform" method="post">
            <p class="awdlogintext"><span class="awdwelcome"><?php echo JText::_('COM_COMAWDWALL_WELCOME');?></span>
		<?php if ($registration_enabled && $show_newaccount && $display_reglink) { ?>
		<?php echo JText::_('COM_COMAWDWALL_DO_NOT_HAVE_ACCOUNT');?> <a href="<?php echo $urlRegister;?>" class="awdsignup "><?php echo JText::_('COM_COMAWDWALL_SIGNUP');?>.</a> <?php echo JText::_('COM_COMAWDWALL_IT_EASY_AND_FREE');?><?php $awdshowreg=1;}else { ?><?php echo JText::_('COM_COMAWDWALL_SIGNIN_USING_ACCOUNT');?> <?php  $awdshowreg=0;}?>            </p>
            <p>&nbsp;</p>
            <?php if ($awdshowreg==1) { ?>
            <center>
              <p  class="awdlogintextgrey"><?php echo JText::_('COM_COMAWDWALL_SIGNIN_USING_ACCOUNT');?></p>
            </center>
            <p>&nbsp;</p>
            <?php } ?>
            <div id="btl-login-in-process"></div>
            <div class="btl-error" id="btl-login-error"></div>
            <p>
              <input id="username" name="username"  type="text" placeholder="<?php echo JText::_('USERNAME');?>" class="awdusername" autocomplete="off"/>
            </p>
            <p>
              <input id="password" name="password"  type="password" placeholder="<?php echo JText::_('COM_COMAWDWALL_LOGIN_PASSWORD');?>" class="awdpassword" autocomplete="off"/>
            </p>
            <p class="keeplogin">
              <input type="checkbox" name="loginkeeping" id="loginkeeping" value="yes" />
              <label for="loginkeeping"><?php echo JText::_('COM_COMAWDWALL_LOGIN_REMEMBER');?>.</label>
              <?php if( $display_forgotlink==1){ ?>
              <label style="float:right; text-align:right;"><a href="<?php echo $urlLostPassword;?>" class="awdsignup "><?php echo JText::_('COM_COMAWDWALL_LOGIN_FORGOT');?></a></label><?php } ?>
            </p>
            <p class="login ">
              <input type="button" value="<?php echo JText::_('COM_COMAWDWALL_LOGIN_BUTTON_TEXT');?>"  class="button" onclick="return checkfrm('awdloginform');"/>
					<input type="hidden" name="option" value="com_users" />
					<input type="hidden" name="task" value="user.login" /> 
					<input type="hidden" name="return" id="awdreturn"	value="<?php echo $return; ?>" />
					<input type="hidden" value="1" name="<?php echo JSession::getFormToken();?>" />
            </p>
            
          </form>
			<?php
			 if ($display_cbfacebook==1 ) {
                if ( isset( $pluginDisplays['afterButton'] ) ) {
                    echo $pluginDisplays['afterButton'];
                }
				}
            ?>
<?php /*?>	<?php if(!empty($fb_id) && !empty($fb_secret)){?>
            <p class="keeplogin" style="width:100%;"></p>
            <div id="fb-root"></div>
            <script src="http://connect.facebook.net/en_US/all.js"></script>
            <script>
                FB.init({ appId: '<?php echo $fb_id; ?>', status: true, cookie: true, xfbml: true });
                FB.Event.subscribe('auth.login', function(response) {		
                    window.location=siteUrl+"index.php?option=com_awdwall&tmpl=component&task=facebooklogin";
                });		
            </script>
            <fb:login-button perms="email,user_about_me,user_birthday" autologoutlink="true">Facebook login</fb:login-button>
        <?php } ?>
<?php */?>        
        </div>
        <div id="awdregisterfrm" class="awdfrmdiv">
          <form  action="" autocomplete="off" name="awdregisterform" id="awdregisterform" >
            <p class="awdlogintext"><span class="awdwelcome"><?php echo JText::_('COM_COMAWDWALL_SIGNUP_UP_IT_IS_EASY');?>.</span><?php echo JText::_('COM_COMAWDWALL_GO_BACK_TO');?><a href="#awdloginfrm" class="awdsignup awdanimateform"><?php echo JText::_('COM_COMAWDWALL_SIGNIN');?>.</a></p>
            <p class="keeplogin" style="width:100%;"></p>
            <div id="btl-success"></div>
            <div id="btl-register-in-process"></div>
            <div id="btl-registration-error" class="btl-error"></div>
            <div id="awdregisterdiv">
            <p>
              <input id="fullname" name="fullname"  type="text" placeholder="<?php echo JText::_('COM_COMAWDWALL_FULLNAME');?>" class="awdusername" autocomplete="off"/>
            </p>
            <p>
              <input id="rusername" name="rusername"  type="text" placeholder="<?php echo JText::_('USERNAME');?>" class="awdemail" autocomplete="off"/>
            </p>
            <p>
              <input id="remailaddress" name="remailaddress"  type="text" placeholder="<?php echo JText::_('COM_COMAWDWALL_SIGNUP_EMAIL_ADDRESS');?>" class="awdemail" autocomplete="off"/>
            </p>
            <p>
              <input id="rpassword" name="rpassword"  type="password" placeholder="<?php echo JText::_('COM_COMAWDWALL_SIGNUP_CHOOSE_PASSWORD');?>" class="awdpassword" autocomplete="off"/>
            </p>
            <p>
              <input id="rcpassword" name="rcpassword"  type="password" placeholder="<?php echo JText::_('COM_COMAWDWALL_SIGNUP_CONFIRM_PASSWORD');?>" class="awdpassword" autocomplete="off"/>
            </p>
           <?php if($showterms==1){?>
            <p class="keeplogin">
              <input type="checkbox" name="termscondition" id="termscondition" value="1" />
              <label for="loginkeeping"><?php echo JText::sprintf ( 'COM_COMAWDWALL_TERMS_CONDITION', '#',$termsconditiontxt );?></label>
            </p>
           <?php } ?>
            <div id="captcha-wrap">
                <div class="captcha-box">
                    <img src="" alt="" id="captcha" />
                </div>
                <div class="text-box">
                    <label><?php echo JText::_('COM_COMAWDWALL_CAPTCHA_TEXT');?></label>
                    <input name="captchacode" type="text" id="captchacode">
                </div>
                <div class="captcha-action">
                    <img src="<?php echo JURI::base();?>components/com_awdwall/images/buttonreload.png"  alt="" id="captcha-refresh" />
                </div>
            </div>
            <p class="login ">
              <input type="button" value="Sign Up"  class="button" onclick="return checkfrm('awdregisterform');" />
              <input type="hidden" value="1" name="<?php echo JSession::getFormToken();?>" />
            </p>
            </div>
          </form>
          
<?php /*?>          
		<?php if(!empty($fb_id) && !empty($fb_secret)){?>
            <p class="keeplogin" style="width:100%;"></p>
            <div id="fb-root"></div>
            <script src="http://connect.facebook.net/en_US/all.js"></script>
            <script>
                FB.init({ appId: '<?php echo $fb_id; ?>', status: true, cookie: true, xfbml: true });
                FB.Event.subscribe('auth.login', function(response) {		
                    window.location=siteUrl+"index.php?option=com_awdwall&tmpl=component&task=facebooklogin";
                });		
            </script>
            <fb:login-button perms="email,user_about_me,user_birthday" autologoutlink="true">Facebook login</fb:login-button>
        <?php } ?>
        
<?php */?> 

       </div>
        <div id="awdreminderfrm" class="awdfrmdiv">
          <form  action="" autocomplete="off" name="awdforgotform" id="awdforgotform" >
            <p class="awdlogintext"><span class="awdwelcome"><?php echo JText::_('COM_COMAWDWALL_LOGIN_FORGOT');?></span><?php echo JText::_('COM_COMAWDWALL_GO_BACK_TO');?> <a  href="#awdloginfrm" class="awdsignup awdanimateform"><?php echo JText::_('COM_COMAWDWALL_SIGNIN');?>.</a></p>
            <p class="keeplogin" style="width:100%;"></p>
            <center>
              <p  style="font-size:11px; font-weight:bold;"><?php echo JText::_('COM_COMAWDWALL_FORGOT_DESCRIPTION');?></p>
            </center>
            <p>&nbsp;</p>
            <div id="btl-resetsuccess"></div>
            <div id="btl-forgot-in-process"></div>
            <div id="btl-forgot-error" class="btl-error"></div>
            <p>&nbsp;</p>
            <div id="awdforgotdiv">
            <p>
              <input id="femailaddress" name="femailaddress"  type="text" placeholder="<?php echo JText::_('COM_COMAWDWALL_SIGNUP_EMAIL_ADDRESS');?>" class="awdemail" autocomplete="off"/>
            </p>
            <p class="login ">
              <input type="button" value="<?php echo JText::_('COM_COMAWDWALL_FORGOT_BUTTON_TEXT');?>"  class="button" onclick="return checkfrm('awdforgotform');"/>
              <input type="hidden" value="1" name="<?php echo JSession::getFormToken();?>" />
            </p>
            </div>
          </form>
        </div>
        
      </div>
    </div>
  </div>
</div>
		<input type="hidden" name="wall_last_time" id="wall_last_time" value="<?php echo time();?>" />
		<input type="hidden" name="layout" id="layout" value="login" />
		<input type="hidden" name="posted_wid" id="posted_wid" value="" />
