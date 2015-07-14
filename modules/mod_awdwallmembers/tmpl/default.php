<?php 
/**
 * @package    JomWALL 
 * @subpackage  mod_awdwallmembers
 * @link http://www.AWDsolution.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<?php
$rrows=$params->get('rows',1);
$ccolumns=$params->get('columns',5);
$interval=$params->get('interval',1500);
$maxStep=$params->get('maxStep',1);
$preventClick=$params->get('preventClick',false);
$animType=$params->get('animType',"fadeInOut");

$document	= JFactory::getDocument();
if(count($rows)>1)
{
	$count=count($rows)-1;
}
else
{
	$count=1;
}


//$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js' );
$document->addScript( JURI::base() . 'modules/mod_awdwallmembers/js/modernizr.custom.26887.js' );
$document->addScript( JURI::base() . 'modules/mod_awdwallmembers/js/jquery.transit.min.js' );
$document->addScript( JURI::base() . 'modules/mod_awdwallmembers/js/jquery.gridrotator.js' );
$document->addStyleSheet(JURI::base() . 'modules/mod_awdwallmembers/css/style.css');	
$script='jQuery.noConflict();jQuery(document).ready(function() {
				jQuery( "#ri-grid" ).gridrotator( {
					rows		: '.$rrows.',
					columns		: '.$count.',
					animType	: "'.$animType.'",
					animSpeed	: 1000,
					interval	: '.$interval.',
					preventClick	: '.$preventClick.',
					maxStep		: '.$maxStep.'
				} );
			});';
if(count($rows)>0)
{
	$document->addScriptDeclaration( $script);	
}
$itemId = modAwdwallmembersHelper::getComItemId();
$app = JFactory::getApplication('site');
$config =  $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$image=$template."51.png";
$integration=$params->get('integration',2);
?>
<div id="ri-grid" class="ri-grid ri-grid-size-2">
    <ul>
<?php
$i=0;
foreach($rows as $row)
{
	$url = JRoute::_('index.php?option=com_comprofiler&view=userprofile&user=' . $row->user_id . '&Itemid=445', false);
	$avatar =AwdwallHelperUser::getBigAvatar51( $row->user_id);
	$pos = strrpos($avatar, $image);
	if ($pos === false) 
	{ 
		if (file_exists($avatar)) 
		{
			
		} 
		else 
		{
		
		}
	}
	else
	{
		if($integration==0) //cb
		{
			$avatar=JURI::base() . "modules/mod_jomwallmember/tn51nophoto_n.png";
		}
		if($integration==1) //jomsocial
		{
			$avatar=JURI::base() . "modules/mod_jomwallmember/tn51user.png";
		}
	}
?>
                        <li>
                            <a href="<?php echo $url;?>">
                                <img src="<?php echo  $avatar;?>" title="<?php echo AwdwallHelperUser::getDisplayName($row->user_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($row->user_id);?>"  />
                            </a>
                        </li>
<?php
	$i=$i+1;
	}
	
?>

    </ul>
</div>


				

