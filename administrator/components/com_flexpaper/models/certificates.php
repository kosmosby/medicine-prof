<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * flexpaperList Model
 */
class flexpaperModelCertificates extends JModelList
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

        // Filters.
        $created_date = $this->getState('filter.created_date');

        if($created_date) {
            // Select some fields
            $query->select('a.id,a.name');
            // From the hello table
            $query->from('#__users as a, #__osemsc_order as b');
            $query->from("#__flexpaper_certificate as c");
            $query->from("#__flexpaper_quiz_results as d");

            $query->where("a.id = b.user_id");
            $query->where("c.user_id = b.user_id");
            $query->where("d.user_id = b.user_id AND d.tid = c.tid");

            switch($created_date) {
                case 7:
                    $query->where("d.time > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
                break;
                case 14:
                    $query->where("d.time > DATE_SUB(NOW(), INTERVAL 2 WEEK)");
                break;
                case 30:
                    $query->where("d.time > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
                    break;
                case 90:
                    $query->where("d.time > DATE_SUB(NOW(), INTERVAL 3 MONTH)");
                    break;
                case 180:
                    $query->where("d.time > DATE_SUB(NOW(), INTERVAL 6 MONTH)");
                    break;
                case 810:
                    $query->where("d.time > DATE_SUB(NOW(), INTERVAL 9 MONTH)");
                    break;
                case 365:
                    $query->where("d.time > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
                break;
            }
        }
        else {
            // Select some fields
            $query->select('a.id,a.name');
            // From the hello table
            $query->from('#__users as a, #__osemsc_order as b');
            $query->LeftJoin("#__flexpaper_certificate as c ON c.user_id = b.user_id");
            $query->LeftJoin("#__flexpaper_quiz_results as d ON d.user_id = b.user_id AND d.tid = c.tid");
            $query->where("a.id = b.user_id");
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.name LIKE '.$search.' OR a.username LIKE '.$search.' OR a.email LIKE '.$search.')');
            }
        }

        $query->group('b.user_id');

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'ordering');
        $orderDirn	= $this->state->get('list.direction', 'ASC');

//        echo "<pre>";
//        print_r($_REQUEST); die;

        //echo $orderCol;

//        if ($orderCol == 'a.id') {
//            $orderCol = 'a.id';
//        }
//        if($orderCol == 'a.name')
//            $orderCol = 'a.name';
        if($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol.' '.$orderDirn));
        }
//


        //$query->order('a.name');

//        echo "<pre>";
//        print_r($query);

        return $query;
	}

    protected function populateState($ordering = null, $direction = null)
    {
        // Initialise variables.
        $app = JFactory::getApplication('administrator');

        $createDate = $this->getUserStateFromRequest($this->context.'.filter.created_date', 'filter_created_date', '');
        $this->setState('filter.created_date', $createDate);

        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $filter_order = JRequest::getCmd('filter_order');
        $filter_order_Dir = JRequest::getCmd('filter_order_Dir');

        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);

        // List state information.
        parent::populateState($filter_order, $filter_order_Dir);
    }


}