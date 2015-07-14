<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


// require helper file
JLoader::register('flexpaperHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'flexpaper.php');


// import joomla controller library
jimport('joomla.application.component.controller');

// Set some global property
$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-48-documents {background-image: url(../media/com_flexpaper/images/documents-48x48.png);}');
$document->addStyleDeclaration('.icon-48-courses {background-image: url(../media/com_flexpaper/images/courses-48x48.png);}');
$document->addStyleDeclaration('.icon-48-bundles {background-image: url(../media/com_flexpaper/images/bundles-48x48.png);}');
$document->addStyleDeclaration('.icon-48-quizes {background-image: url(../media/com_flexpaper/images/quizes-48x48.png);}');
$document->addStyleDeclaration('.icon-48-tests {background-image: url(../media/com_flexpaper/images/tests-48x48.png);}');
$document->addStyleDeclaration('.icon-48-questions {background-image: url(../media/com_flexpaper/images/questions-48x48.png);}');
$document->addStyleDeclaration('.icon-48-certificates {background-image: url(../media/com_flexpaper/images/certificates-48x48.png);}');
$document->addStyleDeclaration('.icon-48-videos {background-image: url(../media/com_flexpaper/images/videos-48x48.png);}');


// Get an instance of the controller prefixed by HelloWorld
$controller = JController::getInstance('flexpaper');

// Perform the Request task


$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();