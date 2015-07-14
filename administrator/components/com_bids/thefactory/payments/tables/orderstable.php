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


class JTheFactoryOrdersTable extends JTable {
    var $id=null;
    var $orderdate=null;
    var $modifydate=null;
    var $userid=null;
    var $order_total=null;
    var $order_currency=null;
    var $status=null; // C=confirmed, P=pending, X=cancelled, R=refunded
    var $paylogid=null;
    var $params=null;
    function __construct( &$db ) {
        parent::__construct( '#__'.APP_PREFIX.'_payment_orders', 'id', $db );
    }

}
