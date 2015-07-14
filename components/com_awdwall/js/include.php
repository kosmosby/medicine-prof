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
	$jqueryversion = $config->get('jqueryversion', '1.8.2');
	$includejquery = $config->get('includejquery', '0');
	if($includejquery==1)
	{
	$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/'.$jqueryversion.'/jquery.min.js' );
	}

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
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/jplist.css');
<?php  if($template=='default') { ?>
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/dcdrilldown.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/blue.css');
	@import url('<?php echo JURI::base();?>components/com_awdwall/css/jquery.popover.css');
<?php } ?>
</style>
<script type="text/javascript">jQuery.noConflict();var siteUrl = '<?php echo JURI::base();?>';</script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.poshytip.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/autogrow.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.prettyPhoto.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/ui.notificationmsg.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.expander.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.colorbox.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/privacy.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jplist.min.js"></script>
<?php  if($template=='default') { ?>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.cookie.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.dcdrilldown.1.2.js"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.popover.js"></script>
<?php } ?>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/functions.js"></script>

	<link rel="stylesheet" href="<?php echo JURI::base();?>components/com_awdwall/css/style_<?php echo $template; ?>.css"  type="text/css" />
<input type="hidden" id="select_temp" value="<?php echo $template; ?>"/>
<input type="hidden" id="Itemid" value="<?php echo $_REQUEST['Itemid']; ?>"/>

<form method="post" action="index.php?option=com_users&task=user.logout" name="awdlogoutfrm" >
<input type="hidden" value="<?php echo $mainlink;?>" name="return" />
<input type="hidden" value="1" name="<?php echo JSession::getFormToken();?>" />
</form>

<style type="text/css">
	#awd-mainarea .successbox,#awd-mainarea  .warningbox,#awd-mainarea .errormsgbox {
	margin: 0px 0px;
	padding:5px 10px 5px 25px;
	background-repeat: no-repeat;
	background-position: 0px center;
	width:230px;
}
#awd-mainarea .successbox {
	color: #ffffff;
	background-image:url(<?php echo JURI::base();?>components/com_awdwall/images/noticeinfo.png);
}

#awd-mainarea .nboxmain{
float:none; 
width:250px;
padding:3px;
clear:both;
padding-bottom:5px;
min-height:50px;
}
#awd-mainarea .nboximg{
float:left; 
width:50px;
margin-right:5px;
max-height:50px;
}
#awd-mainarea .nboxcontent{
color:#ffffff;
float:left; 
width:190px;
margin-right:3px;
}
#awd-mainarea .ac_results{
	text-align:left!important;
}
#awd-mainarea .post_type_icon
{ margin-left:-20px; margin-top:-15px; position:relative!important; display:inline-block; height:15px; width:15px;}
@media screen and (-webkit-min-device-pixel-ratio:0) {
#awd-mainarea textarea.round {
		/*width:99% !important;*/
		}
	.postButton_small{
	/*margin-right:3px!important;*/
	}
}

@-moz-document url-prefix() {

   #awd-mainarea .round, x:-moz-any-link, x:default {
       width:98% !important;
    }
	#awd-mainarea .postButton_small, x:-moz-any-link, x:default {
	margin-right:15px!important;
	}
}
#awd-mainarea  .round{
width:98%!important;
}
/*.ui-widget-content{
border:0px !important;
visibility:hidden;
}
*/</style>
<!--[if IE ]>
	<link rel="stylesheet" href="<?php echo JURI::base();?>components/com_awdwall/css/ieonly.css"  type="text/css" />
<![endif]-->

<script language="javascript" type="text/javascript">

function awdsignout()
{
	document.awdlogoutfrm.submit();
}

