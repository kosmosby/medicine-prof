<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * HelloWorldList Model
 */
class SocialLoginModelSocialLogin extends JModelList
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
    /*
	protected function getListQuery()
	{
		// Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('id,greeting');
		// From the hello table
		$query->from('#__helloworld');
		return $query;
	}
    */


    function saveSettings() {

        $db		= $this->getDbo();

        $settings = JRequest::getVar('oa_social_login_settings');

        $settings['providers'] = serialize($settings['providers']);

        $sql = "DELETE FROM #__sociallogin_settings WHERE setting <> 'oa_social_login_api_settings_verified'";
        $db->setQuery($sql);
        $db->query();

        foreach ($settings as $k=>$v) {
            $sql = "INSERT INTO #__sociallogin_settings ( setting, value )" .
                " VALUES ( ".$db->Quote($k).", ".$db->Quote($v)." )";
            $db->setQuery($sql);
            $db->query();
        }
    }

    function getSettings() {

        $db		= $this->getDbo();

        $sql = "SELECT * FROM #__sociallogin_settings";
        $db->setQuery($sql);
        $rows = $db->LoadAssocList();

        $new_arr = array();
        for($i=0;$i<count($rows);$i++) {
            $new_arr[$rows[$i]['setting']] = $rows[$i]['value'];
        }

        @$new_arr['providers'] = unserialize($new_arr['providers']);

        return $new_arr;
    }

    function set_option($option, $value) {

        $db		= $this->getDbo();

        $sql = "DELETE FROM #__sociallogin_settings WHERE setting = '".$option."'";
        $db->setQuery($sql);
        $db->query();

        $sql = "INSERT INTO #__sociallogin_settings ( setting, value )" .
            " VALUES ( ".$db->Quote($option).", ".$db->Quote($value)." )";
        $db->setQuery($sql);
        $db->query();

    }
}



