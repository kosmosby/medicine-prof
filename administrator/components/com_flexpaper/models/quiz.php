<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * HelloWorld Model
 */
class flexpaperModelquiz extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	JTable	A database object
     * @since	2.5
     */
    public function getTable($type = 'flexpaper', $prefix = 'flexpaperTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
    /**
     * Method to get the record form.
     *
     * @param	array	$data		Data for the form.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	mixed	A JForm object on success, false on failure
     * @since	2.5
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_flexpaper.course', 'course',
            array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form))
        {
            return false;
        }
        return $form;
    }
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	2.5
     */
    protected function loadFormData()
    {

        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_flexpaper.edit.course.data', array());


        if (empty($data))
        {
            $data = $this->getItem();

        }
        return $data;
    }

    public function getMembershipList() {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        // Select some fields
        $query = 'SELECT id, title FROM #__osemsc_acl WHERE published =1';

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $arr = $this->prepare_for_select($rows,'title');

        return $arr;

    }

    public function getMembershipQuizList()
    {

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query = 'select id,name from #__lms_tests WHERE published =1';

        $db->setQuery($query);
        $rows = $db->loadobjectlist();

        $arr = $this->prepare_for_select($rows,'name');

        return $arr;
    }

    function prepare_for_select($rows,$name) {
        $arr = array();
        for($i=0;$i<count($rows);$i++) {

            $arr[$rows[$i]->id] = $rows[$i]->$name;
        }
        return $arr;
    }

    function getItem() {

        $membership_list_id = JRequest::getVar('id');

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query = "select test_id from #__flexpaper_quiz where membership_list_id = ".$membership_list_id;

        $db->setQuery($query);
        $row = $db->loadResult();

        $data->membership_list_id = $membership_list_id;
        $data->test_id = $row;

        return $data;
    }

}