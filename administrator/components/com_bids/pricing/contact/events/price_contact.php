<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: Bids
 * @subpackage: Pay per contact
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryEventPrice_Contact extends JTheFactoryEvents
{
    function getItemName()
    {
        return "contact";
    }
    function getContext()
    {
        return APP_PREFIX.".".self::getItemName();
    }
    function &getModel()
    {
        jimport('joomla.application.component.model');
        JModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'pricing'.DS.self::getItemName().DS.'models');
        $model= JModel::getInstance(self::getItemName(),'JBidPricingModel');
        return $model;
    }


    function onBeforeDisplay($task,$smarty)
    {
        if (!is_object($smarty))
            return;

        $task=strtolower($task);

        if(!in_array($task, array('userdetails','showusers') ) )
            return;
        
        $Itemid=JRequest::getInt('Itemid');
        $user= JFactory::getUser();
        $model=self::getModel();
        $price=$model->getItemPrice();

        $url_buy = 'index.php?option='.APP_EXTENSION.'&task=buy_contact&id=%s&Itemid='.$Itemid;
        if (isset($smarty->_tpl_vars["user"]) && is_object($smarty->_tpl_vars["user"]))
        {
            $userid = empty($smarty->_tpl_vars["user"]->userid) ? $smarty->_tpl_vars["user"]->id : $smarty->_tpl_vars["user"]->userid;
            if ($user->id!==$userid && !$model->checkContact($userid))
            {
                $url=sprintf($url_buy,$userid);
    			$smarty->_tpl_vars["user"]->name = JText::_("COM_BIDS_HIDDEN")."&nbsp;<a href='$url'>".JText::_("COM_BIDS_BUY_THIS_CONTACT_FOR")." ".number_format($price->price,2)." ".$price->currency."</a>";
    			$smarty->_tpl_vars["user"]->surname = "<span class='bid_hidden'>".JText::_("COM_BIDS_HIDDEN")."</span>";
    			$smarty->_tpl_vars["user"]->phone = "<span class='bid_hidden'>".JText::_("COM_BIDS_HIDDEN")."</span>";
    			$smarty->_tpl_vars["user"]->address = "<span class='bid_hidden'>".JText::_("COM_BIDS_HIDDEN")."</span>";
                $smarty->_tpl_vars["user"]->city = "<span class='bid_hidden'>".JText::_("COM_BIDS_HIDDEN")."</span>";
    			$smarty->_tpl_vars["user"]->paypalemail = "<span class='bid_hidden'>".JText::_("COM_BIDS_HIDDEN")."</span>";
            }
        }
        
        if(isset($smarty->_tpl_vars["users"]) && is_array($smarty->_tpl_vars["users"]))
        {
            for($i=0;$i<count($smarty->_tpl_vars["users"]);$i++)
            {
                $userid = empty($smarty->_tpl_vars["users"][$i]->userid ) ? $smarty->_tpl_vars["users"][$i]->id : $smarty->_tpl_vars["users"][$i]->userid;
                if ($user->id!==$userid && !$model->checkContact($userid))
                {
                    $url=sprintf($url_buy,$userid);
        			$smarty->_tpl_vars["users"][$i]->name = JText::_("COM_BIDS_HIDDEN")."&nbsp;<a href='$url'>".JText::_("COM_BIDS_BUY_THIS_CONTACT_FOR")." ".number_format($price->price,2)." ".$price->currency."</a>";
        			$smarty->_tpl_vars["users"][$i]->surname = "<span class='bid_hidden'>".JText::_("COM_BIDS_HIDDEN")."</span>";
        			$smarty->_tpl_vars["users"][$i]->phone = "<span class='bid_hidden'>".JText::_("COM_BIDS_HIDDEN")."</span>";
        			$smarty->_tpl_vars["users"][$i]->address = "<span class='bid_hidden'>".JText::_("COM_BIDS_HIDDEN")."</span>";
                    $smarty->_tpl_vars["users"][$i]->city = "<span class='bid_hidden'>".JText::_("COM_BIDS_HIDDEN")."</span>";
        			$smarty->_tpl_vars["users"][$i]->paypalemail = "<span class='bid_hidden'>".JText::_("COM_BIDS_HIDDEN")."</span>";
                }
           }
        }   
    }
    function onBeforeExecuteTask(&$stopexecution)
    {
        $app = JFactory::getApplication();
        if ($app->isAdmin()) {
            return;
        }

        $task = JRequest::getCmd('task','listauctions');
        if ($task=='buy_contact'){
            $user= JFactory::getUser();
            $app= JFactory::getApplication();
            $id = JRequest::getInt("id");
            $model=self::getModel();

            if ($user->id==$id && $model->checkContact($id))
            {
                JError::raiseWarning(501,JText::_("COM_BIDS_CONTACT_IS_ALREADY_PURCHASED"));
                $app->redirect(BidsHelperRoute::getUserdetailsRoute($id,false));
                return;
            }
            $modelorder=JTheFactoryPricingHelper::getModel('orders');
            $modelbalance=JTheFactoryPricingHelper::getModel('balance');

            $price=$model->getItemPrice();
            $balance=$modelbalance->getUserBalance();
            $item=$model->getOderitem($id);

            if (BidsHelperPrices::comparePrices($price,array("price"=>$balance->balance,"currency"=>$balance->currency))>0)
            {
                $order=$modelorder->createNewOrder($item,$price->price,$price->currency,null,'P');
                $app->redirect(BidsHelperRoute::getCheckoutRoute($order->id,false));
                return;
            }
            //get funds from account, create confirmed order
            $balance_minus=BidsHelperPrices::convertCurrency($price->price,$price->currency,$balance->currency);
            $modelbalance->decreaseBalance($balance_minus);

            $order=$modelorder->createNewOrder($item,$price->price,$price->currency,null,'C');
            $model->addContact($id,$order->userid);
            $app->redirect(BidsHelperRoute::getUserdetailsRoute($id));
            return;
            
        }
    }
    function onPaymentForOrder($paylog,$order)
    {
        if ($order->status!='C') return;
        $modelorder=JTheFactoryPricingHelper::getModel('orders');
        $items=$modelorder->getOrderItems($order->id,self::getItemName());
        if (!is_array($items)||!count($items)) return; //no Listing items in order

        $model=self::getModel();
        $date=new JDate();
        foreach($items as $item){
            if (!$item->iteminfo) continue; //AuctionID is stored in iteminfo
            if ($item->itemname!=self::getItemName()) continue;

            $model->addContact($item->iteminfo,$order->userid);
        }

    }
}
