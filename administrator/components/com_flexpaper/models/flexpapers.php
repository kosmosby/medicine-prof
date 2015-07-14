<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * flexpaperList Model
 */
class flexpaperModelflexpapers extends JModelList
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{

		// Filter by course.
		$course_id = $this->getState('filter.course_id');

		// Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('a.id,a.name,b.title, c.title as course');
		// From the hello table

		if ($course_id && is_numeric($course_id)) {
			$query->from('#__flexpaper as a, #__categories as b, #__osemsc_acl as c');
			$query->where('a.catid = b.id AND a.membership_list_id = c.id AND a.membership_list_id = '.$course_id.' ');
		}
		else {
			$query->from('#__flexpaper as a, #__categories as b, #__osemsc_acl as c');
			$query->where('a.membership_list_id = c.id');
			$query->where('a.catid = b.id');
		}	

		//if (is_numeric($course_id)) {
			//$query->where('a.cid = '.(int) $clientId);
		//}

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'ordering');
        $orderDirn	= $this->state->get('list.direction', 'ASC');


       if($orderCol == 'b.title') {
           $orderCol = 'b.title, a.name, d.title';
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

		$clientId = $this->getUserStateFromRequest($this->context.'.filter.course_id', 'filter_course_id', '');
		$this->setState('filter.course_id', $clientId);

        $filter_order = JRequest::getCmd('filter_order');
        $filter_order_Dir = JRequest::getCmd('filter_order_Dir');

        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);

        // List state information.
		parent::populateState($filter_order, $filter_order_Dir);
	}

}