<?php 

$db		=& JFactory :: getDBO();

//$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";

$link='index.php?option=com_awdwall&controller=colors';

$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");

$params = json_decode( $db->loadResult(), true );

for($i=1; $i<=14; $i++)

{

	$str_color = 'color'.$i;			

	$color[$i]= $params[$str_color];

}





?>



<div style="background-color:#<?php echo $color[12];?>;margin-bottom:5px;">

<?php 

$rows=$this->rows;

$user =& JFactory::getUser();

$totLike=$this->totLike;

?>

<div style="width:100%; text-align:left;padding-bottom:3px;"><?php echo $totLike.'&nbsp;'.JText::_('People like this photo');?></div>

<?php

foreach($rows as $row)

{

$userprofileLinkAWDCUser=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$row->userid.'&Itemid='.AwdwallHelperUser::getComItemId());

		 $values=getCurrentUserDetails($row->userid);  

		 $avatarTable=$values[2];

		 $userprofileLinkCUser=$values[1];

	

	$values1=getUserDetails($row->userid,$avatarTable,$user->id); 

	$imgPath1=$values1[0];

	 

	?>

	<a href="<?php echo $userprofileLinkCUser; ?>" style="padding-right:5px;">
			<img src="<?php echo $imgPath1; ?>" border="0" />
	</a>

	<?php

}



?></div>