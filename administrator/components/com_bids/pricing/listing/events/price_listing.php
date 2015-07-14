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
 * @subpackage: Pay per listing
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryEventPrice_Listing extends JTheFactoryEvents
{
    function getItemName()
    {
        return "listing";
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
        if(!in_array($task, array('republish','new','form','edit','editauction','newauction') ) )
            return;

        $id = JRequest::getInt("id");
        $curent_info=$smarty->get_template_vars('payment_items_header');

        if (in_array($task, array('form','edit','editauction')) && $id)
        {
            $orderitems=BidsHelperAuction::getOrderItemsForAuction($id,self::getItemName());
            if (count($orderitems)){
                $priceinfo=JText::_("COM_BIDS_PAYMENT_FOR_THIS_AUCTION_IS_PENDING");
                foreach ($orderitems as $item)
                    if ($item->status=='C')
                        return;//Auction was payed for!
                $smarty->assign('payment_items_header',$curent_info.$priceinfo);
                return;
            }
        }
        $auction= $smarty->get_template_vars('auction');

        $model=self::getModel();
        $price=$model->getItemPrice( isset($auction->cat) ? $auction->cat : 0 );

        $modelbalance=JTheFactoryPricingHelper::getModel('balance');
        $balance=$modelbalance->getUserBalance();
        if (!floatval($price->price)){
            $priceinfo=JText::_("COM_BIDS_PUBLISHING_IN_THIS_CATEGORY_IS_FREE");
        }else{
            $priceinfo=JText::_("COM_BIDS_YOUR_CURRENT_BALANCE_IS").number_format($balance->balance,2)." ".$balance->currency."<br/>";
            $priceinfo.=JText::_("COM_BIDS_PUBLISHING_IN_THIS_CATEGORY_WILL_COST").number_format($price->price,2)." ".$price->currency;
            if($balance->currency && $balance->currency<>$price->currency) $priceinfo.=" (".
                number_format(BidsHelperPrices::convertCurrency($price->price,$price->currency,$balance->currency),2)." ".$balance->currency.")";
            $priceinfo.="<br/>";
            if (BidsHelperPrices::comparePrices($price,array("price"=>$balance->balance,"currency"=>$balance->currency))>0)
                $priceinfo.=JText::_("COM_BIDS_YOUR_FUNDS_ARE_UNSUFFICIENT")."<br/>";
        }
        $smarty->assign('payment_items_header',$curent_info.$priceinfo);

    }
    function onAfterSaveAuctionSuccess($auction)
    {
        if (!$auction->published) return; //not published yet

        $orderitems=BidsHelperAuction::getOrderItemsForAuction($auction->id,self::getItemName());
        if (count($orderitems)){
            foreach ($orderitems as $item)
                if ($item->status=='C')
                    return;//Auction was paid for!
        }

        $model=self::getModel();
        $price=$model->getItemPrice($auction->cat);
        if (!floatval($price->price)) return; // Free publishing

        $modelbalance=JTheFactoryPricingHelper::getModel('balance');
        $balance=$modelbalance->getUserBalance();


        if (BidsHelperPrices::comparePrices($price,array("price"=>$balance->balance,"currency"=>$balance->currency))>0)
        {
            //insufficient funds
            $auction->published = 0;
            $a = JTable::getInstance('auction');
            $a->bind($auction);
            $a->store();

            $modelorder=JTheFactoryPricingHelper::getModel('orders');
            $item=$model->getOderitem($auction);
            $order=$modelorder->createNewOrder($item,$price->price,$price->currency,null,'P');
            $session= JFactory::getSession();
            $session->set('checkout-order',$order->id,self::getContext());
            return;
        }
        //get funds from account, create confirmed order
        $balance_minus=BidsHelperPrices::convertCurrency($price->price,$price->currency,$balance->currency);

        $modelbalance->decreaseBalance($balance_minus);

        $modelorder=JTheFactoryPricingHelper::getModel('orders');
        $item=$model->getOderitem($auction);
        $order=$modelorder->createNewOrder($item,$price->price,$price->currency,null,'C');
    }

    function onAfterExecuteTask($controller)
    {
        $app = JFactory::getApplication();
        if ($app->isAdmin()) {
            return;
        }

        $session= JFactory::getSession();
        $orderid=$session->get('checkout-order',0,self::getContext());
        $session->set('checkout-order',null,self::getContext());
        $session->clear('checkout-order',self::getContext());
        if ($orderid) $controller->setRedirect(BidsHelperRoute::getCheckoutRoute($orderid,false));

    }
    function onPaymentForOrder($paylog,$order)
    {
        if (!$order->status=='C') return;
        $modelorder=JTheFactoryPricingHelper::getModel('orders');
        $items=$modelorder->getOrderItems($order->id,self::getItemName());
        if (!is_array($items)||!count($items)) return; //no Listing items in order

        $cfg = BidsHelperTools::getConfig();

        $nowDate=new JDate();
        $auction = JTable::getInstance('auction');
        foreach($items as $item) {
            if (!$item->iteminfo) continue; //AuctionID is stored in iteminfo
            if ($item->itemname!=self::getItemName()) continue;

            if(!$auction->load($item->iteminfo)) continue; //auction no longer exists
            $auction->modified=$nowDate->toMySQL();
            $auction->published=1;

            if(!$cfg->bid_opt_enable_date) {
                $startDate = new JDate($auction->start_date);
                $diff = $nowDate->toUnix() - $startDate->toUnix();
                if($diff > 0) {
                    $auction->start_date = $nowDate->toMySQL();

                    $endDate = new JDate($auction->end_date);
                    $endDate->add( new DateInterval('PT'.$diff.'S') );
                    $auction->end_date = $endDate->toMySQL();
                }
            }

            $auction->store();
            JTheFactoryEventsHelper::triggerEvent('onAfterSaveAuctionSuccess',array($auction));//for email notifications
        }

    }
}
