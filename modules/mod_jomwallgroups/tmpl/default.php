<?php // no direct access
defined('_JEXEC') or die('Restricted access');  
$db			=& JFactory::getDBO();
$Itemid		= JomWallGroupsHelper::getComItemId();
$topgroup=$params->get('topgroup', '');
if($topgroup)
{

}
else
{
$topgroup=$list[0]->id;
}
?>
<link rel="stylesheet" href="<?php echo JURI::base();?>modules/mod_jomwallgroups/css/style.css"  type="text/css" />

<div id="awdWallGroup">
<?php
if($topgroup)
{
			 $sql="select * from #__awd_groups where id=".$topgroup." limit 1";
			 $db->setQuery($sql);
			 $topgrouprows = $db->loadObjectList();
				$grouplink= 'index.php?option=com_awdwall&task=viewgroup&groupid='.$topgroup.'&Itemid='.$Itemid;
				$groupmemberlink= 'index.php?option=com_awdwall&task=grpmembers&groupid='.$topgroup.'&Itemid='.$Itemid;
				$grpthumb=AwdwallHelperUser::getBigGrpImg133($topgrouprows[0]->image,$topgroup);
				
			  $groupModel = new AwdwallModelGroup();
				$nofPosts = $groupModel->countPostGrp($topgroup);
				$nofMembers = $groupModel->countMemGrp($topgroup) + 1;
?>
<div id="parentGroupDiv" class="parentgrpdiv">
	<div id="jomwallgrpthumb" ><a href="<?php echo  JRoute::_($grouplink,false); ?>"  ><div style="background-image:url('<?php echo $grpthumb; ?>'); background-position:center center; " class="featuregrpthumb"></div></a></div>
	<div id="jomwallgrpcontent" ><h3 class="jomwallgrphtitle"><a href="<?php echo  JRoute::_($grouplink,false); ?>"  > <?php echo $topgrouprows[0]->title; ?></a></h3>
<div class="jomwallgrpActions clearfix">
  <div style="float:left; "> <i class="jomwallmembericon"></i> <a href="<?php echo JRoute::_($groupmemberlink); ?>"><?php echo  $nofMembers . ' ' . JText::_('GROUP MEMBER');?></a> </div>
  <div style="float:left;margin-left:5px;"> <i class="jomwallwallposticon"></i><a href="<?php echo  JRoute::_($grouplink,false); ?>"> <?php echo  $nofPosts . ' ' .JText::_('POSTS');?> </a></div>
</div>
<div id="jomwallgrpdescr" class="clearfix"><?php if(str_word_count($topgrouprows[0]->description)>10){ echo substr($topgrouprows[0]->description,0,50).'...'; } else { echo $topgrouprows[0]->description; } ?></div>
  <span  class="grpdate"><?php echo JText::_('Created');?> : <strong><?php echo AwdwallHelperUser::getDisplayTime($topgrouprows[0]->created_date);?></strong></span>
</div>
</div>
<?php
}
 ?>

<div style="clear:both"></div>
<?php $i=0; 
foreach($list as $item1){
if($item1->id==$topgroup) {continue;}
?>
<div id="subGroupDiv<?php echo $i; ?>"  class="subgrprowdiv">
	<div id="jomwallgrpsubthumb" ><a href="<?php echo JRoute::_($item1->link); ?>"  ><div style="background-image:url('<?php echo $item1->thumb; ?>'); background-position:center center; " class="subgrpthumb"></div></a></div>
	<div id="jomwallgrpsubcontent" ><h3 class="jomwallgrphsubtitle"><a href="<?php echo  JRoute::_($item1->link,false); ?>"  > <?php echo $item1->title; ?></a></h3>
  <span class="grpdate"><?php echo JText::_('Created');?> : <strong><?php echo AwdwallHelperUser::getDisplayTime($item1->created_date);?></strong></span>
<div id="jomwallgrpsubdescr" class="clearfix"><?php if(str_word_count($item1->descr)>10){ echo substr($item1->descr,0,50).'...'; } else { echo $item1->descr; } ?></div>
<div class="jomwallgrpActions clearfix">
  <div style="float:left; "> <i class="jomwallmembericon"></i> <a href="<?php echo JRoute::_($item1->memberlink); ?>"><?php echo  $item1->nofMembers . ' ' . JText::_('Member');?></a> </div>
  <div style="float:left;margin-left:5px;"> <i class="jomwallwallposticon"></i><a href="<?php echo JRoute::_($item1->link); ?>"> <?php echo  $item1->nofPosts . ' ' .JText::_('Post');?> </a></div>
</div>
</div>
	
</div>
<?php
$i++; } 
?>
<div id="viewall">
<?php
if($list)
{?>
	<img src="<?php echo JURI::base(); ?>modules/mod_jomwallgroups/images/arrow.png"  align="baseline"  /><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=groups&Itemid='.$Itemid,false) ?>" class="link"><?php echo  JText::_('View all groups');?></a>
<?php
}
else {
	echo  JText::_('No groups');
}?>
</div>
</div>
