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

class JTheFactoryEventBalance extends JTheFactoryEvents
{
    function getItemName()
    {
        return "Balance";
    }
    function getContext()
    {
        return APP_PREFIX.".".self::getItemName();
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
            $modelbalance->increaseBalance($item->price,$order->userid);
        }

    }
    function onDefaultCurrencyChange() {

        $db = JFactory::getDbo();

        $db->setQuery('SELECT * FROM #__'.APP_PREFIX.'_payment_balance');
        $balances = $db->loadObjectList();

        $currency=JTheFactoryPricingHelper::getModel('currency');
        $newCurrency = $currency->getDefault();

        foreach($balances as $b) {
            $balance = JTable::getInstance('BalanceTable','JTheFactory');
            $balance->bind($b);
            $balance->balance = $currency->convertCurrency($balance->balance,$balance->currency,$newCurrency);
            $balance->currency = $newCurrency;
            $balance->store();
        }

    }
}


?>
