<?php
/**
 * @version 3.0
 * @package JomWALL gallery
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
	//$config 		= &JComponentHelper::getParams('com_awdwall');
	$app = JFactory::getApplication('site');
	$config =  & $app->getParams('com_awdwall');
	$template 		= $config->get('temp', 'default');
	$jqueryversion 		= $config->get('jqueryversion', '1.7.2');
//$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js' );
	$display_jomwalllogin = $config->get('display_jomwalllogin', 0);
	if($display_jomwalllogin==1)
	{ 
		$mainlink=base64_encode(JRoute::_("index.php?option=com_awdwall&task=login&Itemid=".$_REQUEST['Itemid'],false)); 
	}
	else
	{
		$mainlink=base64_encode(JRoute::_("index.php?option=com_users&view=login&Itemid=".$_REQUEST['Itemid'],false)); 
	}
$document	= JFactory::getDocument();
?>
<link rel="stylesheet" href="<?php echo JURI::base();?>components/com_awdwall/css/jquery.autocomplete.css"  type="text/css" />
<link href="<?php echo JURI::base();?>components/com_awdwall/css/jquery-ui.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo JURI::base();?>components/com_awdwall/css/style_<?php echo $template; ?>.css"  type="text/css" />
<link href="<?php echo JURI::base();?>components/com_awdwall/css/notificationmsg.css" rel="stylesheet" type="text/css" />
<link href="<?php echo JURI::base();?>components/com_awdjomalbum/css/tab.css" rel="stylesheet" type="text/css" />
<link href="<?php echo JURI::base();?>components/com_awdjomalbum/css/colorbox.css" rel="stylesheet" type="text/css" />
<link href="<?php echo JURI::base();?>components/com_awdjomalbum/css/jquery.loadmask.css" rel="stylesheet" type="text/css" />
<link href="<?php echo JURI::base();?>components/com_awdwall/css/jquery.popover.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/<?php echo $jqueryversion; ?>/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdjomalbum/js/functions.js"></script>
<script type="text/javascript">jQuery.noConflict();var siteUrl = '<?php echo JURI::base();?>';</script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.autocomplete.js"></script>

<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdjomalbum/js/ui.notificationmsg.js"></script>

<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdjomalbum/js/jquery.colorbox.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdjomalbum/js/jquery.loadmask.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.popover.js"></script>
<input type="hidden" id="select_temp" value="<?php echo $template; ?>"/>

<input type="hidden" id="Itemid" value="<?php echo AwdwallHelperUser::getComItemId(); ?>"/>
<form method="post" action="index.php?option=com_users&task=user.logout" name="awdlogoutfrm" >
<input type="hidden" value="<?php echo $mainlink;?>" name="return" />
<input type="hidden" value="1" name="<?php echo JSession::getFormToken();?>" />
</form>

<style>

.successbox, .warningbox, .errormsgbox {

	margin: 0px 0px;

	padding:5px 10px 5px 25px;

	background-repeat: no-repeat;

	background-position: 0px center;

	width:230px;

}

.successbox {

	color: #ffffff;

	background-image:url(<?php echo JURI::base();?>components/com_awdwall/images/noticeinfo.png);

}

.nboxmain{
float:none; 
width:250px;
padding:3px;
clear:both;
padding-bottom:5px;
min-height:50px;
}
.nboximg{
float:left; 
width:50px;
margin-right:5px;
max-height:50px;
}
.nboxcontent{
color:#ffffff;
float:left; 
width:190px;
margin-right:3px;
}
.ui-widget-content{
border:0px !important;
}
</style>

<script type="text/javascript">
/*jQuery(document).ready(function() { 	

	jQuery().piroBox_ext({
	piro_speed : 700,
		bg_alpha : 0.5,
		piro_scroll : true // pirobox always positioned at the center of the page
	});
 });
*/ 
 jQuery(document).ready(function() { 
 	jQuery('#notifications-button').popover('#notifications-popover', {preventRight: true});	
jQuery(".awdiframe").colorbox({
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
 	
  });
</script>


    <div id="notifications-popover" class="popover">
        <header>
            <?php echo JText::_("Notification");?>
        </header>
        <section>
            <div class="content">
            </div>
        </section>
    </div>
