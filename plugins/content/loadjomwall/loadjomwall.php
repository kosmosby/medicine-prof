<?php

// no direct access
defined('_JEXEC') or die;
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
error_reporting(0);
jimport('joomla.plugin.plugin');
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'helpers' . DS . 'user.php');
$lang =& JFactory::getLanguage();
$extension = 'com_awdwall';
$base_dir = JPATH_SITE;
$language_tag = 'en-GB';
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);
class plgContentLoadJomwall extends JPlugin
{
	
	function plgContentLoadJomwall( &$subject, $params ){
		parent::__construct( $subject, $params );
	}
	
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
	
	
		//plgContentLoadJomwall($context, &$article, &$params, $page = 0);
	//print_r($params);
	$db =& JFactory::getDBO();
	$dbcheck=$this->jomwall_checkDatabase();
	// simple performance check to determine whether bot should process further
	if ( JString::strpos( $article->text, 'loadjomwall' ) === false ) {
		return true;
	}

	// Get plugin info
	$plugin =& JPluginHelper::getPlugin('content', 'plgContentLoadJomwall');
 	// expression to search for
 	$regex = '/{loadjomwall}/i';
	// check whether plugin has been unpublished
	if ( !$this->params->def( 'enabled', 1 ) ) {
		$article->text = preg_replace( $regex, '', $article->text );
		return true;
	}

 	// find all instances of plugin and put in $matches
	preg_match_all( $regex, $article->text, $matches );
//print_r($matches);
	// Number of plugins
 	$count = count( $matches[0] );

 	// plugin only processes if there are any instances of the plugin in the text
 	if ( $count ) {
 		$this->plgContentProcessJomwall( $article, $matches, $count, $regex );
	}

	}


//public function plgContentLoadJomwall($context,  &$row, &$params, $page=0 )
//{
//	$db =& JFactory::getDBO();
//	$dbcheck=$this->jomwall_checkDatabase();
//	// simple performance check to determine whether bot should process further
//	if ( JString::strpos( $row->text, 'loadjomwall' ) === false ) {
//		return true;
//	}
//
//	// Get plugin info
//	$plugin =& JPluginHelper::getPlugin('content', 'plgContentLoadJomwall');
//
// 	// expression to search for
// 	$regex = '/{loadjomwall}/i';
//
// 	$pluginParams = new JParameter( $plugin->params );
//
//	// check whether plugin has been unpublished
//	if ( !$pluginParams->get( 'enabled', 1 ) ) {
//		$row->text = preg_replace( $regex, '', $row->text );
//		return true;
//	}
//
// 	// find all instances of plugin and put in $matches
//	preg_match_all( $regex, $row->text, $matches );
////print_r($matches);
//	// Number of plugins
// 	$count = count( $matches[0] );
//
// 	// plugin only processes if there are any instances of the plugin in the text
// 	if ( $count ) {
// 		$this->plgContentProcessJomwall( $row, $matches, $count, $regex );
//	}
//}


public function plgContentProcessJomwall ( $row, $matches, $count, $regex)
{
 	
	
	for ( $i=0; $i < $count; $i++ )
	{
 		$load = str_replace( 'loadjomwall', '', $matches[0][$i] );
 		$load = str_replace( '{', '', $load );
 		$load = str_replace( '}', '', $load );
 		$load = trim( $load );

		$jomwall	= $this->plgContentLoadJomwallContent( $row->id );
		$row->text 	= str_replace($matches[0][$i], $jomwall, $row->text );
 	}

  	// removes tags without matching module positions
	$row->text = preg_replace( $regex, '', $row->text );
}


public function jomwall_checkDatabase() {
	$db =& JFactory::getDBO();

	$query = 'CREATE TABLE IF NOT EXISTS `#__awd_wall_content_comments` ( '
		. ' 	`id` int(10) unsigned NOT NULL auto_increment, '
		. ' 	`content_id` int(10) unsigned NOT NULL, '
		. ' 	`user_id` int(10) unsigned NOT NULL, '
		. ' 	`comment` text NOT NULL, '
		. ' 	`submitted` datetime NOT NULL, '
		. '		PRIMARY KEY  (`id`) '
		. ' )';
	$db->setQuery($query);	
	$db->query();
	
	$query = 'CREATE TABLE IF NOT EXISTS `#__awd_wall_content_comment_like` ( '
		. ' 	`id` int(10) unsigned NOT NULL auto_increment, '
		. ' 	`userid` int(10) unsigned NOT NULL, '
		. ' 	`commentid` int(10) unsigned NOT NULL, '
		. '		PRIMARY KEY  (`id`) '
		. ' )';
	$db->setQuery($query);	
	 $db->query();
}
	
