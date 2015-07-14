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
 * @subpackage: Comission
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryEventPrice_Comission extends JTheFactoryEvents
{
    function getItemName()
    {
        return "comission";
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
    function &getTable()
    {
        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'pricing'.DS.self::getItemName().DS.'tables');
        $table = JTable::getInstance('comission');
        return $table;
    }

    function onBeforeDisplay($task,$smarty)
    {
        if (!is_object($smarty))
            return;
        if(!in_array($task, array('viewbids') ) )
            return;

        $curent_info=$smarty->get_template_vars('payment_items_header');

        $my = JFactory::getUser();

        $auction= $smarty->get_template_vars('auction');
        //if ($auction->close_offer || !$auction->published || !$auction->isMyAuction())
        if ($auction->close_offer || !$auction->published || $my->guest)
            return;

        $model=self::getModel();
        $price=$model->getItemPrice($auction->cat, $auction->isMyAuction() ? 'seller' : 'buyer' );
        $defaultPrice = $model->getDefaultPrice( $auction->isMyAuction() ? 'seller' : 'buyer' );

        $modelbalance=JTheFactoryPricingHelper::getModel('balance');
        $balance=$modelbalance->getUserBalance();
        if (!floatval($price->price)){
            if($defaultPrice) {
                $priceinfo=JText::_("COM_BIDS_THERE_IS_NO_COMMISSION_FOR_THIS_CATEGORY");
            } else {
                return;
            }
        }else{

            $priceinfo=JText::_("COM_BIDS_YOUR_CURRENT_BALANCE_IS").number_format($balance->balance,2)." ".$balance->currency."<br/>";
            if($my->id==$auction->userid) {
                $priceinfo.=JText::_("COM_BIDS_COMMISSION_FOR_THIS_CATEGORY_IS").number_format($price->price,2)."% <br/>";
            } else {
                $priceinfo.=JText::_("COM_BIDS_THE_COMMISSION_FOR_THE_HIGHEST_BID_NOW_WOULD_BE").number_format($auction->get('highest_bid')*$price->price/100,2)." ".$auction->currency;
            }

            if ($balance->currency<>$auction->currency)
            {
                $amount=BidsHelperPrices::convertCurrency($auction->get('highest_bid'),$auction->currency,$balance->currency);
                $priceinfo.=" (".number_format($amount*$price->price/100,2)." $balance->currency)";
            }
            $priceinfo.="<br/>";
        }
        $smarty->assign('payment_items_header',$curent_info.$priceinfo);

    }

    function onAfterAcceptBid($auction,$bid)
    {
        if (!$auction->published) return; //not published yet

        $this->commissionSeller($auction,$bid);

        $this->commissionBuyer($auction,$bid);
    }

    private function commissionSeller($auction,$bid) {

        $orderitems=BidsHelperAuction::getOrderItemsForAuction($auction->id,self::getItemName());
        if (count($orderitems)){
            foreach ($orderitems as $item)
                if ($item->status=='C')
                    return;//Auction was paid for!
        }

        $my = JFactory::getUser();

        $model=self::getModel();
        $price=$model->getItemPrice($auction->cat, 'seller' );
        if (!floatval($price->price)) return; // Free publishing


        $modelbalance=JTheFactoryPricingHelper::getModel('balance');
        $balance=$modelbalance->getUserBalance();

        $comission_amount=$price->price*$bid->bid_price/100;
        $currency=$auction->currency;
        $funds_delta=0;
        if (BidsHelperPrices::comparePrices(array("price"=>$comission_amount,"currency"=>$currency),
                array("price"=>$balance->balance,"currency"=>$balance->currency))>0)
        {
            $funds_delta=BidsHelperPrices::convertCurrency($balance->balance,$balance->currency,$currency);
            if ($funds_delta<=0) $funds_delta=0; //if he has some funds - get the rest
            $has_funds=false;
        }
        else
            $has_funds=true;

        $balance_minus=BidsHelperPrices::convertCurrency($comission_amount,$currency,$balance->currency);
        $modelbalance->decreaseBalance($balance_minus);


        $modelorder=JTheFactoryPricingHelper::getModel('orders');
        $items=array($model->getOderitem($auction,$bid,'seller'));
        if ($funds_delta>0){
            $item=new stdClass();
            $item->itemname=JText::_("COM_BIDS_EXISTING_FUNDS");
            $item->itemdetails=JText::_("COM_BIDS_EXISTING_FUNDS");
            $item->iteminfo=0;
            $item->price=-$funds_delta;
            $item->currency=$currency;
            $item->quantity=1;
            $item->params='';
            $items[]=$item;
        }
        $order=$modelorder->createNewOrder($items,$comission_amount-$funds_delta,$currency,$auction->userid,$has_funds?'C':'P');
        if (!$has_funds && $my->id==$auction->userid){
            $session= JFactory::getSession();
            $session->set('checkout-order',$order->id,self::getContext());
        }

        $date=new JDate();

        $comission_table=self::getTable();
        $comission_table->userid=$auction->userid;
        $comission_table->auction_id=$auction->id;
        $comission_table->bid_id=$bid->id;
        $comission_table->comission_date=$date->toMySQL();
        $comission_table->amount=$comission_amount;
        $comission_table->currency=$currency;
        $comission_table->commissionType = 'seller';
        $comission_table->store();
    }

    private function commissionBuyer($auction,$bid) {

        $orderitems=BidsHelperAuction::getOrderItemsForAuction($bid->id,self::getItemName());
        if (count($orderitems)) {
            foreach ($orderitems as $item)
                if ($item->status=='C')
                    return;//Bid was paid for!
        }

        $my = JFactory::getUser();

        $model=self::getModel();
        $price=$model->getItemPrice($auction->cat, 'buyer');
        if (!floatval($price->price)) return; // no buyer's premium

        $modelbalance=JTheFactoryPricingHelper::getModel('balance');
        $balance=$modelbalance->getUserBalance();

        $bp_amount=$price->price*$bid->bid_price/100;
        $currency=$auction->currency;
        $funds_delta=0;
        if (BidsHelperPrices::comparePrices(array("price"=>$bp_amount,"currency"=>$currency),
                array("price"=>$balance->balance,"currency"=>$balance->currency))>0)
        {
            $funds_delta=BidsHelperPrices::convertCurrency($balance->balance,$balance->currency,$currency);
            if ($funds_delta<=0) $funds_delta=0; //if he has some funds - get the rest
            $has_funds=false;
        }
        else
            $has_funds=true;

        $balance_minus=BidsHelperPrices::convertCurrency($bp_amount,$currency,$balance->currency);
        $modelbalance->decreaseBalance($balance_minus);

        $modelorder=JTheFactoryPricingHelper::getModel('orders');
        $items=array($model->getOderitem($auction,$bid,'buyer'));
        if ($funds_delta>0){
            $item=new stdClass();
            $item->itemname=JText::_("COM_BIDS_EXISTING_FUNDS");
            $item->itemdetails=JText::_("COM_BIDS_EXISTING_FUNDS");
            $item->iteminfo=0;
            $item->price=-$funds_delta;
            $item->currency=$currency;
            $item->quantity=1;
            $item->params='';
            $items[]=$item;
        }
        $order=$modelorder->createNewOrder($items,$bp_amount-$funds_delta,$currency,$bid->userid,$has_funds?'C':'P');
        if (!$has_funds && $my->id==$bid->userid){
            $session= JFactory::getSession();
            $session->set('checkout-order',$order->id,self::getContext());
        }

        $date=new JDate();

        $comission_table=self::getTable();
        $comission_table->userid=$bid->userid;
        $comission_table->auction_id=$auction->id;
        $comission_table->bid_id=$bid->id;
        $comission_table->comission_date=$date->toMySQL();
        $comission_table->amount=$bp_amount;
        $comission_table->currency=$currency;
        $comission_table->commissionType = 'buyer';
        $comission_table->store();
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

        foreach($items as $item){
            if ($item->itemname!=self::getItemName()) continue;

            $modelbalance=JTheFactoryPricingHelper::getModel('balance');

            $currency=JTheFactoryPricingHelper::getModel('currency');
            $default_currency=$currency->getDefault();
            $amount=BidsHelperPrices::convertCurrency($item->price,$item->currency,$default_currency);
            $modelbalance->increaseBalance($amount,$order->userid);
        }

    }
    function onBeforeExecuteTask(&$stopexecution)
    {
        $app = JFactory::getApplication();
        if($app->isAdmin()) {
            return;
        }

        $task = JRequest::getCmd('task','listauctions');
        if ($task=='paycomission'){
            $stopexecution=true; //task is fully processed here
            $app= JFactory::getApplication();
            $user= JFactory::getUser();
            $modelbalance=JTheFactoryPricingHelper::getModel('balance');
            $balance=$modelbalance->getUserBalance();
            
            if ($balance->balance>=0){
                JError::raiseNotice(501,JText::_("COM_BIDS_YOU_HAVE_A_POSITIVE_BALANCE"));
                $app->redirect(BidsHelperRoute::getAddFundsRoute());
                return;                
            }
            
            $model=self::getModel();
            $modelorder=JTheFactoryPricingHelper::getModel('orders');
            $item=$model->getOderitemFromBalance($balance);
            $order=$modelorder->createNewOrder($item,$item->price,$item->currency,null,'P');
            $app->redirect(BidsHelperRoute::getCheckoutRoute($order->id,false));
            return;
        }

    }
    
    
}
