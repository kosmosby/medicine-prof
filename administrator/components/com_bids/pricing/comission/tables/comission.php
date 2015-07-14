<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: Bids
 * @subpackage: Comission
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTableComission extends JTable {
    var $id=null;
    var $userid=null;
    var $auction_id=null;
    var $bid_id=null;
    var $comission_date=null;
    var $amount=null;
    var $currency=null;

    function __construct(&$db) {
        parent::__construct('#__'.APP_PREFIX.'_pricing_comissions', 'id', $db);
    }
        
}


