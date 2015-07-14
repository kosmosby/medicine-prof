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


defined( '_JEXEC' ) or die( 'Restricted access' );

define('AUCTION_MEDIA',JURI::root().'media/com_bids/');
define('AUCTION_PICTURES', AUCTION_MEDIA.'images/');
define('AUCTION_PICTURES_PATH',JPATH_ROOT.DS.'media'.DS.'com_bids'.DS.'images'.DS);
define('AUCTION_UPLOAD_FOLDER',JPATH_ROOT.DS.'media'.DS.'com_bids'.DS.'files'.DS);
define('AUCTION_TEMPLATE_CACHE', JPATH_ROOT.DS.'cache'.DS.'com_bids'.DS.'templates');
define('AUCTION_BACKUPS_PATH',JPATH_COMPONENT_ADMINISTRATOR.DS.'backups');

define('AUCTION_TYPE_PUBLIC', 1 );
define('AUCTION_TYPE_PRIVATE', 2 );
define('AUCTION_TYPE_BIN_ONLY', 3 );
