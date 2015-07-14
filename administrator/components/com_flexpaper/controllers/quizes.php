<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');
 
/**
 * flexpapers Controller
 */
class flexpaperControllerquizes extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 * @since	2.5
	 */
	public function getModel($name = 'courses', $prefix = 'coursesModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

    function delete() {
        $db = JFactory::getDBO();

        $cids = JRequest::getVar('cid');

        $query = $db->getQuery(true);
        $query = "delete from  #__flexpaper_quiz where membership_list_id IN ( ".implode(',',$cids).")";

        $db->setQuery($query);
        $db->query();

        $this->setRedirect(JRoute::_('index.php?option=com_flexpaper&task=quizes&view=quizes', false));

    }

}