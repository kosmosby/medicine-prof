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

class JTheFactoryPaymentLogTable extends JTable {
    var $id=null;
    var $date=null;
    var $amount=null;
    var $currency=null;
    var $refnumber=null;
    var $invoice=null;
    var $ipn_response=null;
    var $ipn_ip=null;
    var $status=null;
    var $userid=null;
    var $orderid=null;
    var $payment_method=null;

    function __construct( &$db ) {
        parent::__construct( '#__'.APP_PREFIX.'_payment_log', 'id', $db );
    }

}
