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

jimport('joomla.application.component.model');
jimport('joomla.html.parameter');

class JBidPricingModelFeatured extends JModel
{
    var $name='featured';
    var $context='featured';
    var $description="Pay for Featured";
    function loadPricingObject()
    {
        $db= $this->getDbo();
        $db->setQuery("select * from `#__".APP_PREFIX."_pricing` where `itemname`='".$this->name."'");
        return $db->loadObject();
    }

    function getItemPrices()
    {
        $r=new stdClass();
        $db= $this->getDbo();
        $db->setQuery("select * from `#__".APP_PREFIX."_pricing` where `itemname`='".$this->name."'");
        $r=$db->loadObject();
        $params=new JParameter($r->params);

        $res=new stdClass();
        $res->default_price=$r->price;
        $res->default_currency=$r->currency;
        $res->price_powerseller=$params->get('price_powerseller');
        $res->price_verified=$params->get('price_verified');

        return $res;
    }
    function getItemPrice()
    {
        $userprofile=BidsHelperTools::getUserProfileObject();
        $userprofile->getUserProfile();

        $r=$this->loadPricingObject();
        $params=new JParameter($r->params);
        if (isset($userprofile->powerseller) && $userprofile->powerseller)
            $defaultprice=$params->get('price_powerseller',$r->price);
        elseif(isset($userprofile->verified) && $userprofile->verified)
            $defaultprice=$params->get('price_verified',$r->price);
        else
            $defaultprice=$r->price;
        $res=new stdClass();
        $res->price=$defaultprice;
        $res->currency=$r->currency;
        return $res;
    }
    function saveItemPrices($d)
    {
        $params=new JParameter();
        $params->set('price_powerseller',JArrayHelper::getValue($d,'price_powerseller'));
        $params->set('price_verified',JArrayHelper::getValue($d,'price_verified'));
        $params->set('category_pricing_enabled',JArrayHelper::getValue($d,'category_pricing_enabled'));
        $p=$params->toString('INI');
        $price=JArrayHelper::getValue($d,'default_price');
        $currency=JArrayHelper::getValue($d,'currency');

        $db= $this->getDbo();
        $db->setQuery("update `#__".APP_PREFIX."_pricing`
            set `price`='$price',`currency`='$currency',
            `params`='$p'
            where `itemname`='".$this->name."'");
        $db->query();

    }
    function getOderitem($auction)
    {
        $price=$this->getItemPrice();
        $item=new stdClass();
        $item->itemname=$this->name;
        $item->itemdetails=JText::_($this->description);
        $item->iteminfo=$auction->id;
        $item->price=$price->price;
        $item->currency=$price->currency;
        $item->quantity=1;
        $item->params='';
        return $item;
    }

}
