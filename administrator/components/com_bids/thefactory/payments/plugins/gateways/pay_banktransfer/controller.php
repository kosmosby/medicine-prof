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

class Pay_Banktransfer extends TheFactoryPaymentGateway
{
    var $name='pay_banktransfer';
    var $fullname='Bank Transfer Payment Method';

    function getPaymentForm($order,$items,$urls,$shipping=null,$tax=null)
    {
        $Itemid=JRequest::getVar('Itemid');


        $result="<form method='get' action='index.php'>
                    <input type='hidden' name='option' value='".APP_EXTENSION."' />
                    <input type='hidden' name='task' value='orderprocessor.gateway' />
                    <input type='hidden' name='orderid' value='{$order->id}' />
                    <input type='hidden' name='gateway' value='{$this->name}' />
                    <input type='hidden' name='Itemid' value='$Itemid' />

        ";


        $result.="<input type='image' src='".$this->getLogo()."' name='submit' alt='".JText::_("FACTORY_BUY_NOW")."' style='margin-left: 30px;' border='0'>
              </form>
        ";
        return $result;

    }
    function processTask()
    {
        $Itemid=JRequest::getVar('Itemid');
        $orderid=JRequest::getVar('orderid');
        $task2=JRequest::getVar('task2','step1');
        if ($task2=='step2'){
            $this->savePayment();
            $app= JFactory::getApplication();
            $app->redirect('index.php?option='.APP_EXTENSION.'&task=orderprocessor.returning&orderid='.$orderid.'&Itemid='.$Itemid.'&gateway='.$this->name);
            return;
        }else{
            $this->showBankForm();
        }
    }
    function savePayment()
    {
        $orderid=JRequest::getVar('orderid');
        $Itemid=JRequest::getVar('Itemid');

        $order= JTable::getInstance('OrdersTable','JTheFactory');
        if (!$order->load($orderid)){
            $app= JFactory::getApplication();
            $app->redirect('index.php?option='.APP_EXTENSION.'&Itemid='.$Itemid,JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }

        $paylog= JTable::getInstance('PaymentLogTable','JTheFactory');
        $date=new JDate();
        $paylog->date=$date->toMySQL();
        $paylog->amount=$order->order_total;
        $paylog->currency=$order->order_currency;
        $paylog->refnumber=JRequest::getVar('customer_note');;
        $paylog->invoice=$orderid;
        $paylog->ipn_response=print_r($_REQUEST,true);
        $paylog->ipn_ip=$_SERVER['REMOTE_ADDR'];
        $paylog->status='manual_check';
        $paylog->userid=$order->userid;
        $paylog->orderid=$order->id;
        $paylog->payment_method=$this->name;
        $paylog->store();

        $order->paylogid=$paylog->id;
        $order->store();
    }
    function showBankForm()
    {
        $orderid=JRequest::getVar('orderid');
        $Itemid=JRequest::getVar('Itemid');

        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $params=$model->loadGatewayParams($this->name);
        $order= JTable::getInstance('OrdersTable','JTheFactory');
        if (!$order->load($orderid)){
            $app= JFactory::getApplication();
            $app->redirect('index.php?option='.APP_EXTENSION.'&Itemid='.$Itemid,JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }

        $info=$params->get('bank_info','');
        echo "<form method='post' action='index.php'>
					<table>
					    <tr>
					        <td><h2>".JText::_("FACTORY_ORDER")." #{$order->id} - {$order->order_total} {$order->order_currency}</h2></td>
					    </tr>
						<tr>
							<td>
								<strong>".JText::_("FACTORY_PAYMENT_INFORMATION")."</strong><br />
								$info
							</td>
						</tr>
						<tr>
							<td>
								<strong>".JText::_("FACTORY_CUSTOMER_NOTE")."</strong><br />
								<textarea name='customer_note' style='width:400px; height:200px;'></textarea>
							</td>
						</tr>
						<tr>
							<td><input type='submit' value='".JText::_("FACTORY_BUY_NOW")."' /></td>
						</tr>
					</table>
                    <input type='hidden' name='option' value='".APP_EXTENSION."' />
                    <input type='hidden' name='task' value='orderprocessor.gateway' />
                    <input type='hidden' name='task2' value='step2' />
                    <input type='hidden' name='orderid' value='{$order->id}' />
                    <input type='hidden' name='gateway' value='{$this->name}' />
                    <input type='hidden' name='Itemid' value='$Itemid' />
                </form>
        ";

    }
}


