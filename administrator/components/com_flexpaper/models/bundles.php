<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * flexpaperList Model
 */
class flexpaperModelBundles extends JModelList
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

    public function getBundlesList() {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            // Select some fields
            $query = 'SELECT b.id, b.title from #__flexpaper_bundle as a, #__osemsc_acl as b WHERE a.bundle_id = b.id group by b.id';

            $db->setQuery($query);
            $rows = $db->loadObjectList();

        return $rows;
    }
}