jQuery.noConflict();  
jQuery(document).ready(function() { 
<?php  if($template=='default') { ?>
   jQuery("#btl-login-in-process").show();
   jQuery("#btl-login-in-process").css('height',jQuery('#drilldown-4').outerHeight()+'px');
   
	jQuery('#drilldown-4').dcDrilldown({
			speed       	: 'fast',
			saveState		: false,
			showCount		: false,
			defaultText		: 'back',
			linkType		: 'backlink'
	});
	jQuery("#btl-login-in-process").hide();
	jQuery('#drilldown-4').css({opacity: 1.0, visibility: "visible"}).animate({opacity: 100}, 800);
	jQuery('#notifications-button').popover('#notifications-popover', {preventRight: true});
<?php } ?>	
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
  <?php  if($_REQUEST['task']!='login') { // if not login page ?>

<?php  if($avatarintergration==1) { // k2 ?>
	
	jQuery(document).ready(function(){
	jQuery('img.awdpostavatar').each(function(){
		jQuery(this).load(function(){
			var maxWidth = jQuery(this).width(); // Max width for the image
			var maxHeight = jQuery(this).height();   // Max height for the image
			jQuery(this).css("width", "auto").css("height", "auto"); // Remove existing CSS
			jQuery(this).removeAttr("width").removeAttr("height"); // Remove HTML attributes
			var width = jQuery(this).width();    // Current image width
			var height = jQuery(this).height();  // Current image height
	
			if(width > height) {
				// Check if the current width is larger than the max
				if(width > maxWidth){
					var ratio = maxWidth / width;   // get ratio for scaling image
					jQuery(this).css("width", maxWidth); // Set new width
					jQuery(this).css("height", height * ratio);  // Scale height based on ratio
					height = height * ratio;    // Reset height to match scaled image
				}
			} else {
				// Check if current height is larger than max
				if(height > maxHeight){
					var ratio = maxHeight / height; // get ratio for scaling image
					jQuery(this).css("height", maxHeight);   // Set new height
					jQuery(this).css("width", width * ratio);    // Scale width based on ratio
					width = width * ratio;  // Reset width to match scaled image
				}
			}
		});
	});
	});
	<?php }  ?>
	<?php if($k2comment==1 || $k2article==1){?>
		function getk2feedtowall()
		{
				jQuery.noConflict();
				var url='<?php echo JURI::base().'index.php?option=com_awdwall&task=getk2feedtowall&tmpl=component';?>';
				jQuery.post(url);
				var t = setTimeout('getk2feedtowall()', 15000);
		}
		getk2feedtowall();
	<?php }  ?>

	<?php if($easyblogcomment==1 || $easyblogpost==1){?>
		function geteasyfeedtowall()
		{
				jQuery.noConflict();
				var url='<?php echo JURI::base().'index.php?option=com_awdwall&task=geteasyfeedtowall&tmpl=component';?>';
				jQuery.post(url);
				var t = setTimeout('geteasyfeedtowall()', 15000);
		}
		geteasyfeedtowall();
	<?php }  ?>
	<?php if($alphauserpointactivity==1){?>
		function alphauserpointactivity()
		{
				jQuery.noConflict();
				var url='<?php echo JURI::base().'index.php?option=com_awdwall&task=alphauserpointactivity&tmpl=component';?>';
				jQuery.post(url);
				var t = setTimeout('alphauserpointactivity()', 15000);
		}
		alphauserpointactivity();
	<?php }  ?>
	<?php if($sobiproactivity==1){?>
		function sobiproactivity()
		{
				jQuery.noConflict();
				var url='<?php echo JURI::base().'index.php?option=com_awdwall&task=sobiproactivity&tmpl=component';?>';
				jQuery.post(url);
				var t = setTimeout('sobiproactivity()', 15000);
		}
		sobiproactivity();
	<?php }  ?>
	<?php if($jeventactvity==1){?>
		function jeventactvity()
		{
				jQuery.noConflict();
				var url='<?php echo JURI::base().'index.php?option=com_awdwall&task=jeventactvity&tmpl=component';?>';
				jQuery.post(url);
				var t = setTimeout('jeventactvity()', 15000);
		}
		jeventactvity();
	<?php }  ?>
<?php }  ?>
</script>
<?php  if($template=='default') { ?>
    <div id="notifications-popover" class="popover">
        <header>
            <?php echo JText::_("Notification");?>
        </header>
        <section>
            <div class="content">
            </div>
        </section>
    </div>
<?php }  ?>