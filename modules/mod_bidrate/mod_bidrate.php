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

// no direct access

defined('_JEXEC') or die('Restricted access');

$my = JFactory::getUser();
if($my->guest) {
    return;
}

require_once (JPATH_ROOT . DS . "components" . DS . "com_bids" . DS . 'defines.php');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

JHtml::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'htmlelements');
require_once (JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'tools.php');

$type   = intval( $params->get( 'type', 1 ) );

$rows = mod_bidsrateHelper::getRatings($type);

require(JModuleHelper::getLayoutPath('mod_bidrate'));