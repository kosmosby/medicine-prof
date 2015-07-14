<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by flexpaper
$controller = JControllerLegacy::getInstance('flexpaper');

// Perform the Request task
$input = JFactory::getApplication()->input;


if(JRequest::getVar('view') == 'membersarea' && !JRequest::getVar('task')) {
    JRequest::setVar('task','membersarea');
}
if(JRequest::getVar('view') == 'certificates' && !JRequest::getVar('task')) {
    JRequest::setVar('task','certificates');
}

if(JRequest::getVar('view') == 'catalogcategories' && !JRequest::getVar('task')) {
    JRequest::setVar('task','catalogcategories');
}

if(JRequest::getVar('view') == 'videoconsultant' && !JRequest::getVar('task')) {
    JRequest::setVar('task','videoconsultant');
}

if(JRequest::getVar('view') == 'pay' && !JRequest::getVar('task')) {
    JRequest::setVar('task','pay');
}


if(JRequest::getVar('view') == 'courses') {

    $menu =   &JSite::getMenu();
    $params = $menu->getParams(JRequest::getVar('Itemid'));

    $all_courses = $params->get('all_courses');

    if($all_courses) {
        JRequest::setVar('task','mydocs');
    }
}
if(JRequest::getVar('view') == 'flexpapers' && !JRequest::getVar('task')) {
    JRequest::setVar('task','list_docs');
}
if(JRequest::getVar('view') == 'quizes' && !JRequest::getVar('task')) {
    JRequest::setVar('task','quizes');
}

$controller->execute($input->getCmd('task', 'courses_list'));

// Redirect if set by the controller
$controller->redirect();