<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by Social_login
$controller = JController::getInstance('SocialLogin');

// Perform the Request task
$controller->execute(JRequest::getCmd('task', 'display'));


// Redirect if set by the controller
$controller->redirect();