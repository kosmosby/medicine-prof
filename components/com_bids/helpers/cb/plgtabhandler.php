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



defined('_JEXEC') or die('Restricted access!');

class bidsCbTabHandler extends cbTabHandler {

    function cbTabHandler() {

        if(!file_exists(JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'bids.php')) {
            return "<div>You must First install <a href='http://www.thefactory.ro/shop/joomla-components/auction-factory.html'> Auction Factory </a></div>";
        }

        //need the whole framework loaded so we can access the price_item classes, in order to get the correct price for current user (verified, powerseller,...)
        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'thefactory'.DS.'application'.DS.'application.class.php');

        $cnfigfile = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_bids'.DS.'application.ini';
        $MyApp = JTheFactoryApplication::getInstance($cnfigfile, true);

        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'tables');
        JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'html');

        require_once(JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'options.php');
        require_once(JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'defines.php');

        require_once(JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'bids.php');
        BidsHelper::LoadHelperClasses();

        JFactory::getLanguage()->load('com_bids');

        parent::cbTabHandler();
    }

    function printDate($date) {

        $cfg = new BidConfig();

        $dateformat = $cfg->bid_opt_date_format;
        if ($cfg->bid_opt_enable_hour)
            $dateformat.=" H:i";

        return date($dateformat, strtotime($date));
    }
}
