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

class JTheFactoryPaymentsController extends JTheFactoryController
{
    var $name='Payments';
    var $_name='Payments';
	function __construct()
	{
       parent::__construct();
       JHtml::addIncludePath($this->basepath.DS.'html');
    }
    function Listing()
    {

        $app = JFactory::getApplication();
        $filter_username = $app->getUserStateFromRequest(APP_EXTENSION . "_payments." . 'filter_username', 'filter_username', '', 'string');
        $cfg = JTheFactoryHelper::getConfig();

        $model = JModel::getInstance('Payments', 'JTheFactoryModel');
        $rows = $model->getPaymentsList($filter_username);

        $view=$this->getView('paylog');
        $view->assign('payments',$rows);
        $view->assign('filter_username', $filter_username);
        $view->assign('pagination',$model->get('pagination'));
        $view->assign('cfg', $cfg);
        $view->display('list');
    }
    function ViewDetails()
    {
        $paylogid=JRequest::getInt('id');
        $paylog= JTable::getInstance('PaymentLogTable','JTheFactory');

        if (!$paylogid|| !$paylog->load($paylogid))
        {
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=payments.listing',JText::_("FACTORY_PAYMENT_DOES_NOT_EXIST"));
            return;
        }

        $user= JFactory::getUser($paylog->userid);

        $view=$this->getView('paylog');
        $view->assign('paylog',$paylog);
        $view->assign('user',$user);
        $view->display('details');

    }
    function Confirm()
    {
        $paylogid=JRequest::getInt('id');
        $paylog= JTable::getInstance('PaymentLogTable','JTheFactory');

        if (!$paylogid|| !$paylog->load($paylogid))
        {
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=payments.listing',JText::_("FACTORY_PAYMENT_DOES_NOT_EXIST"));
            return;
        }

        if ($paylog->status=='ok')
        {
            $this->setRedirect('index.php?  option='.APP_EXTENSION.'&task=payments.viewdetails&id='.$paylogid,JText::_("FACTORY_PAYMENT_ALREADY_CONFIRMED"));
            return;
        }
        $paylog->status='ok';
        $paylog->store();

        $order= JTable::getInstance('OrdersTable','JTheFactory');

        if (!$paylog->orderid){
            //Still no order attached to this payment?
            $error=JText::_('FACTORY_PAYMENT_DID_NOT_MATCH_AN_ORDER');
            JTheFactoryEventsHelper::triggerEvent('onPaymentIPNError',array($paylog,$error));
            exit;
        }

        if (!$order->load($paylog->orderid)){
            //Still no order attached to this payment?
            $error=JText::_('FACTORY_PAYMENT_DID_NOT_MATCH_AN_ORDER');
            JTheFactoryEventsHelper::triggerEvent('onPaymentIPNError',array($paylog,$error));
            exit;
        }
        $date=new JDate();
        $order->modifydate=$date->toMySQL();
        if ($paylog->status=='ok')
            $order->status='C';
        $order->paylogid=$paylog->id;
        $order->store();
        JTheFactoryEventsHelper::triggerEvent('onPaymentForOrder',array($paylog,$order));

        $this->setRedirect('index.php?  option='.APP_EXTENSION.'&task=payments.viewdetails&id='.$paylogid,JText::_("FACTORY_PAYMENT_CONFIRMED"));


    }
    function NewPayment()
    {
        $view=$this->getView('paylog');
        $view->display('new');
    }
    function SavePayment()
    {
        $amount=JRequest::getFloat('amount');
        $currency=JRequest::getVar('currency');
        $refnumber=JRequest::getVar('refnumber');
        $paymentstatus=JRequest::getString('paymentstatus');
        $userid=JRequest::getInt('userid');

        $paylog= JTable::getInstance('PaymentLogTable','JTheFactory');
        $date=new JDate();
        $paylog->date=$date->toMySQL();
        $paylog->amount=$amount;
        $paylog->currency=$currency;
        $paylog->refnumber=$refnumber;
        $paylog->invoice=-1;
        $paylog->status=$paymentstatus;
        $paylog->userid=$userid;
        $paylog->payment_method='manual_payment';
        $paylog->store();

        if ($paylog->status=='ok'){
            $model= JModel::getInstance('Balance','JTheFactoryModel');
            $model->increaseBalance($amount,$userid);
        }
        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=payments.listing',JText::_("FACTORY_PAYMENT_SAVED_USER_ACCOUNT_CREDITED"));
    }
}
