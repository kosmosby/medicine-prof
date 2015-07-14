<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * flexpaperList Model
 */
class flexpaperModelQuizes extends JModelList
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
		$query->select('bundle_id,membership_list_id');
		// From the hello table
		$query->from('#__flexpaper_bundle');
		return $query;
	}

    public function getQuizesList() {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            // Select some fields
            $query = 'SELECT c.id, c.title, b.name from #__flexpaper_quiz as a, #__lms_tests as b, #__osemsc_acl as c WHERE a.test_id = b.id AND c.id = a.membership_list_id /*group by b.id*/';

            $db->setQuery($query);
            $rows = $db->loadObjectList();

        return $rows;
    }
}