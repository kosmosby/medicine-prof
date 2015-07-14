<?php
/**
 * 			JOOOID
 * @version		1.0.0
 * @package		JOOOID for Joomla!
 * @copyright		Copyright (C) 2007-2011 Joomler!.net. All rights reserved.
 * @license		GNU/GPL 2.0 or higher
 * @author		Joomler!.net  joomlers@gmail.com
 * @link			http://www.joomler.net
 */

/**
* @package		Joomla
* @copyright		Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL
*/

defined('_JEXEC') or die;



// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_joooid')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}


function joooid_getController(){
	if (version_compare(JVERSION, '3.0', '<')){
		return JController::getInstance('joooid');
	}
	return JControllerLegacy::getInstance('joooid');
}


JRequest::setVar('view', 'configuration');
// Include dependancies
jimport('joomla.application.component.controller');

$controller	= joooid_getController();
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

?>
