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



defined('_JEXEC') or die();

class JHTMLUserProfile {

    static function fbLikeButton($user) {

        $cfg = BidsHelperTools::getConfig();

        if(!$cfg->bid_opt_fblikebutton) {
            return;
        }
        $iframeURL = 'https://www.facebook.com/plugins/like.php?href='.urlencode(self::userProfileURL($user));
        $iframeAttribs = 'scrolling="no" frameborder="0" class="bidsFbLikeButton"';

        return JHTML::iframe($iframeURL, APP_EXTENSION.'_fbLikeButton_'.$user->userid, $iframeAttribs);
    }

    static function userProfileURL($user) {
        return JRoute::_('index.php?option='.APP_EXTENSION.'&task=userdetails&id='.$user->userid);
    }

    static function linkEditProfile() {

        $url = 'index.php?option='.APP_EXTENSION.'&task=editProfile';
        $txt = '<img src="'.JURI::root().'components/'.APP_EXTENSION.'/images/edit_user_detail.jpg" width="24" title="'.JText::_('COM_BIDS_EDIT_USER_DETAILS').'" alt="'.JText::_('COM_BIDS_EDIT_USER_DETAILS').'"/>';

        return JHTML::link($url,$txt);
    }

    static function linkUserAuctions($user) {
        return JHTML::link(JRoute::_('index.php?option='.APP_EXTENSION.'&task=listauctions&users[]='.$user->id.'&Itemid'),JText::_('COM_BIDS_MORE_AUCTIONS'));
    }

    static function linkUserRatings($user) {

        $url = JRoute::_('index.php?option=com_bids&task=userratings&userid='.$user->userid);
        $text = JText::_('COM_BIDS_VIEW_ALL_RATINGS');

        return JHtml::link($url,$text);
    }
}
