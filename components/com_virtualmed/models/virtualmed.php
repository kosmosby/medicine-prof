<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * HelloWorld Model
 *
 * @since  0.0.1
 */
class VirtualmedModelVirtualmed extends JModelItem
{
	/**
	 * @var object item
	 */
	protected $item;

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	2.5
	 */
	protected function populateState()
	{
		// Get the message id
		$jinput = JFactory::getApplication()->input;
		$id     = $jinput->get('id', 1, 'INT');
		$this->setState('message.id', $id);

		// Load the parameters.
		$this->setState('params', JFactory::getApplication()->getParams());
		parent::populateState();
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'HelloWorld', $prefix = 'HelloWorldTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get the message
	 * @return object The message to be displayed to the user
	 */
	public function getItems()
	{
//		if (!isset($this->item))
//		{
			//$id    = $this->getState('message.id');
            $group_id= 10;
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('h.user_id, a.name, b.avatar, b.cb_speciality, b.cb_city')
				  ->from('#__user_usergroup_map as h')
                    ->from('#__users as a')
                    ->from('#__comprofiler as b')
				 // ->leftJoin('#__categories as c ON h.catid=c.id')
				  ->where('h.group_id=' . (int)$group_id)
                    ->where('h.user_id= a.id')
                ->where('b.user_id= a.id')
                ->order(('a.name asc'));;
			$db->setQuery((string)$query);

			$this->items = $db->loadObjectList();





            for($i=0;$i<count($this->items);$i++) {

                $nodes = array();
                $nodes = $this->getClinics($this->items[$i]->user_id);

                for($j=0;$j<count($nodes);$j++) {
                    $departments = $this->getDepartments($nodes[$j]->profile_id, $nodes[$j]->id);


                    for($k=0;$k<count($departments);$k++) {
                        $employees = $this->getEmployees($departments[$k]->id);
                        $departments[$k]->nodes = $employees;
                    }

                    $nodes[$j]->nodes = $departments;
                }

                $this->items[$i]->nodes = $nodes;

            }
//		}
//
//        echo "<pre>";
//        print_r($this->items); die;


        return $this->items;
	}

    public function getEmployees($department_id) {

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('b.user_id, a.username as name, b.avatar, b.cb_speciality')
            //->from('#__user_usergroup_map as h')
            ->from('#__users as a')
            ->from('#__comprofiler as b')
            ->from('#__comprofiler_plugin_department_employees as c')
            // ->leftJoin('#__categories as c ON h.catid=c.id')
            //->where('h.group_id=' . (int)$group_id)
            ->where('c.department_id=' . (int)$department_id)
            ->where('c.user_id= a.id')
            ->where('c.user_id= b.user_id');
        //->where('b.user_id= a.id');
        $db->setQuery((string)$query);

        $employees = $db->loadObjectList();

        //echo $db->getQuery(); die;


        return $employees;
    }

    public function getClinics($id) {

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('c.*')
            //->from('#__user_usergroup_map as h')
            //->from('#__users as a')
            ->from('#__comprofiler as b')
            ->from('#__comprofiler_plugin_department_clinic as c')
            // ->leftJoin('#__categories as c ON h.catid=c.id')
            //->where('h.group_id=' . (int)$group_id)
            ->where('c.profile_id=' . (int)$id)
            ->where('c.profile_id= b.user_id')
            ->order(('c.title asc'));
        $db->setQuery((string)$query);

        $departments = $db->loadObjectList();

        //echo $db->getQuery(); die;


        return $departments;
    }

    public function getDepartments($profile_id, $clinic_id) {

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('c.*')
            //->from('#__user_usergroup_map as h')
            //->from('#__users as a')
            ->from('#__comprofiler as b')
            ->from('#__comprofiler_plugin_department as c')
            // ->leftJoin('#__categories as c ON h.catid=c.id')
            //->where('h.group_id=' . (int)$group_id)
            ->where('c.profile_id=' . (int)$profile_id)
            ->where('c.clinic_id=' . (int)$clinic_id)
            ->where('c.profile_id= b.user_id')
            ->order(('c.title asc'));
        //->where('b.user_id= a.id');
        $db->setQuery((string)$query);

        $departments = $db->loadObjectList();

        //echo $db->getQuery(); die;

//        echo "<pre>";
//        print_r($departments); die;


        return $departments;
    }
}
