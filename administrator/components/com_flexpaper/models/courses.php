<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * flexpaperList Model
 */
class flexpaperModelCourses extends JModelList
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
        $query->select('a.id, b.title, d.title as category');
        // From the hello table

        $query->from('#__osemsc_acl as b, #__flexpaper_content as a');
        $query->LeftJoin('#__categories as d ON a.catid = d.id');
        $query->where('a.membership_list_id = b.id');

        return $query;
	}
}