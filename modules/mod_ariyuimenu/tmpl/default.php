<?php
/*
 * ARI YUI menu Joomla! module
 *
 * @package		ARI YUI Menu Joomla! module.
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2009 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
?>

<div class="yui-skin-sam" id="<?php echo $menuId; ?>">	
<?php
AriTemplate::display(
	dirname(__FILE__) . DS . 'menu.php', 
	array(
		'menu' => $menu,
		'menuId' => $menuId . '_menu',
		'menuStartLevel' => $menuStartLevel,
		'menuEndLevel' => $menuEndLevel,
		'menuLevel' => $menuLevel,
		'menuDirection' => $menuDirection,
		'hlCurrentItem' => $hlCurrentItem,
		'hlOnlyActiveItems' => $hlOnlyActiveItems,
		'showHiddenItems' => (bool)$params->get('showHiddenItems', false),
		'remainActive' => (bool)$params->get('remainActive', false),
		'advSeparator' => (bool)$params->get('advSeparator', false),
		'activeTopId' => $activeTopId,
		'parent' => 0
	)
);
?>
</div>