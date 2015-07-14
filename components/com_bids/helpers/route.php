<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/


// no direct access
defined('_JEXEC') or die('Restricted access');

// stupid but idiot
jimport('joomla.application.component.helper');
$Tapp = JFactory::getApplication();

/*if (!$Tapp->isAdmin() && !defined('APP_CATEGORY_TABLE')) {
    require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'thefactory'.DS.'application'.DS.'application.class.php');
    $MyApp = JTheFactoryApplication::getInstance(null, true);
}*/

/**
 * Bids Component Route Helper
 *
 * @static
 * @package		Ads Factory
 * @subpackage	Router
 * @since 1.5.0
 */
class BidsHelperRoute {

    function getSEFCatString($id) {

        $catOb = BidsHelperTools::getCategoryModel();

        return $catOb->getCategoryPathString($id);

    }

    /**
     * Get the Parameter Task ItemID
     *  	if more than one menu items of same task, returns the first
     *
     * @param  $task_name
     * @return int
     */
    function getMenuItemByTaskName($task_name) {
        $database = JFactory::getDBO();
        $database->setQuery("SELECT id FROM #__menu WHERE link LIKE '%task=$task_name%' ORDER BY id DESC LIMIT 0 , 1 ");
        return $database->loadResult();
    }

    /**
     * Finds The Menu Item Of the Component
     *  by the needles as params
     *
     * needle example: 'view' => 'category'
     *
     *
     * @param array $needle
     * @since 1.5.0
     */
    static function getMenuItemId($needles) {

        $component = JComponentHelper::getComponent('com_bids');

        $app = JFactory::getApplication();
        $menus = $app->getMenu('site',array());
        $items = $menus->getItems('component_id', $component->id);

        $match = null;

        foreach ($items as $item) {

            $ok = true;
            foreach ($needles as $needle => $id) {
                if (@$item->query[$needle] != $id) {

                    $ok = false;
                    break;
                }
            }
            if ($ok == true) {
                $match = $item;
                break;
            }
        }

        if (isset($match)) {

            return $match->id;
        }else
            return null;
    }

    static function getItemid($needles=null) {

        require_once('tools.php');

        $Itemid=JRequest::getInt('Itemid');
        if (!$Itemid) $Itemid=BidsHelperTools::getMenuItemId($needles);

        if ($Itemid) return "&Itemid=".$Itemid;

        return "";
    }

    static function getAuctionListRoute($filters=null,$xhtml=true) {

        $link="index.php?option=com_bids&task=listauctions";
        if (is_array($filters)){
            foreach($filters as $k=>$v)
                $link.="&$k=$v";
        }elseif($filters) $link.=$filters;
        $link.=self::getItemid(array('task'=>'listauctions'));
        return JRoute::_($link,$xhtml);

    }

    static function getUserdetailsRoute($userid=null,$xhtml=true) {

        $link = "index.php?option=com_bids&task=userdetails" . ( $userid ? '&id='.$userid : '' );

        return JRoute::_($link,$xhtml);
    }

    static function getAddFundsRoute($xhtml=true)
    {
        $link="index.php?option=com_bids&task=balance.addfunds".self::getItemid(array('task'=>'userdetails'));
        return JRoute::_($link,$xhtml);
    }
    static function getPaymentsHistoryRoute($xhtml=true)
    {
        $link="index.php?option=com_bids&task=payments.history".self::getItemid(array('task'=>'userdetails'));
        return JRoute::_($link,$xhtml);
    }
    static function getCheckoutRoute($orderid,$xhtml=true)
    {
        $link="index.php?option=com_bids&task=orderprocessor.checkout&orderid=$orderid".self::getItemid(array('task'=>'form','task'=>'editauction','task'=>'new'));
        return JRoute::_($link,$xhtml);
    }
    static function getFeaturedRoute($auctionid,$xhtml=true)
    {
        $link="index.php?option=com_bids&task=setfeatured&id=".$auctionid.self::getItemid(array('task'=>'viewbids','task'=>'details'));
        return JRoute::_($link,$xhtml);
    }
    static function getAuctionDetailRoute($auction,$xhtml=true)
    {
        return JHtml::_('auctiondetails.auctionDetailsURL',$auction,$xhtml);
        //$link="index.php?option=com_bids&task=viewbids&id=".$auctionid.self::getItemid(array('task'=>'viewbids','task'=>'details'));
        //return JRoute::_($link,$xhtml);
    }
    static function getNewAuctionRoute($xhtml=true)
    {
        $link="index.php?option=com_bids&task=newauction".self::getItemid(array('task'=>'newauction','task'=>'form'));
        return JRoute::_($link,$xhtml);
    }
    static function getCategoriesRoute($catid=null,$xhtml=true)
    {
        $link="index.php?option=com_bids&task=listcats".self::getItemid(array("task" => "listcats"),array("task" => "listauctions"));
        if ($catid) $link.="&cat={$catid}"; 
        return JRoute::_($link,$xhtml);
    }
    static function getAddToCatWatchlist($catid,$xhtml=true)
    {
        $link="index.php?option=com_bids&task=addwatchcat&cat={$catid}".self::getItemid(array('task'=>'listcats'));
        return JRoute::_($link,$xhtml);
    }
    static function getDelToCatWatchlist($catid,$xhtml=true)
    {
        $link="index.php?option=com_bids&task=delwatchcat&cat={$catid}".self::getItemid(array('task'=>'listcats'));
        return JRoute::_($link,$xhtml);
    }
    static function getCategoryRoute($catid=null,$task='categories',$catslug=null,$xhtml=true,$filterLetter=null)
    {
        $link="index.php?option=com_bids&task={$task}".(($catid)?("&cat={$catid}"):("")).$catslug. ($filterLetter ? '&filter_letter='. $filterLetter : '');
        return JRoute::_($link,$xhtml);
    }
}
