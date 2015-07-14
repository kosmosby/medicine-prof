<?php
/**
 * @version 2.5
 * @package JomWALL CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

	$config 		= &JComponentHelper::getParams('com_awdwall');
	$template 		= $config->get('temp', 'default');
	$jqueryversion 		= $config->get('jqueryversion', '1.7.2');
	$avatarintergration 	= $config->get('avatarintergration', '0');
	
	$k2comment 		= $config->get('k2comment', '0');
	$k2article 		= $config->get('k2article', '0');
	
	$easyblogcomment 		= $config->get('easyblogcomment', '0');
	$easyblogpost 			= $config->get('easyblogpost', '0');
	$alphauserpointactivity = $config->get('alphauserpointactivity', '0');
	$sobiproactivity 		= $config->get('sobiproactivity', '0');
	$jeventactvity 			= $config->get('jeventactvity', '0');
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

<style>
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/jquery-ui.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/ui.dialog.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/ui.theme.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/prettyPhoto.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/jquery.autocomplete.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/notificationmsg.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/privacy.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/loadjomwall.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/tip-twitter.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/colorbox.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/loginbox.css');
/*.ui-widget-content{
border:0px !important;
visibility:hidden;
}
*/</style>
<script type="text/javascript">jQuery.noConflict();var siteUrl = '<?php echo JURI::base();?>';</script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.poshytip.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/autogrow.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.prettyPhoto.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.expander.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.colorbox.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/privacy.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/functionscb.js"></script>

	<link rel="stylesheet" href="<?php echo JURI::base();?>components/com_awdwall/css/style_<?php echo $template; ?>.css"  type="text/css" />
<input type="hidden" id="select_temp" value="<?php echo $template; ?>"/>
<input type="hidden" id="Itemid" value="<?php echo $_REQUEST['Itemid']; ?>"/>

<form method="post" action="index.php?option=com_users&task=user.logout" name="awdlogoutfrm" >
<input type="hidden" value="<?php echo $mainlink;?>" name="return" />
<input type="hidden" value="1" name="<?php echo JSession::getFormToken();?>" />
</form>
<script language="javascript" type="text/javascript">

function awdsignout()
{
	document.awdlogoutfrm.submit();
}

jQuery.noConflict();  
jQuery(document).ready(function() { 
if (jQuery(".postButton_small")[0]){
	jQuery('.postButton_small').poshytip({
		className: 'tip-twitter',
		showTimeout: 4,
		showOn: 'none',
		alignTo: 'target',
		alignX: 'center',
		offsetX: 0,
		offsetY: 5
	});
}
  jQuery('span.awdmessagetxt').expander({
	slicePoint: 150,
	widow: 2,
	expandSpeed: 0,
	userCollapseText: '<?php echo JText::_('read less');?>',
	expandText:'<?php echo JText::_('read more');?>',
  });
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
