<?php
/**
 * @package AuctionsFactory
 * @version 2.1.2
 * @copyright www.thefactory.ro
 * @license: commercial
*/

// no direct access
defined('_JEXEC') or die('Restricted access!');

JLoader::register('bidsCbTabHandler', JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'cb'.DS.'plgtabhandler.php');


class getmysettingsTab extends bidsCbTabHandler {

    function getmysettingsTab() {
        $this->cbTabHandler();
    }

    function getDisplayTab($tab,$user,$ui) {

        $database = &JFactory::getDBO();
        $my =& JFactory::getUser();

        if($my->id!=$user->user_id){
            return null;
        }

        $cfg = new BidConfig();

        if($cfg->bid_opt_allow_user_settings==0) {
            return false;
        }

        $isSeller = $isBidder = true;
        if ($cfg->bid_opt_enable_acl)
        {
            $user_groups=JAccess::getGroupsByUser($user->id);

            $isBidder=count(array_intersect($user_groups,$cfg->bid_opt_acl_bidder))>0;
            $isSeller=count(array_intersect($user_groups,$cfg->bid_opt_acl_seller))>0;
        }

        if(!$isSeller) {
            return;
        }

        JHTML::_('behavior.tooltip');

        $p = JTable::getInstance('bidusersettings');

        $act = JRequest::getVar("com_act","","post");
        if($act =="save_u_settings") {
            //load component's user model to manage saving user's settings
            jimport('joomla.application.component.model');
            JModelLegacy::addIncludePath(JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'models');
            $modelUser = JModelLegacy::getInstance('user','bidsModel');

            $modelUser->saveDefaultAuctionSettings(JRequest::get());
        }

        // setting processing
        $p->load($my->id);
        $settings = $p->settings;

        $lists = array();

        $payment_options='';
        if(isset($settings['payment_options'])){
            $payment_options = $settings['payment_options'];
        }

        $fi = isset($settings['auction_type']) ? $settings['auction_type'] : null;

        $opts=null;
        $opts[] = JHTML::_('select.option','',Jtext::_('COM_BIDS_pick_type_of_auction'));
        $opts[] = JHTML::_('select.option',AUCTION_TYPE_PUBLIC,Jtext::_('COM_BIDS_public_label') );
        if( $cfg->bid_opt_global_enable_private )
            $opts[] = JHTML::_('select.option',AUCTION_TYPE_PRIVATE,Jtext::_('COM_BIDS_private_label') );
        if( $cfg->bid_opt_enable_bin_only )
            $opts[] = JHTML::_('select.option',AUCTION_TYPE_BIN_ONLY,Jtext::_('COM_BIDS_bin_only_label'));

        $lists['auction_type'] = JHTML::_('select.genericlist', $opts, 'auction_type', 'class="inputbox" alt="auction_type"',  'value', 'text',$fi);

        $fi = isset($settings['currency']) ? $settings['currency'] : null;

        $database->setQuery( "SELECT name as value, name as text FROM #__bid_currency" );
        $lists['currency'] = JHTML::_('select.genericlist', $database->loadObjectList(), 'currency', 'class="inputbox" size="1"',  'value', 'text',$fi);

        if($cfg->bid_opt_enable_hour) {
            //convert end time to GMT
            try {
                $endTime = @$settings["end_hour"].':'.@$settings["end_minute"];
                @list($settings["end_hour"],$settings["end_minute"]) = explode(':',JHTML::date($endTime,'H:i'));
            } catch(Exception $e) {
                if(JDEBUG) {
                    JError::raiseWarning(1,$e->getMessage());
                }
            }
            //end time is GMT, convert it to locale

            $settings['end_hour'] = '<input type="text" name="end_hour" class="inputbox" value="'.@$settings['end_hour'].'" style="width:20px;" />';
            $settings['end_minute'] = '<input type="text" name="end_minute" class="inputbox" value="'.@$settings['end_minute'].'" style="width:20px;" />';
        }

        $return = "<div>";

        $return .="<table width='100%'>";
        $return .='<form name="topForm'.$tab->tabid.'" action="index.php?option=com_comprofiler&task=userProfile" method="post">';
        $return .="<input type='submit' name='save' value='Save settings' />";
        $return .="<input type='hidden' name='option' value='com_comprofiler' />";
        $return .="<input type='hidden' name='com_act' value='save_u_settings' />";
        $return .="<input type='hidden' name='task' value='userProfile' />";
        $return .="<input type='hidden' name='user' value='".$user->user_id."' />";
        $return .="<input type='hidden' name='tab' value='".$tab->tabid."' />";
        $return .="<input type='hidden' name='act' value='' />";

        $return .= "<tr>
            <td>".Jtext::_('COM_BIDS_setting_currency')."</td>
            <td>
                ".@$lists["currency"]."
            </td>
        </tr>";
        $return .= "<tr>
            <td>".Jtext::_('COM_BIDS_type_of_auction')."</td>
            <td>
                ".@$lists["auction_type"]."
            </td>
        </tr>";
        if($cfg->bid_opt_enable_hour) {
            $return .= "<tr>
                <td>".Jtext::_('COM_BIDS_setting_end_time')."</td>
                <td>
                    H: ".@$settings["end_hour"]."
                    m: ".@$settings["end_minute"]."
                </td>
            </tr>";
        }
        $return .= "<tr>
            <td>".Jtext::_('COM_BIDS_payment_info')."</td>
            <td>
                <input type=\"text\" name=\"payment_info\" value=\"".@$settings["payment_info"]."\" />
            </td>
        </tr>";
        $return .= "<tr>
            <td>".Jtext::_('COM_BIDS_shipment')."</td>
            <td>
                <input type=\"text\" name=\"shipment_info\" value=\"".@$settings["shipment_info"]."\" />
            </td>
        </tr>";
        $return .= "<tr>
            <td>".Jtext::_('COM_BIDS_shipment_price')."</td>
            <td>
                <input type=\"text\" name=\"shipment_price\" value=\"".@$settings["shipment_price"]."\" />
            </td>
        </tr>";

        if ($cfg->bid_opt_global_enable_reserve_price) {
        $return .= "<tr>
                <td>".Jtext::_('COM_BIDS_param_reserve_price_text').":</td>
                <td>".
                    JHTML::_('select.booleanlist','show_reserve','',@$settings['show_reserve'],'com_bids_show','com_bids_hide').
                "</td>
            </tr>";
        }
        $return .= "<tr>
            <td>".Jtext::_('COM_BIDS_param_picture_text').JHTML::_('tooltip',Jtext::_('COM_BIDS_param_picture_help')).":</td>
            <td>".
                JHTML::_('select.booleanlist','picture','',@$settings["picture"],'com_bids_show','com_bids_hide').
            "</td>
        </tr>";
        $return .= "<tr>
            <td>".Jtext::_('COM_BIDS_param_add_picture_text').JHTML::_('tooltip',Jtext::_('COM_BIDS_param_add_picture_help')).":</td>
            <td>".
                JHTML::_('select.booleanlist','add_picture','',@$settings["add_picture"],'com_bids_show','com_bids_hide').
            "</td>
        </tr>";
        if (($cfg->bid_opt_global_enable_bin || $cfg->bid_opt_enable_bin_only)) {
            $return .= "<tr>
                <td>".Jtext::_('COM_BIDS_param_accept_bin_text').JHTML::_('tooltip',Jtext::_('COM_BIDS_param_accept_bin_help')).":</td>
                <td>".
                    JHTML::_('select.booleanlist','auto_accept_bin','',@$settings["auto_accept_bin"]).
                "</td>
            </tr>";
        }
        $return .= "<tr>
            <td>".Jtext::_('COM_BIDS_param_counts_text').JHTML::_('tooltip',Jtext::_('COM_BIDS_param_counts_help')).":</td>
            <td>".
                JHTML::_('select.booleanlist','bid_counts','',@$settings['bid_counts'],'com_bids_show','com_bids_hide').
            "</td>
        </tr>";

        $return .= "<tr>
            <td>".Jtext::_('COM_BIDS_param_max_price_text').JHTML::_('tooltip',Jtext::_('COM_BIDS_param_max_price_help')).":</td>
            <td>".
                JHTML::_('select.booleanlist','max_price','',@$settings['max_price'],'com_bids_show','com_bids_hide').
            "</td>
        </tr>";

        $return .= "</form>";
        $return .= "</table>";
        $return .= "</div>";

		return $return;
	}
}
