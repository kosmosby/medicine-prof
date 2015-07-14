<?php
/*
 * ARI YUI Menu Joomla! module
 *
 * @package		ARI YUI Menu Joomla! module.
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2009 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__) . '/mod_ariyuimenu/kernel/class.AriKernel.php';

AriKernel::import('Utils.Utils');
AriKernel::import('Menu.Menu');
AriKernel::import('Web.HtmlHelper');
AriKernel::import('YUIMenu.YUIMenu');
AriKernel::import('Template.Template');

$menu = new AriMenu($params->get('menutype', 'mainmenu'));
$menuLevel = $menuStartLevel = intval($params->get('startLevel', 0), 10);
$menuEndLevel = intval($params->get('endLevel', 0), 10);

$uniqueId = (bool)$params->get('uniqId', false);
$activeMenuItem = $menu->getActive();

// Template parameters
$menuDirection = $params->get('direction');
$menuId = !$uniqueId ? 'ariyui' . $module->id : uniqid('ayui', false);
$hlCurrentItem = (bool)$params->get('highlightCurrentItem', true) && !is_null($activeMenuItem);
$hlOnlyActiveItems = (bool)$params->get('onlyActiveItems', false);
$activeTopId = $activeMenuItem ? $activeMenuItem->id : 0;

AriYUIMenuHelper::initMenu($menuId, $menu, $menu->getItems(ARI_MENU_LEVEL_PARAM, $menuLevel), $activeTopId, $hlCurrentItem, $params);

require JModuleHelper::getLayoutPath('mod_ariyuimenu');