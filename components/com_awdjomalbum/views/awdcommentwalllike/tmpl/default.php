<div style="background-color:#CCCCCC; height:50px; padding-left:2px; margin-bottom:2px;">

<?php 

$rows=$this->rows;

$user =& JFactory::getUser();

$totLike=$this->totLike;

?>

<div style="width:100%; text-align:left;"><?php echo $totLike.'&nbsp;'.JText::_('People like this comment');?></div>

<?php

foreach($rows as $row)

{

$userprofileLinkAWDCUser=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$row->userid.'&Itemid='.AwdwallHelperUser::getComItemId());

		 $values=getCurrentUserDetails($row->userid);  

		 $avatarTable=$values[2];

		 

	

	$values1=getUserDetails($row->userid,$avatarTable,$user->id); 

	$imgPath1=$values1[0];

	 

	?>

	<div class="albumlike"><a href="<?php echo $userprofileLinkAWDCUser; ?>">
			<img src="<?php echo $imgPath1; ?>" border="0" />
	</a></div>

	<?php

}



?></div>