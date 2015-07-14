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

class JTheFactoryEventPrice_Featured extends JTheFactoryEvents
{
    function getItemName()
    {
        return "featured";
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
        if(!in_array($task, array('viewbids','details') ) )
            return;

        $auction= $smarty->get_template_vars('auction');
        $curent_info=$smarty->get_template_vars('payment_items_header');

        if ($auction->close_offer || !$auction->published || !$auction->isMyAuction() || $auction->featured=='featured')
            return;

        $orderitems=BidsHelperAuction::getOrderItemsForAuction($auction->id,self::getItemName());
        if (count($orderitems)){
            $priceinfo=JText::_("COM_BIDS_PAYMENT_FOR_FEATURE_AUCTION_IS_PENDING");
            foreach ($orderitems as $item)
                if ($item->status=='C')
                    return;//Auction was payed for!
            $smarty->assign('payment_items_header',$curent_info.$priceinfo);
            return;
        }

        $model=self::getModel();
        $price=$model->getItemPrice($auction->cat);

        if (!floatval($price->price)){
            return;
        }else{
            
            $priceinfo="<a href='".BidsHelperRoute::getFeaturedRoute($auction->id)."'>";
            $priceinfo.=JText::_("COM_BIDS_UPGRADE_TO_FEATURED_FOR").number_format($price->price,2)." ".$price->currency;
            $priceinfo.="</a><br/>";
        }
        $smarty->assign('payment_items_header',$curent_info.$priceinfo);

    }
    function onBeforeExecuteTask(&$stopexecution)
    {
        $app = JFactory::getApplication();
        if ($app->isAdmin()) {
            return;
        }

        $task = JRequest::getCmd('task','listauctions');
        if ($task=='setfeatured'){
            $id = JRequest::getInt("id");
            $auction = JTable::getInstance('auction');
            $app= JFactory::getApplication();
            if (!$auction->load($id)){
                JError::raiseWarning( 550, JText::_("COM_BIDS_ERROR_LOADING_AUCTION_ID").$id  );
                $app->redirect(BidsHelperRoute::getAuctionDetailRoute($auction));
                return;
            }
            if(!$auction->isMyAuction()){
                JError::raiseWarning(501,JText::_("COM_BIDS_THIS_AUCTION_DOES_NOT_BELONG_TO_YOU"));
                $app->redirect(BidsHelperRoute::getAuctionDetailRoute($auction));
                return;
            }

            $model=self::getModel();
            $modelorder=JTheFactoryPricingHelper::getModel('orders');
            $modelbalance=JTheFactoryPricingHelper::getModel('balance');

            $price=$model->getItemPrice();
            $balance=$modelbalance->getUserBalance();
            $item=$model->getOderitem($auction);


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
            $auction->featured='featured';
            $auction->store();
            $app->redirect(BidsHelperRoute::getAuctionDetailRoute($auction));
            return;
        }

    }
    function onPaymentForOrder($paylog,$order)
    {

        if (!$order->status=='C') return;
        $modelorder=JTheFactoryPricingHelper::getModel('orders');
        $items=$modelorder->getOrderItems($order->id,self::getItemName());
        if (!is_array($items)||!count($items)) return; //no Listing items in order

        $date=new JDate();
        $auction = JTable::getInstance('auction');
        foreach($items as $item){
            if (!$item->iteminfo) continue; //AuctionID is stored in iteminfo
            if ($item->itemname!=self::getItemName()) continue;
            if(!$auction->load($item->iteminfo)) continue; //auction no longer exists
            $auction->modified=$date->toMySQL();
            $auction->featured='featured';
            $auction->store();

        }

    }
}
