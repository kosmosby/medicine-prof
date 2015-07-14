<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: payments
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');


class JTheFactoryBalanceTable extends JTable {
    var $id=null;
    var $userid=null;
    var $balance=null;
    var $currency=null;
    function __construct( &$db ) {
        parent::__construct( '#__'.APP_PREFIX.'_payment_balance', 'userid', $db );
    }
    function addBalance($userid,$amount=null,$currency=null)
    {
        $db= $this->getDbo();
        $db->setQuery("insert into `".$this->getTableName()."` (userid,balance,currency) values(
            ".$db->quote($userid).",
            ".$db->quote($amount).",
            ".$db->quote($currency)."
        ) ");
        $db->query();
    }
}
