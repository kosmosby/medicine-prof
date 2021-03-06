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
$menuId = isset($_REQUEST['menuId']) ? trim($_REQUEST['menuId']) : '';
$theme = isset($_REQUEST['theme']) ? trim($_REQUEST['theme']) : 'sam';
$langDir = isset($_REQUEST['dir']) ? trim($_REQUEST['dir']) : 'ltr';

if ($menuId && !preg_match('/^[A-z_0-9]+$/', $menuId)) $menuId = '';
if ($theme && !preg_match('/^[A-z_0-9]+$/', $theme)) $theme = 'sam';

$basePath = dirname(__FILE__) . '/js/assets/menu/';
if (!@file_exists($basePath . $theme)) $theme = 'sam';

$cssContent = '';
$cssPath = ($langDir == 'rtl')
		? $basePath . $theme . '/' . ($menuId ? 'safe_menu_rtl.css' : 'menu_rtl.css')
		: $basePath . $theme . '/' . ($menuId ? 'safe_menu.css' : 'menu.css');
if (@file_exists($cssPath))
{
	$cssContent = file_get_contents($cssPath);
	if ($menuId) $cssContent = str_replace('{$id}', '#' . $menuId, $cssContent);
}

@ob_end_clean();		
header('Content-Type: text/css');
echo $cssContent;
exit();
?>