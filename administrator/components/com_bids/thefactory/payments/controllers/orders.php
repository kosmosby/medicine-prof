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

class JTheFactoryOrdersController extends JTheFactoryController
{
    var $name='Orders';
    var $_name='Orders';
	function __construct()
	{
       parent::__construct('payments');
       JHtml::addIncludePath($this->basepath.DS.'html');
    }

    function Listing()
    {
        $app = JFactory::getApplication();
        $filter_username = $app->getUserStateFromRequest(APP_EXTENSION . "_payments." . 'filter_username', 'filter_username', '', 'string');
        $cfg = JTheFactoryHelper::getConfig();

        $model= JModel::getInstance('Orders','JTheFactoryModel');
        $rows=$model->getOrdersList($filter_username);

        $view=$this->getView('orders');
        $view->assign('orders',$rows);
        $view->assign('filter_username', $filter_username);
        $view->assign('pagination',$model->get('pagination'));
        $view->assign('cfg', $cfg);
        $view->display('list');
    }
    function ViewDetails()
    {
        $orderid=JRequest::getInt('id');
        $order= JTable::getInstance('OrdersTable','JTheFactory');
        $model= JModel::getInstance('Orders','JTheFactoryModel');

        if (!$orderid || !$order->load($orderid))
        {
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=orders.listing',JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }

        $items=$model->getOrderItems($orderid);
        $user= JFactory::getUser($order->userid);

        $view=$this->getView('orders');
        $view->assign('orderitems',$items);
        $view->assign('order',$order);
        $view->assign('user',$user);
        $view->display('details');

    }
    function Confirm()
    {
        $orderid=JRequest::getVar('id');
        if (is_array($orderid)) $orderid=$orderid[0];

        $order= JTable::getInstance('OrdersTable','JTheFactory');
        $model= JModel::getInstance('Orders','JTheFactoryModel');

        if (!$orderid || !$order->load($orderid))
        {
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=orders.listing',JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }
        if($order->status=='C'){
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=orders.viewdetails&id='.$orderid,JText::_("FACTORY_ORDER_IS_ALREADY_CONFIRMED"));
            return;
        }

        $paylog=$model->confirmOrder($order);

        JTheFactoryEventsHelper::triggerEvent('onPaymentForOrder',array($paylog,$order));

        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=orders.viewdetails&id='.$orderid,JText::_("FACTORY_ORDER_WAS_CONFIRMED"));
    }
    function Cancel()
    {
        $orderid=JRequest::getVar('id');
        if (is_array($orderid)) $orderid=$orderid[0];

        $order= JTable::getInstance('OrdersTable','JTheFactory');

        if (!$orderid || !$order->load($orderid))
        {
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=orders.listing',JText::_("FACTORY_ORDER_DOES_NOT_EXIST"));
            return;
        }
        if($order->status=='X'){
            $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=orders.viewdetails&id='.$orderid,JText::_("FACTORY_ORDER_IS_ALREADY_CANCELLED"));
            return;
        }
        $order->status='X';
        $order->store();

        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=orders.viewdetails&id='.$orderid,JText::_("FACTORY_ORDER_WAS_CANCELLED"));
    }
}