public function plgContentLoadJomwallContent( $id)
{

	$ItemId=$this->getComItemId();
	
	$view 			= JRequest::getCmd('view');
	$id 			= JRequest::getInt('id');
	$itemid 		= JRequest::getInt('Itemid');
	if(!$itemid) $itemid = 999999;
	if($view=='article')
	{
	
	$db =& JFactory::getDBO();
	$user =& JFactory::getUser();

		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');



$query = 'SELECT *' .
' FROM #__awd_wall_content_comments' .
' WHERE content_id = '.(int) $id.' order by id desc';
$db->setQuery($query);
$commentrows = $db->loadObjectList();
jimport( 'joomla.plugin.helper' );
$plg = JPluginHelper::getPlugin('content', 'loadjomwall');
//$pluginParams    = new JParameter( $plg->params );

$showavatar=$this->params->def('showavatar', '1');
$wallversion=$this->params->def('wallversion', '1');
$nocomment=$this->params->def('nocomment', '10');
$moderator_users=$this->params->def('moderator_users', '');
$moderator_users=explode(',',$moderator_users);

$avatar=$this->getAvatar($user->id,$wallversion);
$avatar51=$this->getAvatar51($user->id,$wallversion);
ob_start();	
$document	= JFactory::getDocument();

//$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js' );
$document->addStyleSheet(JURI::base(). 'components/com_awdwall/css/style_'.$template.'.css');
$document->addStyleSheet(JURI::base() . 'plugins/content/loadjomwall/loadjomwall/css/loadjomwall.css');

?>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function(){

//		jQuery("a#show-panel").click(function(){
//			jQuery("#awdlightbox, #awdlightbox-panel").fadeIn(300);
//		})

	jQuery("#submit_wall").submit(function() 
	{
		
		var message_wall = jQuery('#message_wall').attr('value');
		if(message_wall == ''){
			jQuery('#message_wall').addClass('invalid');
			return false;
		}
		
			dataString = jQuery("#submit_wall").serialize();
			jQuery.ajax(
			{
				type: "POST",
				url: "<?php echo JURI::base();?>plugins/content/loadjomwall/loadjomwall_ajax.php",
				data:dataString,
				//dataType: "json",
				success: function(data)
				{
					//alert(data.message);
					var string=data.split("^") ;
					var comment=string[0];
					jQuery(".main-holder").prepend(comment);
					var commentid=string[1];
					var divcomment='#'+commentid;
					jQuery(divcomment).hide();
					jQuery(divcomment).fadeIn();
				}
			});
			jQuery('#message_wall').removeClass('invalid');
			//jQuery('#message_wall').value='';
			document.getElementById("message_wall").value = '';
		return false;
	});
	
		jQuery("a#close-panel").click(function(){
			jQuery("#awdlightbox, #awdlightbox-panel").fadeOut(300);
		})
		getnewmessage();
});

function getnewmessage()
{
		var dataString='task=getnewmessage&id='+<?php echo $id;?>;
		
			jQuery.ajax(
			{
				type: "POST",
				url: "<?php echo JURI::base();?>plugins/content/loadjomwall/loadjomwall_ajax.php",
				data:dataString,
				success: function(data)
				{
					//alert(data.message);
					jQuery(data).hide().prependTo(".main-holder").fadeIn("slow");
							
				}
			});

//var data='';
//data='<div class="testdiv"></div>';
//jQuery(data).hide().prependTo(".main-holder").fadeIn("slow");

 var t = setTimeout('getnewmessage()', 15000);
}

function deletecomment(id)
{
		var dataString='task=del&id='+id;
			jQuery.ajax(
			{
				type: "POST",
				url: "<?php echo JURI::base();?>plugins/content/loadjomwall/loadjomwall_ajax.php",
				data:dataString,
				dataType: "json",
				success: function(data)
				{
					//alert(data.message);
				}
			});
		
	var divcomment='#'+id;
	jQuery(divcomment).remove();
	return false;

}
function showallcomment()
{
	jQuery('#viewmore').hide();
	jQuery('#moreDiv').fadeIn();
}
function showallcommentlike(id)
{
	var Itemid=document.getElementById("Itemid").value;
	var dataString='task=getlike&id='+id+'&Itemid='+Itemid;
	//alert(dataString);
//	var divloading='#loadingc'+id;
//	jQuery(divloading).css("visibility","visible");
//	var divcomment='#awdlightcontentbox';
//	jQuery(divcomment).html('<center><img src="plugins/content/loadjomwall/images/loading.gif" width="250" /></center>');
//	jQuery("#awdlightbox, #awdlightbox-panel").fadeIn(300);
		jQuery.ajax(
		{
			type: "POST",
			url: "<?php echo JURI::base();?>plugins/content/loadjomwall/loadjomwall_ajax.php",
			data:dataString,
			dataType: "json",
			success: function(data)
			{
				//alert(data.message);
				if(data)
				{
					var divcomment='#awdlightcontentbox';
					jQuery(divcomment).html(data.message);
					jQuery("#awdlightbox, #awdlightbox-panel").fadeIn(300);
					//jQuery(divloading).css("visibility","hidden");
				}

			}
		});
	
	return false;
}

function likecomment(id)
{
	var dataString='task=like&id='+id;
		jQuery.ajax(
		{
			type: "POST",
			url: "<?php echo JURI::base();?>plugins/content/loadjomwall/loadjomwall_ajax.php",
			data:dataString,
			dataType: "json",
			success: function(data)
			{
				//alert(data.message);
				var divcomment='#commentlike'+id;
				jQuery(divcomment).html(data.message);
			}
		});
	
	return false;

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

</script>
<?php 
$path=JURI::base(); 
$temp='plugins/content/loadjomwall/';
$path=str_replace($temp,'',$path);
?>
<div id="awd-mainarea" style="padding:0; margin:0; background:transparent; width:100%;">
<form id="submit_wall" />
<div id="post-comment">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="left"  valign="top" width="70"><?php if($user->id){?>
<img src="<?php echo $avatar51;?>" alt="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>" width="50" height="50">
<?php }else{?>
<img src="<?php echo $path;?>plugins/content/loadjomwall/loadjomwall/images/men.gif"  width="50"  alt="" title="">
<?php }?>
</td>
    <td  align="left" valign="top">
    <div style="position:relative; width:98%;">
<?php echo AwdwallHelperUser::getSmileyicons("message_wall");?>

            <textarea  name="message_wall" id="message_wall" class="awdround" ></textarea>  
<img src="<?php echo JURI::base();?>components/com_awdwall/images/smicon.png" alt="Insert emotions" title="Insert emotions" onclick="smilyshow('message_wall')" style="cursor: pointer; clear:both; margin-top: -35px;position: absolute;right: 3%; z-index: 1; display:block;" />
  </div>   
    
    </td>
  </tr>
</table>



        



<input type="hidden" id="contentid" name="contentid" value="<?php echo $id;?>" />
<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $ItemId;?>" />

<input type="hidden" id="task" name="task" value="add" />
<?php if($user->id){?>
<p>
<input id="btncomment" value="<?php echo JText::_('Comment')?>" type="submit" class="postButton_small">
</p>
<?php 
}
else
{ 
 echo "<br>";
 echo JText::_('You need login to comment.');
} 
?>
</div>
</form>

<div class="main-holder">
	
	<?php 
	$admin_groupid=array(7,8);
//	echo '<pre>';
//	print_r($user);
//		echo '</pre>';
//		
		$can_delete='';
		if($user->id)
		{
			$query="SELECT group_id FROM #__user_usergroup_map where user_id=".$user->id;
			$db->setQuery($query);
			$user_groupidList= $db->loadObjectList();
			$can_delete='';
			foreach ($user_groupidList as $ugroupid)
			{
				if(in_array($ugroupid->group_id,$admin_groupid)){
				$can_delete=1;
				}
			}
		}

	$i=1;
	if(count($commentrows))
	{
	foreach ($commentrows as $commentrow)
	{
		
	?>
		
<div class="loadjomwallouter" id="<?php echo $commentrow->id;?>"> 
	<?php if($showavatar){
		$avatar=$this->getAvatar($commentrow->user_id,$wallversion);
		$commentuser =& JFactory::getUser($commentrow->user_id);
		$profilelink='index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$commentrow->user_id.'&Itemid='.$ItemId;
		
	$startd=explode(" ",$commentrow->submitted);
	$submittedtime=date('l,M j',strtotime($startd[0]));
	$commentdate=strtotime($commentrow->submitted);
	$commentuserusername=AwdwallHelperUser::getDisplayName($commentrow->user_id);
	?>
	  <div class="loadjomwallinner" id="avatar">
	 <a href="<?php echo $profilelink;?>" alt="<?php echo $commentuserusername;?>" title="<?php echo $commentuserusername;?>"> <img src="<?php echo $avatar;?>"  border="0" alt="<?php echo $commentuserusername;?>" title="<?php echo $commentuserusername;?>" width="32" height="32"> </a>
	  </div>
	  <?php } ?>
					  
					  <div class="loadjomwallinner" id="comment-holder">
						<div id="text"><a href="<?php echo $profilelink;?>" ><?php echo $commentuserusername;?></a> <?php echo AwdwallHelperUser::showSmileyicons($commentrow->comment);?> </div>
						<div class="ago"><a href="javascript::void(0);" onclick="return showallcommentlike(<?php echo $commentrow->id;?>);" > <span id="commentlike<?php echo $commentrow->id;?>" class="show-more"><?php echo $this->countlike($commentrow->id);?></span></a><?php if($user->id){?><a href="javascript::void(0);" onclick="return likecomment(<?php echo $commentrow->id;?>)"><?php echo JText::_('Like');?></a><?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo  AwdwallHelperUser::getDisplayTime($commentdate);// echo $submittedtime.' '.JText::_('at').' '.$startd[1].$startd[2];?></div>
					  </div>
					  <?php 
					  if($user->id)
					  {
					 
					  if($user->id==$commentrow->user_id || $can_delete==1 || ( in_array($user->id,$moderator_users)) ){?>
					  <a href="javascript::void(0)" onclick="return deletecomment(<?php echo $commentrow->id;?>)" class="loadjomwalldelete"></a>
					  <?php } } ?>
					  <br style="clear: both;">
</div>		
		
		
	<?php
		
		if( ($i==$nocomment) && ($flag==''))
		{
			echo '<div style="display:none" id="moreDiv">';
			$flag=1;
		}
	$i++;
	}
		
	}

	if($flag==1)
	{
		echo '</div>';
	?>
	<div id="viewmore" style="text-align:center; "><a href="javascript::void(0);" onclick="return showallcomment()"><?php echo JText::_('View All');?></a></div>
	<?php
	}
	?>
</div>
</div>
<div style="display: none;" id="awdlightbox-panel">
<h2><?php echo JText::_('People Like');?></h2>
<div id="awdlightcontentbox"></div>
<p align="center;" style="clear:left; text-align:center;">
	<a id="close-panel" href="javascript::void(0)"><?php echo JText::_('Close');?></a>
</p>
</div>
<div style="display: none;" id="awdlightbox"></div>
<?php
  
		$contents  = ob_get_contents();
		ob_end_clean();  
	}      
		return $contents;	
}


public function countlike($commentid)
{
	$db =& JFactory::getDBO();
	$query 	= "SELECT count(*) as totalcount FROM #__awd_wall_content_comment_like where commentid = " . (int)$commentid;
	$db->setQuery($query);
	$totalcount = $db->loadResult();
	
	return $totalcount;
}

public function getAvatar($userId,$wallversion)
{	
		$db =& JFactory::getDBO();
		$config 		= &JComponentHelper::getParams('com_awdwall');
		$template 		= $config->get('temp', 'blue');
		$avatarintergration 		= $config->get('avatarintergration', '0');
		$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userId;
		$db->setQuery($query);
		$facebook_id = $db->loadResult();
		if($facebook_id)
		{
			$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
		}
		else
		{
			$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
			$db 	= & JFactory::getDBO();
			$db->setQuery($query);
			$img = $db->loadResult();		
			
			if($img == NULL){
				$avatar = JURI::base() . "components/com_awdwall/images/".$template."/".$template."32.png";
			}else{
				$avatar = JURI::base() . "images/wallavatar/" . $userId . "/thumb/tn32" . $img;
			}
			
		}
	    if($avatarintergration==1) // k2
		{
				if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
				{
					require_once (JPATH_SITE . '/components/com_k2/helpers/utilities.php');
				
				$avatar=K2HelperUtilities::getAvatar($userId);
				}
		}
		else if($avatarintergration==2) // easyblog
		{
				if(file_exists(JPATH_SITE . '/components/com_easyblog/easyblog.php'))
				{
					require_once (JPATH_SITE . '/components/com_easyblog/helpers/helper.php');
				
				$blogger	= EasyBlogHelper::getTable( 'Profile', 'Table');
				$blogger->load( $userId );
				$avatar=$blogger->getAvatar();
				}
		}
		else if($avatarintergration==3) // alphauserpoint
		{
				if(file_exists(JPATH_SITE . '/components/com_alphauserpoints/alphauserpoints.php'))
				{
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helper.php');
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helpers/helpers.php');
				
					$_user_info = AlphaUserPointsHelper::getUserInfo ( $referrerid='', $userId  );
					$com_params = &JComponentHelper::getParams( 'com_alphauserpoints' );
					$useAvatarFrom = $com_params->get('useAvatarFrom');
					$height = 32;
					$width=32;
					$avatar = getAvatar( $useAvatarFrom, $_user_info, $height,$width);	
					$doc = new DOMDocument();
					$doc->loadHTML($avatar);
					$imageTags = $doc->getElementsByTagName('img');
					
					foreach($imageTags as $tag) {
						$avatar=$tag->getAttribute('src');
					}
				}
		}
		return $avatar;
}
public function getAvatar51($userId,$wallversion)
{	
		$db =& JFactory::getDBO();
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$template 		= $config->get('temp', 'blue');
		$avatarintergration 		= $config->get('avatarintergration', '0');
		$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userId;
		$db->setQuery($query);
		$facebook_id = $db->loadResult();
		if($facebook_id)
		{
			$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
		}
		else
		{
			$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
			$db 	= & JFactory::getDBO();
			$db->setQuery($query);
			$img = $db->loadResult();		
			
			if($img == NULL){
				$avatar = JURI::base() . "components/com_awdwall/images/".$template."/".$template."51.png";
			}else{
				$avatar = JURI::base() . "images/wallavatar/" . $userId . "/thumb/tn51" . $img;
			}
			
		}
		if($avatarintergration==1) // k2
		{
				if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
				{
					require_once (JPATH_SITE . '/components/com_k2/helpers/utilities.php');
				
				$avatar=K2HelperUtilities::getAvatar($userId);
				}
			
		}
		else if($avatarintergration==2) // easyblog
		{
				if(file_exists(JPATH_SITE . '/components/com_easyblog/easyblog.php'))
				{
					require_once (JPATH_SITE . '/components/com_easyblog/helpers/helper.php');
				
				$blogger	= EasyBlogHelper::getTable( 'Profile', 'Table');
				$blogger->load( $userId );
				$avatar=$blogger->getAvatar();
				}
		}
		else if($avatarintergration==3) // alphauserpoint
		{
				if(file_exists(JPATH_SITE . '/components/com_alphauserpoints/alphauserpoints.php'))
				{
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helper.php');
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helpers/helpers.php');
				
					$_user_info = AlphaUserPointsHelper::getUserInfo ( $referrerid='', $userId  );
					$com_params = &JComponentHelper::getParams( 'com_alphauserpoints' );
					$useAvatarFrom = $com_params->get('useAvatarFrom');
					$height = 50;
					$width=50;
					$avatar = getAvatar( $useAvatarFrom, $_user_info, $height,$width);	
					$doc = new DOMDocument();
					$doc->loadHTML($avatar);
					$imageTags = $doc->getElementsByTagName('img');
					
					foreach($imageTags as $tag) {
						$avatar=$tag->getAttribute('src');
					}
				}
		}
		return $avatar;
	}




public function getComItemId()
{
	$db 	= &JFactory::getDBO();
	$query  = "SELECT id FROM #__menu WHERE link='index.php?option=com_awdwall&view=awdwall&layout=main' and published='1'";
	$db->setQuery($query);
	return $db->loadResult();
}

public function formatUrlInMsg($msg)
{
		$stringToArray = explode(" ", $msg);
		$msg = '';
		foreach($stringToArray as $key => $val){
			if(preg_match('/^(http(s?):\/\/|ftp:\/\/{1})((\w+\.){1,})\w{2,}$/i', $val)){
				$val = '<a href="' . $val . '" target="_blank">' . $val . '</a>';
			}else if(preg_match('/^((\w+\.){1,})\w{2,}$/i', $val)){
				$val = '<a href="http://' . $val . '" target="_blank">' . $val . '</a>';
			}else if(preg_match('/^(http(s?):\/\/|ftp:\/\/{1})/i', $val)){
				$val = '<a href="' . $val . '" target="_blank">' . $val . '</a>';
			}
			$msg .= $val . ' ';
		}
		return $msg;
}

}