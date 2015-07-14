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

class bidsViewProfile extends BidsSmartyView {

    function display($tpl=null) {

        $database = JFactory::getDBO();
        $my = JFactory::getUser();
        $cfg = BidsHelperTools::getConfig();

        JHTML::_('behavior.modal');
        JHTML::_('behavior.tooltip');

        JHTML::script(JURI::root().'components/com_bids/js/ratings.js');
        JHTML::script( JURI::root().'components/com_bids/js/startup.js' );

        $model = $this->getModel();
        $profile = $model->get('profile');

        $lists['balance'] = $model->getBalance();

        $lists["ratings"] = $model->getRatings(10);
        $lists["messages"]["received"] = $model->getMessages();

        if ($cfg->bid_opt_allow_user_settings && $profile->isSeller) {

            $p = JTable::getInstance('bidusersettings');
            $p->load($my->id);
            $settings = $p->settings;

            $lists['user_settings'] = array();

            if($cfg->bid_opt_enable_hour) {
                //convert end time to GMT
                try {
                    $endTime = @$settings["end_hour"].':'.@$settings["end_minute"];
                    @list($settings["end_hour"],$settings["end_minute"]) = explode(':',JHTML::date($endTime,'H:i'));
                } catch(Exception $e) {
                    if(JDEBUG) {
                        //JError::raiseWarning(1,$e->getMessage());
                    }
                }
                //end time is GMT, convert it to locale

                $lists['user_settings']['end_hour'] = '<input type="text" name="end_hour" class="inputbox" value="'.@$settings['end_hour'].'" style="width:20px;" />';
                $lists['user_settings']['end_minute'] = '<input type="text" name="end_minute" class="inputbox" value="'.@$settings['end_minute'].'" style="width:20px;" />';
            }
            $lists['user_settings']['payment_info'] = '<input style="width: 300px;" type="text" name="payment_info"
            class="inputbox"
            value="'.@$settings['payment_info'].'" />';
            $lists['user_settings']['shipment_info'] = '<input style="width: 300px;" type="text" name="shipment_info" class="inputbox" value="'.@$settings['shipment_info'].'" />';
            $lists['user_settings']['show_reserve'] = JHTML::_('select.booleanlist','show_reserve',null,@$settings['show_reserve'],'COM_BIDS_SHOW','COM_BIDS_HIDE');
            $lists['user_settings']['auto_accept_bin'] = JHTML::_('select.booleanlist','auto_accept_bin',null,@$settings['auto_accept_bin']);
            $lists['user_settings']['bid_counts'] = JHTML::_('select.booleanlist','bid_counts',null,@$settings['bid_counts'],'COM_BIDS_SHOW','COM_BIDS_HIDE');
            $lists['user_settings']['max_price'] = JHTML::_('select.booleanlist','max_price',null,@$settings['max_price'],'COM_BIDS_SHOW','COM_BIDS_HIDE');

            $fi = isset($settings['auction_type']) ? $settings['auction_type'] : null;

            $opts = array();
            $opts[] = JHTML::_('select.option', '', JText::_('COM_BIDS_PICK_TYPE_OF_AUCTION'));
            $opts[] = JHTML::_('select.option', AUCTION_TYPE_PUBLIC, JText::_('COM_BIDS_PUBLIC_LABEL'));
            if ($cfg->bid_opt_global_enable_private)
                $opts[] = JHTML::_('select.option', AUCTION_TYPE_PRIVATE, JText::_('COM_BIDS_PRIVATE_LABEL'));
            if ($cfg->bid_opt_enable_bin_only)
                $opts[] = JHTML::_('select.option', AUCTION_TYPE_BIN_ONLY, JText::_('COM_BIDS_BIN_ONLY_LABEL'));

            $lists['user_settings']['auction_type'] = JHTML::_('select.genericlist', $opts, 'auction_type', 'class="inputbox" alt="auction_type"', 'value', 'text', $fi);

            $fi = isset($settings['currency']) ? $settings['currency'] : null;

            $query = "SELECT name AS value, name AS text FROM #__bid_currency ORDER BY id";
            $opts = null;
            $database->setQuery($query);
            $opts = $database->loadObjectList();
            $defaultOpt = JHtml::_('select.option','',JText::_('COM_BIDS_SELECT_CURRENCY'));
            array_unshift($opts,$defaultOpt);
            $lists["user_settings"]['currency'] = JHTML::_('select.genericlist', $opts, 'currency',
                'class="inputbox" onchange="bidsRefreshCurrency(this.value)"', 'value', 'text', $fi);

            $lists['user_settings']['shipment_price'] = '<input type="text" name="shipment_price" class="inputbox"
            style="text-align: right; width: 80px;" value="' . BidsHelperAuction::formatPrice
            (@$settings['shipment_price']) . '" />&nbsp;&nbsp;<span class="bidsRefreshCurrency">'.$fi.'</span>';
        }

        $lists["links"]=array(
          "upload_funds"=>BidsHelperRoute::getAddFundsRoute(),
          "payment_history"=>BidsHelperRoute::getPaymentsHistoryRoute()
        );

        $lists['fbLikeButton'] = JHTML::_('userProfile.fbLikeButton', $profile);
        $lists['linkEditProfile'] = JHTML::_('userprofile.linkEditProfile');
        $lists['linkUserRatings'] = JHTML::_('userprofile.linkUserRatings',$profile);

        $profile->paypalemail = BidsHelperTools::cloack_email($profile->paypalemail);

        $fields = CustomFieldsFactory::getFieldsList("user_profile");

        if($cfg->terms_and_conditions) {
            $lists['linktc'] = JRoute::_('index.php?option=com_bids&task=terms_and_conditions&tmpl=component');
        }


        $this->assign("user", $profile);
        $this->assign("lists", $lists);
        $this->assign("fields", $fields);
        $this->assign('return', JRequest::getCmd("return"));

        return parent::display($tpl);
    }
}
