<?php
/**
* @version		$Id: router.php 10711 2008-08-21 10:09:03Z eddieajau $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

function medicineproductsBuildRoute(&$query)
{
	$segments = array();
	$db = JFactory::getDBO();
	$app		= JFactory::getApplication();
	$menu		= $app->getMenu();
	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
	} else {
		$menuItem = $menu->getItem($query['Itemid']);
	}
	$view_str = '';
	if(isset($query['view']))
	{
		$segments[] = $view_str = $query['view'];
		unset($query['view']);
	}elseif (isset($query['task'])){
		$segments[] = $view_str = $query['task'];
		unset($query['task']);
	}
	
	if(isset($query['id']))
	{
		
                $segments[] = $query['id'];
                
		unset($query['id']);
	};
	
	
	return $segments;
}

function medicineproductsParseRoute($segments)
{
	$vars = array();
	//ob_start();
	switch($segments[0])
	{
		
			
		default:
			
				$vars['view'] = 'list';
				
			
			
		
	}
	//ob_end_clean();
	return $vars;
}
