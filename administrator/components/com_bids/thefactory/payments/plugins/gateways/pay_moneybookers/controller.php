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

class Pay_Moneybookers extends TheFactoryPaymentGateway
{
    var $name='pay_moneybookers';
    var $fullname='Moneybookers Payment Method';
    function getPaymentForm($order,$items,$urls,$shipping=null,$tax=null)
    {
        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $params=$model->loadGatewayParams($this->name);
        $emailaddress=$params->get('email','');


        $result ="<form name='moneybookerForm' action='https://www.moneybookers.com/app/payment.pl' method='post' >";
        $result.="<input type='hidden' name='pay_to_email' value='$emailaddress'>";
        $result.="<input type='hidden' name='recipient_description' value=''>";
        $result.="<input type='hidden' name='logo_url' value=''>";
        $result.="<input type='hidden' name='language' value='en'>";
        $result.="<input type='hidden' name='hide_login' value='0'>";
        $result.="<input type='hidden' name='merchant_fields' value='m_orderid'>";
        $result.="<input type='hidden' name='m_orderid' value='{$order->id}'>";
        $result.="<input type='hidden' name='pay_from_email' value=''>";
        $result.="<input type='hidden' name='transaction_id' value='{$order->id}'>";
        $result.="<input type='hidden' name='return_url' value='".$urls['return_url']."'>";
        $result.="<input type='hidden' name='cancel_url' value='".$urls['cancel_url']."'>";
        $result.="<input type='hidden' name='status_url' value='".$urls['notify_url']."'>";
        $result.="<input type='hidden' name='amount' value='{$order->order_total}'>";
        $result.="<input type='hidden' name='currency' value='{$order->order_currency}'>";
      	$result.="<input type='hidden' name='detail1_description' value='".JText::_('FACTORY_ORDER').$order->id."'>";

        $result.="<input type='image' src='http://www.moneybookers.com/images/logos/checkout_logos/checkout_120x40px.gif' name='submit' alt='".JText::_("FACTORY_BUY_NOW")."' style='margin-left: 30px;'>";
        $result.="</form>";
        return $result;

    }
    function processIPN()
    {
        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $params=$model->loadGatewayParams($this->name);
        $paypal_address=$params->get('paypalemail','');

        $paylog= JTable::getInstance('PaymentLogTable','JTheFactory');
        $date=new JDate();
        $paylog->date=$date->toMySQL();
        $paylog->amount=JRequest::getVar('mb_amount');
        $paylog->currency=JRequest::getVar('mb_currency');
        $paylog->refnumber=JRequest::getVar('mb_transaction_id');
        $paylog->invoice=JRequest::getVar('transaction_id');
        $paylog->ipn_response=print_r($_REQUEST,true);
        $paylog->ipn_ip=$_SERVER['REMOTE_ADDR'];
        $paylog->status='error';
        $paylog->userid=null;
        $paylog->orderid=JRequest::getVar('mb_transaction_id');
        $paylog->payment_method=$this->name;

        $receiver_email = JRequest::getVar('pay_to_email');
        $payment_status = JRequest::getVar('status');
        switch  ($payment_status){
           case "2":
                $paylog->status='ok';
           break;
            case "-1":
            case "-2":
            case "-3":
               $paylog->status='error';
           break;
           default:
           case "0":
                $paylog->status='manual_check';
           break;
        }

        if($receiver_email<>$paypal_address){
            $paylog->status='error';
        }
        $paylog->store();
        return $paylog;
    }

}


