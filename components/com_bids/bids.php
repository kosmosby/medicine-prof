<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/


defined('_JEXEC') or die('Restricted access');

error_reporting(JDEBUG ? E_ALL & ~E_STRICT : 0);

$oldTimeZone = date_default_timezone_get();
date_default_timezone_set('UTC');

defined('_JEXEC') or die('Restricted access');

//Load Framework
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'thefactory'.DS.'application'.DS.'application.class.php');
$MyApp = JTheFactoryApplication::getInstance(null,true);
$MyApp->Initialize();

require_once JPATH_COMPONENT_SITE.DS.'classes'.DS.'bids_smarty.php';
require_once JPATH_COMPONENT_SITE.DS.'classes'.DS.'bids_smartyview.php';
require_once JPATH_COMPONENT_SITE.DS.'classes'.DS.'bids_model.php';


$MyApp->dispatch();
date_default_timezone_set($oldTimeZone);
