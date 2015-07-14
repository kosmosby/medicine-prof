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

//Load Framework
jimport('joomla.form.helper');

jimport('joomla.application.component.model');
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_categories'.DS.'tables');
JModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_categories'.DS.'models');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'thefactory'.DS.'application'.DS.'application.class.php');
$MyApp=JTheFactoryApplication::getInstance();
// Include dependencies
$MyApp->Initialize();

if(!JFolder::exists(AUCTION_PICTURES_PATH)) JFolder::create(AUCTION_PICTURES_PATH);
if(!JFolder::exists(AUCTION_UPLOAD_FOLDER)) JFolder::create(AUCTION_UPLOAD_FOLDER);
if(!JFolder::exists(AUCTION_TEMPLATE_CACHE)) JFolder::create(AUCTION_TEMPLATE_CACHE);

$MyApp->dispatch();
