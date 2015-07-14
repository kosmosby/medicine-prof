<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$Itemid		= AwdWallGroupsHelper::getComItemId();
?>
<link rel="stylesheet" href="<?php echo JURI::base();?>modules/mod_awdwallgroups/css/style.css"  type="text/css" />

<div id="awdWallGroup">
<?php
$i=0;
 foreach($list as $item){
 if($i==0) {
 $style='style="display:block;"';
 } else {
 $style='style="display:none;"';
 }

 ?>
<div id="parentGroupDiv<?php echo $i; ?>" <?php echo $style; ?> >
	<div id="thumb" ><a href="<?php echo JRoute::_($item->link); ?>"  ><img src="<?php echo $item->thumb; ?>" /></a></div>
	<div id="title" style="text-align:center;"><a href="<?php echo  JRoute::_($item->link,false); ?>"  > <?php echo $item->title; ?></a></div>
	<div id="descr"><?php
	if(str_word_count($item->descr)>10){
	echo substr($item->descr,0,50).'...'; } else {
	echo $item->descr; } ?></div>
</div>
<?php $i++; } ?>
<div>
<?php $i=0;
foreach($list as $item1){
?>
<div id="thumbGroup"> <img src="<?php echo $item1->thumb; ?>"   onclick="activeGroupDiv('<?php echo $i; ?>')" height="30px" width="30px" /> </div>
<?php
$i++; }
?>
</div>
<div id="viewall">
<?php
if($list)
{?>
	<img src="<?php echo JURI::base(); ?>modules/mod_awdwallgroups/images/arrow.png"  align="baseline"  /><a href="<?php echo JRoute::_('index.php?option=com_comprofiler&task=pluginclass&plugin=cbgroupjive&action=groups&func=all&Itemid='.$Itemid,false) ?>" class="link"><?php echo JText::_('MOD_AWDWALLVIEWALLGROUP');?></a>
<?php
}
else {
	echo  JText::_('MOD_AWDWALLNOGROUP');
}?>
</div>
</div>
<script type="text/javascript">
function activeGroupDiv(id)
{
	var totItem=<?php echo count($list) ?>;

	for(i=0;i<totItem;i++)
	{

		var divID="parentGroupDiv"+i;
		if(i==id){
		//alert(i);
			document.getElementById(divID).style.display='block';
		} else {
			document.getElementById(divID).style.display='none';
		}
	}
}
</script>