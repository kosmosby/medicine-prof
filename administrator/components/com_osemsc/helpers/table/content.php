<?php
defined('JPATH_PLATFORM') or die;
class oseMscTableContent extends JTable
{

	public $id = 0;
	public $type = 'joomla';
	public $content_type = null;
	public $content_id = 0;
	public $entry_type = 'msc';
	public $entry_id = 0;
	public $status = 0;
	public $params = array();
	
	function __construct(&$db)
	{
		parent::__construct('#__osemsc_content', 'id', $db);
	
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
	
		return parent::bind($array, $ignore);
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
		// Get the table key and key value.
		$k = $this->_tbl_key;
		$key =  $this->$k;
	
		// Insert or update the object based on presence of a key value.
		if ($key) {
			// Already have a table key, update the row.
			$return = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		}
		else {
			// Don't have a table key, insert the row.
			$return = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}
	
		// Handle error if it exists.
		if (!$return)
		{
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', strtolower(get_class($this)), $this->_db->getErrorMsg()));
			return false;
		}
	
		return $return;
	}
}
