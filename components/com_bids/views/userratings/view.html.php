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

class bidsViewUserRatings extends BidsSmartyView {

    function display($tpl=null) {

        JHTML::script( JURI::root().'components/com_bids/js/ratings.js' );
        JHTML::script( JURI::root().'components/com_bids/js/startup.js' );

        $model = $this->getModel('ratings');
        $ratings = $model->get('userratings');

        $lists = array();
        //$lists[''];

        $this->assign('ratings', $ratings);
        $this->assign('lists', $lists);

        parent::display($tpl);
    }
}
