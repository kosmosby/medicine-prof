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

class Pay_2checkout extends TheFactoryPaymentGateway
{
    var $name='pay_2checkout';
    var $fullname='2checkout Payment Method';
    function  showAdminForm()
    {
        $app=JFactory::getApplication();
        $app->enqueueMessage(JText::_("FACTORY_NOTE_2CHECKOUT_DOES_NOT_SUPPORT"));
        parent::showAdminForm();
    }
    function getPaymentForm($order,$items,$urls,$shipping=null,$tax=null)
    {

        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $params=$model->loadGatewayParams($this->name);

        $test_mode=$params->get('test_mode',0);
        $x_login=$params->get('x_login','');
        
        $result ="<form name='check2outForm' action='https://www.2checkout.com/checkout/purchase' method='post'>";
			$result.="<input type='hidden' name='x_login' value='$x_login' />";
            if ($test_mode)
                    $result.="<input type='hidden' name='demo' value='Y' />";
			$result.="<input type='hidden' name='x_email_merchant' value='TRUE' />";
			$result.="<input type='hidden' name='list_currency' value='{$order->order_currency}' />
			<input type='hidden' name='x_invoice_num' value='{$order->id}' />
			<input type='hidden' name='merchant_order_id' value='{$order->id}' />
			<input type='hidden' name='x_receipt_link_url' value='".$urls['notify_url']."' />";
            
        $result.="<input type='hidden' name='x_amount' value='{$order->order_total}' />";
        $result.="<input type='image' name='submit' src='https://www.2checkout.com/images/buy_logo.gif' border='0' alt='Pay 2checkout' />";
        $result.="</form>";
        return $result;
        
    }
    function processIPN()
    {
        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $params=$model->loadGatewayParams($this->name);

        $test_mode=$params->get('test_mode',0);
        $x_login=$params->get('x_login','');

        $paylog= JTable::getInstance('PaymentLogTable','JTheFactory');
        $date=new JDate();
        $paylog->date=$date->toMySQL();
        $paylog->amount=JRequest::getVar('x_amount');
        $paylog->currency='';//JRequest::getVar('');
        $paylog->refnumber=JRequest::getVar('x_trans_id');
        $paylog->invoice=JRequest::getVar('x_invoice_num');
        $paylog->ipn_response=print_r($_REQUEST,true);
        $paylog->ipn_ip=$_SERVER['REMOTE_ADDR'];
        $paylog->status='error';
        $paylog->userid=null;
        $paylog->orderid=JRequest::getVar('x_invoice_num');
        $paylog->payment_method=$this->name;

        $receiver_email = JRequest::getVar('receiver_email');
        $payment_status = JRequest::getVar('x_2checked');
        if ($payment_status=='Y')
                $paylog->status='ok';
        else
               $paylog->status='error';

        $paylog->store();
        return $paylog;
        
    }
    
}
