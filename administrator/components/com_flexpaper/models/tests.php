<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * flexpaperList Model
 */
class flexpaperModelTests extends JModelList
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('a.id,a.name');
		// From the hello table

        $query->from('#__lms_tests as a');

		//if (is_numeric($course_id)) {
			//$query->where('a.cid = '.(int) $clientId);
		//}

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'ordering');
        $orderDirn	= $this->state->get('list.direction', 'ASC');


       if($orderCol == 'a.name') {
           $orderCol = 'a.name';
       }

        if($orderCol) {
           $query->order($db->escape($orderCol.' '.$orderDirn));
        }

        return $query;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

        $filter_order = JRequest::getCmd('filter_order');
        $filter_order_Dir = JRequest::getCmd('filter_order_Dir');

        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);

        // List state information.
		parent::populateState($filter_order, $filter_order_Dir);
	}

}