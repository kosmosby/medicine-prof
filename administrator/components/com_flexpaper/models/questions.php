<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * flexpaperList Model
 */
class flexpaperModelQuestions extends JModelList
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{

		// Filter by course.
		$test_id = $this->getState('filter.test_id');

		// Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('a.id,a.question,b.name');
		// From the hello table


        $query->from('#__lms_tests as b, #__lms_questions as a');
        $query->where('a.testid = b.id');

        if (is_numeric($test_id)) {
            $query->where('b.id = '.$test_id);
        }


        //if (is_numeric($course_id)) {
			//$query->where('a.cid = '.(int) $clientId);
		//}

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'ordering');
        $orderDirn	= $this->state->get('list.direction', 'ASC');


//       if($orderCol == 'a.question') {
//           $orderCol = 'a.question';
//       }

        if($orderCol) {
           $query->order($db->escape($orderCol.' '.$orderDirn));
        }


        return $query;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		$clientId = $this->getUserStateFromRequest($this->context.'.filter.test_id', 'filter_test_id', '');
		$this->setState('filter.test_id', $clientId);

        $filter_order = JRequest::getCmd('filter_order');
        $filter_order_Dir = JRequest::getCmd('filter_order_Dir');

        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);

        // List state information.
		parent::populateState($filter_order, $filter_order_Dir);
	}

}