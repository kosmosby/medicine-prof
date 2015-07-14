<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Users table
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @since       11.1
 */
 jimport('joomla.database.tablenested');
class oseMscTableMembership extends JTableNested
{
	/**
	 * Contructor
	 *
	 * @param  database   A database connector object
	 *
	 * @return  JTableUser
	 *
	 * @since  11.1
	 */
	public $parent_id = 0;
	public $id = 0;
	public $title = null;
	public $alias = null;
	public $path = 'virtual';
	public $type = 'requiredoption';
	public $description = null;
	public $restricted_number = 99999;
	//public $leaf = 0;
	public $published = 1;
	public $image  = null;
	
	public $menuid  = null;
	
	public $params = '';
	
	function __construct(&$db)
	{
		parent::__construct('#__osemsc_acl', 'id', $db);

		// Initialise.
		//$this->id = 0;
	}
	
	public function bind($array, $ignore = '')
	{
		
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}
		
		$bind = parent::bind($array, $ignore);
		
		if($this->parent_id == 0)
		{
			$this->Set('parent_id',$this->getRootId());
		}
		return $bind;
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success
	 *
	 * @see     JTable::check
	 * @since   11.1
	 */
	public function check()
	{
		// If the alias field is empty, set it to the title.
		return true;
	}
	
	/**
	 * Overloaded store function
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  mixed    False on failure, positive integer on success.
	 *
	 * @see     JTable::store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		$updated = parent::store($updateNulls);

		// Verify that the alias is unique
		$table = JTable::getInstance('Membership','oseMscTable');
		$table->load($this->getRootId());
		$table->rebuild($this->getRootId(),$table->lft, $table->level);
		
		/*if($this->id != $this->getRootId())
		{
			
			// Rebuild the paths of the category's children:
			//oseExit($table);
			if (!$table->rebuild($table->id, $table->lft, $table->level) && $updated) {
				return false;
			}
		}*/
		return $updated;
		//$table->load($table->getRootId());
		
		// Use new path for partial rebuild of table
		// Rebuild will return positive integer on success, false on failure
		//return ($table->rebuild());
		//return ($this->rebuild($this->{$this->_tbl_key}, $this->lft, $this->level) > 0);
	}
	
	
}
