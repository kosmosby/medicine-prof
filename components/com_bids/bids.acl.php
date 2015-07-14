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

class JBidsACL {

    public $taskmapping = array(

        'myauctions' => 'seller',
        'newauction' => 'seller',
        'form' => 'seller',
        'accept' => 'seller',
        'saveauction' => 'seller',
        'cancelauction' => 'seller',
        'editauction' => 'seller',
        'republish' => 'seller',
        'set_featured' => 'seller',
        'savedefaultauctionsettings' => 'seller',

        'selectcat' => 'seller',
        'acceptsuggestion' => 'seller',
        'bulkimport' => 'seller',

        'mybids' => 'bidder',
        'sendbid' => 'bidder',
        'savebid' => 'bidder',
        'bin' => 'bidder',
        'mywonbids' => 'bidder'
    ),

    $publicTasks = array(

    ),

    $anonTasks = array(
        'viewbids',
        'listauctions',
        'listcats',
        'search',
        'showsearchresults',
        'categories',
        'tree',
        'searchusers',
        'showusers',
        'userdetails',
        'editprofile',
        'tags',
        'saveuserdetails',
        'show_profilemodal',
        'googlemap_tool',
        'registerform',
        'rss',
        'report_auction',
        'sendReportAuction',
        'saveuserdetails', //This is for registration through component
    );

    protected $_my = null;

    function __construct() {

        $this->_my = JFactory::getUser();

        $cfg = BidsHelperTools::getConfig();

        if ($cfg->bid_opt_allow_guest_messaging) {
            array_push($this->anonTasks, "savemessage");
        }
    }
/*@andrei : am sters legacy stuff inutil */
 
}
