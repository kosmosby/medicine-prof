<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * HelloWorld Model
 */
class flexpaperModelflexpaper extends JModelItem
{
	/**
	 * @var array messages
	 */
	protected $messages;
 
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	2.5
	 */
	public function getTable($type = 'flexpaper', $prefix = 'flexpaper', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Get the message
	 * @param  int    The corresponding id of the message to be retrieved
	 * @return string The message to be displayed to the user
	 */
	public function getDoc()
	{
        $id = JRequest::getInt('id', 0);

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query = "select * from #__flexpaper where id=".$id;

        $db->setQuery($query);
        $this->_item = $db->loadobject();

        return $this->_item;
	}

    public function getActiveCourse() {

        $id = JRequest::getInt('id', 0);
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query = "select a.catid,a.name,b.title from #__flexpaper as a, #__categories as b where a.id=".$id." AND a.catid = b.id";

        $db->setQuery($query);
        $this->_item = $db->loadObject();

        return $this->_item;
    }
}