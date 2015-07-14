<?php
// no direct access

defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.view');

class AwdjomalbumViewcreatealbum extends JViewLegacy {

	function display($tpl = null) {

		require_once(JPATH_SITE . DS . 'components'.DS.'com_awdjomalbum'. DS.'js' . DS . 'include.php');			

        parent::display($tpl);

    }

}

?>