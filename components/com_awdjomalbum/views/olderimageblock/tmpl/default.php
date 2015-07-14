 <?php 
defined('_JEXEC') or die('Restricted access');
define('REAL_NAME', 0);
define('USERNAME', 1);
$Itemid=AwdwallHelperUser::getComItemId();
$userids=$this->userids;
$privacy=$this->privacy;
$useridstr=$this->useridstr;
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$listingperpage 		= $config->get('listingperpage', 5);
$imagelimit 		= $config->get('imagelimit',4);

$wallversion=checkversionwall();
$db		=& JFactory :: getDBO();
$user =& JFactory::getUser();
$pendingFriends = JsLib::getPendingFriends($user->id);
$groupsUrl=JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);
if($wallversion=='cb')
{
	$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid, false);
	$accountUrl=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $user->id . '&Itemid=' . $Itemid, false);
}
elseif($wallversion=='js')
{
	$friendJsUrl = JRoute::_('index.php?option=com_community&view=friends&userid=' . $user->id . '&Itemid=' . $Itemid,false);
	$accountUrl=JRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id . '&Itemid=' . $Itemid, false);
}
else
{
	$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid, false);
	$accountUrl=JRoute::_('index.php?option=com_awdwall&task=uploadavatar&Itemid=' . $Itemid, false);
}
//$config 		= &JComponentHelper::getParams('com_awdwall');
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'blue');
$width 		= $config->get('width', 725);
$displayName 	= $config->get('display_name', 1);
//$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
$link='index.php?option=com_awdwall&controller=colors';
$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
$params = json_decode( $db->loadResult(), true );
for($i=1; $i<=14; $i++)
{
	$str_color = 'color'.$i;			
	$color[$i]= $params[$str_color];
}
 $link3=JRoute::_('index.php?option=com_awdjomalbum&view=awdalbumlist&wuid='.$user->id.'&Itemid='.AwdwallHelperUser::getComItemId(),false);
$albumlink=JRoute::_("index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$user->id."&Itemid=".AwdwallHelperUser::getComItemId(),false);
if($privacy==0)
{
$awdpstr=JText::_('All');
}
if($privacy==1)
{
$awdpstr=JText::_('Friends Only');
}
if($privacy==2)
{
$awdpstr=JText::_('Friends Of Friends');
}
$count=count($userids);
if($count)
{
 ?>
 <link href="<?php echo JURI::base();?>components/com_awdjomalbum/css/colorbox.css" rel="stylesheet" type="text/css" />
 <script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdjomalbum/js/jquery.colorbox.js"></script>
<script>
function awdlighbox(awdlink){    
  jQuery.fn.colorbox({width:"990px", height:"550px", iframe:true,scrolling: false, href:awdlink});
}
</script>
<div style="clear:both; height:10px;"></div> 
<?php
foreach($userids as $userid)
{
$albumlink=JRoute::_("index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$userid."&Itemid=".AwdwallHelperUser::getComItemId(), false);

if($privacy==2)
{
$query= "SELECT upload_date FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=2 and #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc";
}

if($privacy==1)
{
$query= "SELECT upload_date FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=1 and #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc";
}

if($privacy==0)
{
$query= "SELECT upload_date FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=".$privacy." and  #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc limit 1";
}
$db->setQuery($query);
$upload_date=$db->loadResult();
$upload_date= AwdwallHelperUser::getDisplayTime(strtotime($upload_date));
?>

<div style="width:230px; margin:5px 5px;padding-left:5px;"><div style="float:left;width:50px; height:50px; overflow:hidden; box-shadow: 0px 0px 3px #111;"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$userid.'&Itemid='.AwdwallHelperUser::getComItemId());?>" target="_top"><img src="<?php echo AwdwallHelperUser::getBigAvatar51($userid);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($userid);?>" title="<?php echo AwdwallHelperUser::getDisplayName($userid);?>" border="0" width="50" /></a></div><div style="float:left;width:150px; padding-left:8px; padding-top:0px;"><div style="float:left; width:100%;"><span class="profileName"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$userid.'&Itemid='.AwdwallHelperUser::getComItemId());?>" target="_top"><?php echo AwdwallHelperUser::getDisplayName($userid);?></a></span></div><div style="float:left; width:100%;padding-top:5px;"><span class="wall_date" style="font-size:11px;"><?php echo $upload_date;?></span></div>
</div></div>
<div style="clear:both; height:10px;"></div> 
<?php
if($privacy==1)
{
$query= "SELECT *,#__awd_jomalbum_photos.id as pid FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=1 and  #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc limit ".$imagelimit;
}
if($privacy==2)
{
$query= "SELECT *,#__awd_jomalbum_photos.id as pid FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=2 and  #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc limit ".$imagelimit;
}
if($privacy==0)
{
$query= "SELECT *,#__awd_jomalbum_photos.id as pid FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=".$privacy." and  #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc limit ".$imagelimit;
}
$db->setQuery($query);
$puser_rows=$db->loadObjectList();
foreach($puser_rows as $puser_row)
{
$link 	=JRoute::_('index.php?option=com_awdjomalbum&view=awdimagelist&tmpl=component&pid='.$puser_row->pid.'&albumid='.$puser_row->albumid.'&Itemid='.AwdwallHelperUser::getComItemId());
$cquery= "SELECT count(*) FROM #__awd_jomalbum_comment where photoid=".$puser_row->pid;
$db->setQuery($cquery);
$commentcount=$db->loadResult();

?>
<div style="width:102px;margin:8px;   height:86px; float:left;">
<div  style="width:102px; height:76px; background-position:center; border:1px solid #CCCCCC; padding:4px; float:left;">
<a href="javascript::void(0)"  onclick="awdlighbox('<?php echo $link;?>');"><div style="background-image:url(<?php echo JURI::base();?>images/awd_photo/awd_thumb_photo/<?php echo $puser_row->image_name; ?>); background-repeat:no-repeat; width:102px; height:76px; background-position:center; float:left;"></div></a>
</div>
<div style="clear:both; height:5px;"></div>
<span style="width:100%; font-size:11px;"><?php if($commentcount){echo $commentcount.' '.JText::_('COMMENTS');}?></span>
</div>

<?php
}
?>
<div style="clear:both; height:5px;"></div> 
<span  class="add_as_friend" style="float:right; margin-right:10px;margin-top:15px;">
<a  href="<?php echo $albumlink;?>"><?php echo JText::_('Read More..');?></a>
</span>
<div style="clear:both; height:1px;"></div> 
<hr />
<?php
}
?>
<div style="clear:both; height:10px;"></div>
<?php
}
?>
