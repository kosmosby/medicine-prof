<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: installer
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');


class TheFactoryInstallerMenuHelper
{
	var $id = null;
	var $module_id = null;
	var $menutype = null;
	var $componentid = null;
	var $title = null;
	var $menu_list = array();
	
	function _getMenuType_ID(){
		$menuType = JTable::getInstance('menutype');
		// Joomla 1.6 will fail this
		$menuType->set("_tbl_key","title");
		$menuType->load($this->title);
		$this->id=$menuType->id;
		$this->menutype=$menuType->menutype;
	}
	
	function _getMenuModule_ID(){
		
		$module = JTable::getInstance( 'module');
		// Joomla 1.6 will fail this
		$module->set("_tbl_key","title");
		$module->load($this->title);
		$this->module_id = $module->id;
	}
	
	function storeMenu(){
		
		$this->_getMenuType_ID();
		
		$menuType = JTable::getInstance('menutype');
		$menuType->id 		= $this->id;
		$this->menutype = $menuType->menutype = JFilterInput::clean($this->title, 'username');
		$menuType->title 	= $this->title;
		$menuType->store();
		$this->storeMenuModule();
	}
	
	function storeMenuModule(){

		$db =  JFactory::getDBO();

		$this->_getMenuModule_ID();
		$module = JTable::getInstance( 'module');
		$module->id 		= $this->module_id;
		$module->title 		= $this->title;
		$module->position 	= 'position-7';
		$module->module 	= 'mod_menu';
		$module->published	= 1;
		$module->access 	= 1;
		$module->params		= '{"menutype":"'. $this->menutype.'"}';
        $module->client_id  =0;
		$module->store();
		
		$module->reorder( 'position='.$db->Quote($module->position) );

		// module assigned to show on All pages by default
		// Clean up possible garbage first
		$query = 'DELETE FROM #__modules_menu WHERE moduleid = '.(int) $module->id;
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
		}

		$query = 'INSERT INTO #__modules_menu VALUES ( '.(int) $module->id.', 0 )';
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
		}
		
		// Add Menu Items
		if(count($this->menu_list)){
			//remove all menu items
			 
			foreach ($this->menu_list as $k => $menuitem){
				$menuitem->setLocation(1, 'last-child');
				$menuitem->component_id = $this->componentid;
				$menuitem->menutype = $this->menutype;
				$menuitem->store();
			}
		}
		
	}

	function AddMenuItem($title, $alias, $link, $ordering, $access=1,$params=null){
		
		$menu = JTable::getInstance( 'menu');
		$menu->set("_tbl_key","title");
		$menu->load($title);
		$menu->set("_tbl_key","id");
		$menu->title=$title;
		$menu->alias = $alias;
		$menu->link = $link;
		$menu->type="component";
		$menu->access=$access;
		$menu->published=1;
		$menu->parent_id=1;
        $menu->level=1;
		$menu->ordering=$ordering;        
        $menu->client_id=0;
        $menu->component_id = $this->componentid;
		$this->menu_list[$title] = $menu;
	}
}



?>
