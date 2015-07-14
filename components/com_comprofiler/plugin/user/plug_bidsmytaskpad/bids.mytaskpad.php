<?php

/**
 * @package AuctionsFactory
 * @version 2.1.2
 * @copyright www.thefactory.ro
 * @license: commercial
 */
// no direct access
defined('_JEXEC') or die('Restricted access!');

JLoader::register('bidsCbTabHandler', JPATH_SITE . DS . 'components' . DS . 'com_bids' . DS . 'helpers' . DS . 'cb' . DS . 'plgtabhandler.php');

class myTaskPad extends bidsCbTabHandler {

    function __construct() {

        jimport('joomla.application.component.model');

        parent::__construct();
    }

    function getmywatchlistTab() {
        $this->cbTabHandler();
    }

    function getDisplayTab($tab, $user, $ui) {

        $my = JFactory::getUser();
        $database = &JFactory::getDBO();

        if ($my->id != $user->user_id || !$my->id) {
            return;
        }

        $componentPath = JPATH_SITE . DS . 'components' . DS . 'com_bids';

        require_once($componentPath.DS.'options.php');
        $cfg = new BidConfig();

        require_once($componentPath . DS . 'helpers' . DS . 'tools.php');
        require_once($componentPath . DS . 'thefactory' . DS . 'front.userprofile.php');
        require_once($componentPath . DS . 'helpers' . DS . 'profile.php');


        JModel::addIncludePath($componentPath . DS . 'models');

        $bidsUserModel = JModel::getInstance('user','bidsModel');
        $bidsUserModel->loadUser($my->id);
        $bidsProfile = $bidsUserModel->get('profile');

        $balance = $bidsUserModel->getBalance();

        //here begins output
        $html =
        '<div>
            <table width="100%">
                <tr>
                    <td colspan="4">';

                        if($cfg->bid_opt_enable_acl) {
                            $sellerImg = JURI::root() . 'components/com_bids/images/f_can_sell'.( !empty($bidsProfile->isSeller) ? '1':'2').'.gif';
                            $html .= '<div style="width:120px;float:left;">'.
                                           JHTML::image($sellerImg,'','style="margin-right:50px;" border="0"').
                                       '</div>';
                            $bidderImg = JURI::root() . 'components/com_bids/images/f_can_buy'.( !empty($bidsProfile->isBidder) ? '1':'2').'.gif';
                            $html .= '<div style="width:120px;float:left;">'.
                                           JHTML::image($bidderImg,'','style="margin-right:60px;" border="0"').
                                       '</div>';
                        }
                        elseif(!empty($bidsProfile->powerseller)) {
                            $sellerImg = JURI::root() . 'components/com_bids/images/f_can_sell'.( !empty($bidsProfile->isSeller) ? '1':'2').'.gif';
                            $html .= '<div style="width:120px;float:left;">'.
                                           JHTML::image($sellerImg,'','style="margin-right:50px;" border="0"').
                                       '</div>';
                        }

                        $verifiedImg = JURI::root() . 'components/com_bids/images/verified_'.( !empty($bidsProfile->verified) ? '1':'0').'.gif';
                        $html .= '<div style="width:120px;float:left;">'.
                                       JHTML::image($verifiedImg,'','style="margin-right:50px;" border="0"').
                                   '</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">';

                        if($cfg->bid_opt_enable_acl) {
                            if (!empty($bidsProfile->isSeller)){
                                $html .= "<div style='width:120px;float:left;'>" . Jtext::_('COM_BIDS_acl_group_seller') . ":" . ($bidsProfile->powerseller ? JText::_('Powerseller') : JText::_('Seller'))  . "</div>";
                            } else {
                                $html .= "<div style='width:120px;float:left;'>" . Jtext::_('COM_BIDS_acl_group_seller') . ":" . ($bidsProfile->isSeller ? Jtext::_('COM_BIDS_YES') : Jtext::_('COM_BIDS_no') ) . "</div>";
                            }
                            $html .= "<div style='width:120px;float:left;'>" . Jtext::_('COM_BIDS_acl_group_bidder') . ":" . ($bidsProfile->isBidder ? Jtext::_('COM_BIDS_YES') : Jtext::_('COM_BIDS_no') ) . "</div>";
                        }
                        elseif(!empty($bidsProfile->powerseller)) {
                            $html .= "<div style='width:120px;float:left;'>" . Jtext::_('COM_BIDS_acl_group_seller') . ":" . ($bidsProfile->powerseller ? JText::_('Powerseller') : JText::_('Seller'))  . "</div>";
                        }
                        $html .= "<div style='width:120px;float:left;'>" . Jtext::_('COM_BIDS_user_verified') . ":" . ( (isset($bidsProfile->verified) && $bidsProfile->verified) ? Jtext::_('COM_BIDS_YES') : Jtext::_('COM_BIDS_no') ) . "</div>".

                    '</td>
                </tr>
                <tr>
                    <td colspan="4">';

        $tasklist = array(
            'newauction' => 'f_newauction.png',
            'myauctions' => 'f_myauctions.png ',
            'mybids' => 'f_mybids.png',
            'mywonbids' => 'f_mywonbids.png',
            'mywatchlist' => 'f_mywatchlist.png',
            'listcats' => 'f_listcats.png',
            'listauctions' => 'f_listauctions.png',
            'search' => 'f_search.png'
        );
        $keys = array_keys($tasklist);

        $html .= '<table width="100%">
                    <tr>';
                    for ($i = 0; $i < count($keys) / 2; $i++) {
                        $f_task = JRoute::_("index.php?option=com_bids&task=" . $keys[$i]);
                        $html .= "<td width='100'><a href='$f_task'><img src='" . JURI::root() . "components/com_bids/images/menu/" . $tasklist[$keys[$i]] . "' border=0></a></td>";
                    }
        $html .=    '</tr>
                    <tr>';

                    for ($i = count($keys) / 2; $i < count($keys); $i++) {
                        $f_task = JRoute::_("index.php?option=com_bids&task=" . $keys[$i]);
                        $html .= "<td width='100'><a href='$f_task'><img src='" . JURI::root() . "components/com_bids/images/menu/" . $tasklist[$keys[$i]] . "' border=0></a></td>";
                    }
        $html .=    '</tr>
                </table>
            </td>
        </tr>';

        $html .= '<tr>
            <td>
                <div class="auction_credits">'.
                    JText::_('COM_BIDS_YOUR_CURRENT_BALANCE_IS').'&nbsp'.
                    $balance->balance.'&nbsp;'.$balance->currency.
                '</div>
                <div>
                    <a href="'.BidsHelperRoute::getAddFundsRoute().'">'.
                     JText::_('COM_BIDS_ADD_FUNDS_TO_YOUR_BALANCE').
                     '</a>
                </div>
                 <div>
                     <a href="'.BidsHelperRoute::getPaymentsHistoryRoute().'">'.
                      JText::_('COM_BIDS_SEE_MY_PAYMENTS_HISTORY').
                      '</a>
                 </div>
            </td>
        </tr>';


        $html .=
            '</table>
        </div>';

        return $html;
    }
}