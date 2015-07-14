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




defined('_JEXEC') or die('Restricted access');

class bidsViewReportAuction extends BidsSmartyView {

    function display($tpl=null) {

        $model = $this->getModel('auction');
        $auction = $model->get('auction');

        $this->assign("auction_id", $auction->id);
        $this->assign("auction_title", $auction->title);

        parent::display($tpl);
    }
}
