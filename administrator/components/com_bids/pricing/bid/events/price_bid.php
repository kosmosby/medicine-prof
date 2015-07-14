<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 3.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: Bids
 * @subpackage: Pay per bid
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryEventPrice_Bid extends JTheFactoryEvents
{
    protected
            $paidOnBid=false,
            $paidOnProxy = false;

    function getItemName()
    {
        return "bid";
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
        if ($auction->isMyAuction() || $auction->close_offer) return; //TODO: Verify if auction Expired
        $model=self::getModel();
        $price=$model->getItemPrice($auction->cat);
        if (!floatval($price->price)){
            return;
        }else{
            $priceinfo=JText::_("COM_BIDS_PRICE_PER_BID")." ".number_format($price->price,2)." ".$price->currency;
        }
        $smarty->assign('payment_items_header',$curent_info.$priceinfo);

    }

    function onBeforeUserProxyBid($auction,$proxy) {

        $params = $this->getModel()->getItemPrices();

        if($params->allow_no_funds || $this->paidOnProxy) {
            return;
        }

        $bid = $this->proxy2bid($proxy);

        $this->generateOrder($bid,$auction->cat,true);
    }

    function onAfterUserProxyBid($auction,$proxy) {

        $params = $this->getModel()->getItemPrices();

        if(!$params->allow_no_funds || $this->paidOnProxy) {
            return;
        }

        $bid = $this->proxy2bid($proxy);

        $this->generateOrder($bid,$auction->cat,false);
    }

    function onBeforeSaveBid($auction,$bid) {

        $params = $this->getModel()->getItemPrices();

        if($params->allow_no_funds || $this->paidOnProxy || $this->paidOnBid) {
            return;
        }

        $this->generateOrder($bid,$auction->cat,true);
    }

    function onAfterSaveBid($auction,$bid)
    {
        if ($bid->cancel) return; //not published yet

        $model=self::getModel();
        $params = $model->getItemPrices();

        if(!$params->allow_no_funds || $this->paidOnProxy || $this->paidOnBid) {
            return;
        }

        $this->generateOrder($bid,$auction->cat,false);
    }

    function onBeforeProxyBids($auction,$proxy) {
        $this->paidOnProxy = true;
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

        require_once JPATH_COMPONENT_SITE.DS.'models'.DS.'auction.php';
        $modelAuction = JModel::getInstance('auction','bidsModel');

        foreach($items as $item) {

            if (!$item->iteminfo) continue; //bid id is stored in iteminfo
            if ($item->itemname!=self::getItemName()) continue;

            $itemParams = new JParameter($item->params);
            $auctionId = $itemParams->get('auction_id');
            if(!$auctionId || !$modelAuction->load($auctionId)) {
                return; //auction does not exists anymore
            }

            if($itemParams->get('proxy')) {
                //next time the biding events rises, this plugin won't repeat itself
                $this->paidOnProxy = true;
                $modelAuction->proxyBid($itemParams->get('userid'),$itemParams->get('bid_price'));
            } else {
                //next time the biding events rises, this plugin won't repeat itself
                $this->paidOnBid = true;
                $modelAuction->bid($itemParams->get('userid'),$itemParams->get('bid_price'));
            }
        }
    }

    private function generateOrder($bid,$catid,$needsPositiveBalance) {

        $app = JFactory::getApplication();

        $model=self::getModel();

        $price=$model->getItemPrice($catid);
        if (!floatval($price->price)) return; // Free publishing

        $modelbalance=JTheFactoryPricingHelper::getModel('balance');
        $balance=$modelbalance->getUserBalance($bid->userid);

        if($needsPositiveBalance) {

            if (BidsHelperPrices::comparePrices($price,array("price"=>$balance->balance,"currency"=>$balance->currency))>0) {

                $app->enqueueMessage(JText::_('COM_BIDS_NOT_ENOUGH_FUNDS_TO_BID'),'notice');

                $modelorder=JTheFactoryPricingHelper::getModel('orders');
                $item=$model->getOderitem($bid);
                $order = $modelorder->createNewOrder($item,$price->price,$price->currency,null,'P');

                $app->redirect( BidsHelperRoute::getCheckoutRoute($order->id,false) );
            }
        }

        //get funds from account, create confirmed order
        $balance_minus=BidsHelperPrices::convertCurrency($price->price,$price->currency,$balance->currency);

        $modelbalance->decreaseBalance($balance_minus,$bid->userid);

        $modelorder=JTheFactoryPricingHelper::getModel('orders');
        $item=$model->getOderitem($bid);
        $modelorder->createNewOrder($item,$price->price,$price->currency,$bid->userid,'C');
    }

    private function proxy2bid($proxy) {

        $bid = new stdClass();
        $bid->id = $proxy->id;
        $bid->auction_id = $proxy->auction_id;
        $bid->userid = $proxy->user_id;
        $bid->bid_price = $proxy->max_proxy_price;
        $bid->proxy = 1;

        return $bid;
    }
}