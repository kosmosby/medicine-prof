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

class JTheFactoryOrderProcessorController extends JControllerLegacy
{
    var $name='OrderProcesor';
    var $_name='OrderProcesor';
	function __construct($config=array())
	{
        $MyApp= JTheFactoryApplication::getInstance();
        $lang= JFactory::getLanguage();
        $lang->load('thefactory.payments');

        $config['view_path']=$MyApp->app_path_front.'payments'.DS."views";

        parent::__construct($config);
        JTheFactoryHelper::modelIncludePath('payments');
        JTheFactoryHelper::tableIncludePath('payments');

    }
    function getView( $name = '', $type = 'html', $prefix = '', $config = array() )
    {
        $MyApp= JTheFactoryApplication::getInstance();
        $config['template_path']=$MyApp->app_path_front.'payments'.DS."views".DS.strtolower($name).DS."tmpl";
        return parent::getView($name,$type,'JTheFactoryViewProcessor',$config);
    }

    function checkout()
    {
        $orderid=JRequest::getInt('orderid');
        $Itemid=JRequest::getInt('Itemid');
        $user= JFactory::getUser();

        if (!$orderid){
            $this->setRedirect('index.php?option='.APP_EXTENSION,JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }
        $order= JTable::getInstance('OrdersTable','JTheFactory');
        if (!$order->load($orderid)){
            $this->setRedirect('index.php?option='.APP_EXTENSION,JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }
        if($user->id!==$order->userid){
            $this->setRedirect('index.php?option='.APP_EXTENSION,JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }
        if($order->status!='P'){
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=orderprocessor.details&orderid='.$orderid.'&Itemid='.$Itemid,JText::_("FACTORY_ORDER_IS_NO_LONGER_PENDING"));
            return;
        }

        $ordermodel=JModelLegacy::getInstance('Orders','JTheFactoryModel');
        $model= JModelLegacy::getInstance('Gateways','JTheFactoryModel');
        $items=$model->getGatewayList(true);
        $order_items=$ordermodel->getOrderItems($orderid);
        $urls['return_url']=JURI::root().'index.php?option='.APP_EXTENSION.'&task=orderprocessor.details&orderid='.$orderid.'&Itemid='.$Itemid;
        $urls['notify_url']=JURI::root().'index.php?option='.APP_EXTENSION.'&task=orderprocessor.ipn&orderid='.$orderid;
        $urls['cancel_url']=JURI::root().'index.php?option='.APP_EXTENSION.'&task=orderprocessor.cancel&orderid='.$orderid.'&Itemid='.$Itemid;
        $payment_forms=array();
        $payment_gateways=array();
        foreach($items as $item){
            $gw=$model->getGatewayObject($item->classname);
            $payment_forms[]=$gw->getPaymentForm($order,$order_items,$urls);
            $payment_gateways[]=$gw;
        }

        $view=$this->getView('checkout');
        $view->assign('payment_forms',$payment_forms);
        $view->assign('payment_gateways',$payment_gateways);
        $view->assign('order_items',$order_items);
        $view->assign('order',$order);
        $view->display();

    }
    function details()
    {
        $orderid=JRequest::getInt('orderid');
        $Itemid=JRequest::getInt('Itemid');
        $user= JFactory::getUser();

        if (!$orderid){
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&Itemid='.$Itemid,JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }
        $order= JTable::getInstance('OrdersTable','JTheFactory');
        if (!$order->load($orderid)){
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&Itemid='.$Itemid,JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }
        if($user->id!==$order->userid){
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&Itemid='.$Itemid,JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }

        $ordermodel=JModelLegacy::getInstance('Orders','JTheFactoryModel');
        $model= JModelLegacy::getInstance('Gateways','JTheFactoryModel');
        $items=$model->getGatewayList(true);
        $order_items=$ordermodel->getOrderItems($orderid);
        $payment_forms=array();
        $payment_gateways=array();
        foreach($items as $item){
            $gw=$model->getGatewayObject($item->classname);
            $urls=array();
            $urls['return_url']=JURI::root().'index.php?option='.APP_EXTENSION.'&task=orderprocessor.returning&orderid='.$orderid.'&Itemid='.$Itemid.'&gateway='.$gw->name;
            $urls['notify_url']=JURI::root().'index.php?option='.APP_EXTENSION.'&task=orderprocessor.ipn&orderid='.$orderid.'&gateway='.$gw->name;
            $urls['cancel_url']=JURI::root().'index.php?option='.APP_EXTENSION.'&task=orderprocessor.cancel&orderid='.$orderid.'&Itemid='.$Itemid.'&gateway='.$gw->name;
            $payment_forms[]=$gw->getPaymentForm($order,$order_items,$urls);
            $payment_gateways[]=$gw;
        }

        $view=$this->getView('details');
        $view->assign('payment_forms',$payment_forms);
        $view->assign('payment_gateways',$payment_gateways);
        $view->assign('order_items',$order_items);
        $view->assign('order',$order);
        $view->display();
    }
    function ipn()
    {
        ob_clean();
        $gateway_name=JRequest::getVar('gateway');
        $orderid=JRequest::getVar('orderid');

        $model= JModelLegacy::getInstance('Gateways','JTheFactoryModel');
        $gw=$model->getGatewayObject($gateway_name);
        $order= JTable::getInstance('OrdersTable','JTheFactory');
        if (!is_object($gw)){
            //error
            exit;
        }
        $paylog=$gw->processIPN();

        if(!$paylog->orderid) {
            $paylog->orderid=$orderid;
            $paylog->store();
        }


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
        if (floatval($order->order_total)<>floatval($paylog->amount)|| strtoupper($order->order_currency)<>strtoupper($paylog->currency)){
            $paylog->status='error';
        }
        $paylog->userid=$order->userid;
        $paylog->store();

        $date=new JDate();
        $order->modifydate=$date->toMySQL();
        if ($paylog->status=='ok')
            $order->status='C';
        $order->paylogid=$paylog->id;
        $order->store();
        JTheFactoryEventsHelper::triggerEvent('onPaymentForOrder',array($paylog,$order));
        exit;
    }
    function Cancel()
    {
        $orderid=JRequest::getVar('orderid');
        $Itemid=JRequest::getVar('Itemid');
        $order= JTable::getInstance('OrdersTable','JTheFactory');
        if (!$order->load($orderid)){
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&Itemid='.$Itemid,JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }
        $order->status='X';
        $order->store();
        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=orderprocessor.details&orderid='.$orderid.'&Itemid='.$Itemid,JText::_("FACTORY_ORDER_CANCELLED"));
    }
    function Returning()
    {
        $orderid=JRequest::getVar('orderid');
        $Itemid=JRequest::getVar('Itemid');
        $order= JTable::getInstance('OrdersTable','JTheFactory');
        if (!$order->load($orderid)){
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&Itemid='.$Itemid,JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }
        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=orderprocessor.details&orderid='.$orderid.'&Itemid='.$Itemid,JText::_("FACTORY_PAYMENT_WILL_BE_PROCESSED_SHORTLY"));
    }
    function Gateway()
    {
        $gateway_name=JRequest::getVar('gateway');
        $Itemid=JRequest::getVar('Itemid');
        $model= JModelLegacy::getInstance('Gateways','JTheFactoryModel');
        $gw=$model->getGatewayObject($gateway_name);
        if (!is_object($gw)){
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&Itemid='.$Itemid,JText::_("FACTORY_PAYMENT_GATEWAY_DOES_NOT_EXIST"));
            return;
        }
        $gw->processTask();
    }

}
