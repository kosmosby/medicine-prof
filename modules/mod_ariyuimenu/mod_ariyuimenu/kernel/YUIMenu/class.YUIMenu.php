<?php
/*
 * ARI YUI menu
 *
 * @package		ARI YUI Menu
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2009 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

AriKernel::import('Web.JSON.JSONHelper');

class AriYUIMenuHelper
{
	function loadAssets($menuId, $safeMode = false)
	{
		static $loaded;
		
		if ($loaded)
		{
			if ($safeMode)
				AriYUIMenuHelper::loadCSS($menuId, $safeMode);
		
			return ;
		}

		AriYUIMenuHelper::loadCSS($menuId, $safeMode);
		
		$rootUrl = JURI::root(true) . '/modules/mod_ariyuimenu/mod_ariyuimenu/';
		$jsUrl = $rootUrl . 'js/';

		$doc =& JFactory::getDocument();
		
		$doc->addScript($jsUrl . 'yui.combo.js');
		$doc->addScriptDeclaration('try { document.execCommand("BackgroundImageCache", false, true); } catch(e) {};');

		$loaded = true;
	}
	
	function loadCSS($menuId, $safeMode = false)
	{
		$rootUrl = JURI::root(true) . '/modules/mod_ariyuimenu/mod_ariyuimenu/';
		$doc =& JFactory::getDocument();
		if (!$safeMode)
		{
			$doc->addStyleSheet($rootUrl . 'js/assets/menu/sam/menu.css');
		}
		else
		{
			$lang =& JFactory::getLanguage();
			$doc->addStyleSheet($rootUrl . 'css_loader.php?menuId=' . $menuId . ($lang->isRTL() ? '&dir=rtl' : ''));
		}
	}
	
	function getMenuItemIndex($menu, $currentMenu, $activeTopId, $showHiddenItems = false)
	{
		$selectedIndex = -1;
		
		if (empty($currentMenu) || empty($activeTopId))
			return $selectedIndex;
			
		$i = 0;
		foreach ($currentMenu as $menuItem)
		{
			if ((!J1_6 && !$menuItem->published) || ($showHiddenItems && $menu->authorize($menuItem->id)))
				continue ;
				
			if ($menu->isChildOrSelf($menuItem->id, $activeTopId))
				return $i;

			$i++;
		}

		return $selectedIndex;
	}
	
	function initMenu($id, $menu, $currentMenu, $activeTopId, $hlCurrentItem, $params)
	{
		$safeMode = (bool)$params->get('safeMode', true);
		AriYUIMenuHelper::loadAssets($id, $safeMode);
		
		$menuId = $id . '_menu';
		$direction = $params->get('direction', 'horizontal');
		$isVertical = ($direction != 'horizontal');
		$selectedIndex = $hlCurrentItem 
			? AriYUIMenuHelper::getMenuItemIndex($menu, $currentMenu, $activeTopId, (bool)$params->get('showHiddenItems', false))
			: -1;

		$defMenuConfig = array(
			'zIndex' => 0,
			'classname' => '',
			'hidedelay' => 0,
			'maxheight' => 0,
			'minscrollheight' => 90,
			'scrollincrement' => 1,
			'showdelay' => 250
		);

		$config = array(
			'lazyLoad' => true, 
			'autosubmenudisplay' => true, 
			'position' => 'static'
		);
		foreach ($defMenuConfig as $key => $defValue)
		{
			$value = AriUtils::parseValueBySample($params->get($key, $defValue), $defValue);
			if ($value != $defValue) $config[$key] = $value;
		}
		
		$submenuAlign = $params->get('submenualignment');
		if (!empty($submenuAlign))
		{
			$submenuAlign = explode(',', $submenuAlign);
			$submenuAlign = array_map('trim', $submenuAlign);
	
			$config['submenualignment'] = $submenuAlign;	
		}
		
		$doc =& JFactory::getDocument();
		
		$doc->addScriptDeclaration(
			sprintf(
				'YAHOO.util.Event.onContentReady("' . $menuId . '", function () { var oMenu = new YAHOO.widget.%3$s("' . $menuId . '", %1$s); oMenu.render(); oMenu.show(); if (%2$d > -1) oMenu.getItem(%2$d).cfg.setProperty("selected", true); });',
				AriJSONHelper::encode($config),
				$selectedIndex,
				$isVertical ? 'Menu' : 'MenuBar'
			)
		);
		
		AriYUIMenuHelper::addCustomStyles($id, $params);
	}
	
	function addCustomStyles($id, $params)
	{
		$styles = str_replace(array('{$id}'), array($id), $params->get('customstyle'));
		$width = $params->get('width', '');
		if ($width)
			$styles .= sprintf('#%s{width:%s}',
				$id,
				$width);
				
		$styles .= sprintf('#%1$s A{font-size: %2$s !important; font-weight: %3$s !important; text-transform: %4$s !important;}',
			$id,
			$params->get('fontSize', '11px'),
			$params->get('fontWeight', 'normal'),
			$params->get('textTransform', 'none')
		);
		
		if ($styles)
		{
			$doc =& JFactory::getDocument();
			$doc->addStyleDeclaration($styles);
		}
	}
}