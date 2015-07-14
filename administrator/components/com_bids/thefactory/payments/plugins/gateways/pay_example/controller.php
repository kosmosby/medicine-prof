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

require_once(realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS.'classes'.DS.'gateways.php'));

class Pay_Example extends TheFactoryPaymentGateway
{
    var $name='pay_paypal';
    var $fullname='Paypal Payment Gateway';
    function getPaymentForm($order,$items,$urls,$shipping=null,$tax=null)
    {
        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $params=$model->loadGatewayParams($this->name);

        $result = 'your html for the form payment here';

        return $result;
    }
    function processIPN()
    {
        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $params=$model->loadGatewayParams($this->name);


        $paylog= JTable::getInstance('PaymentLogTable','JTheFactory');
        //paylog data here

        //validate IPN

        $paylog->store();
        return $paylog;
    }
}


