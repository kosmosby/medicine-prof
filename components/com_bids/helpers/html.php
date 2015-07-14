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


class BidsHelperHtml {

    static function selectCategory($name, $params=array()) {

        $cfg = class_exists('JTheFactoryHelper') ? BidsHelperTools::getConfig() : new BidConfig();//so it can be used in modules too

        JModelLegacy::addIncludePath(JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'models');
        $model = JModelLegacy::getInstance('bidscategory','bidsmodel');
        $model->loadCategoryTree();

        $lang = JFactory::getLanguage();
        $lang->load('com_bids');

        $treeCat = $model->get('categories');

        $spacer = '&nbsp;&nbsp;&nbsp;';
        $opts = array();
        $opts[] = JHTML::_('select.option', '', JText::_('COM_BIDS_CHOOSE_CATEGORY') );
        foreach($treeCat as $c) {
            $text = '&nbsp;'.str_repeat($spacer,$c->depth).$c->title.PHP_EOL;
            $opts[] = JHTML::_('select.option',$c->id,$text, 'value', 'text', ($cfg->bid_opt_leaf_posting_only && $c->nrSubcategories) );
        }

        $name = isset($params['name']) ? $params['name'] : 'cat';
        $attribs = isset($params['attribs']) ? $params['attribs'] : '';
        $selected = isset($params["select"]) ? $params["select"] : '';

        return JHTML::_('select.genericlist', $opts, $name, $attribs, 'value', 'text', $selected);
    }

    static function selectAuctionType($selected_type) {

        $cfg = BidsHelperTools::getConfig();

        $js_atype = "";
        $opts[] = JHTML::_('select.option', '', JText::_('COM_BIDS_PICK_TYPE_OF_AUCTION'));
        $opts[] = JHTML::_('select.option', AUCTION_TYPE_PUBLIC, JText::_('COM_BIDS_PUBLIC_LABEL'));
        if ($cfg->bid_opt_global_enable_private) {
            $opts[] = JHTML::_('select.option', AUCTION_TYPE_PRIVATE, JText::_('COM_BIDS_PRIVATE_LABEL'));
        }

        if ($cfg->bid_opt_enable_bin_only) {
            // function in auction_edit.js ; 1 - displayes only BIN related fields; 0 displayes all fields
            $js_atype = " onchange='changeAuctionType();'";
            $opts[] = JHTML::_('select.option', AUCTION_TYPE_BIN_ONLY, JText::_('COM_BIDS_BIN_ONLY_LABEL'));
        }
        if (count($opts) == 2) {
            $typelist = "<span id='bold_text'>" . $opts[1]->text . "</span><input type='hidden' name='auction_type' id='auction_type' value='" . $opts[1]->value . "'>";
        } else {
            $typelist = JHTML::_('select.genericlist', $opts, 'auction_type', 'class="inputbox required" ' . $js_atype . ' alt="auction_type"', 'value', 'text', $selected_type);
        }

        return $typelist;
    }

    /**
     * Generate HTML Select With Countries
     *
     * @param varchar $name
     * @param array $params
     * @return unknown
     */
    static function selectCountry($name, $params=array()) {

        $database = JFactory::getDBO();

        if (isset($params["active_only"]) && $params["active_only"] != "")
            $active_only = $params["active_only"];
        else
            $active_only = 1;

        if (isset($params["selected"]) && $params["selected"] != "")
            $filter_country = $params["selected"];
        else
            $filter_country = null;

        if (isset($params["existing_users"]) && $params["existing_users"] != "")
            $existing_users = $params["existing_users"];
        else
            $existing_users = 0;

        $active = "";

        if ($active_only) {
            $active = " WHERE c.active=1";
        }


        $filter = "";
        if ($existing_users)
            $filter = " INNER JOIN #__bid_users AS p ON p.country = c.id ";

        $countryid = array();
        $sql = "SELECT DISTINCT c.id AS countryid, c.name FROM #__bid_country c {$filter} $active ORDER BY name";
        $database->setQuery($sql);

        $countryid[] = JHTML::_('select.option', '0', JText::_("COM_BIDS_CHOOSE"), 'countryid', 'name');
        $countryid = array_merge($countryid, $database->loadObjectList());

        return JHTML::_('select.genericlist', $countryid, $name, ' class="inputbox" ', 'countryid', 'name', $filter_country);
    }

    static function selectUser($name, $params=array()) {

        $database = JFactory::getDbo();

        $where = array();

        $filter_user = null;
        if (isset($params["select"]) && (int) $params["select"] != 0) {
            $filter_user = $params["select"];
        }

        if (isset($params["filter_admin"]) && (int) $params["filter_admin"] == 1) {
            $where[] = "u.usertype!='Super Administrator' and u.usertype!='Administrator'";
        }
        if (isset($params["filter_enabled"]) && (int) $params["filter_enabled"] == 1) {
            $where[] = "u.block!=1";
        }

        $attribs = isset($params['attribs']) ? $params['attribs'] : '';


        $multiple_attr = "";
        if (isset($params["multiple"]) && (int) $params["multiple"] == 1) {
            $name = $name . "[]";
            $multiple_attr = "multiple";
        }

        $query = "select distinct u.* from #__users u ";
        if (count($where) > 0)
            $query .=" WHERE " . implode("AND", $where);

        $database->setQuery($query);
        $users = $database->loadObjectList();

        $useropts = array();
        if (isset($params["select_all"]) && (int) $params["select_all"] == 1 && count($users)) {
            $useropts[] = JHTML::_("select.option", 0, "Select All");
        }

        for ($i = 0; $i < count($users); $i++) {
            $useropts[] = JHTML::_("select.option", $users[$i]->id, $users[$i]->username);
        }

        return JHTML::_("select.genericlist", $useropts, $name, 'class="inputbox" '.$attribs . $multiple_attr, 'value', 'text', $filter_user);
    }

    static function init_captcha() {

        $cfg = BidsHelperTools::getConfig();

        if ($cfg->bid_opt_enable_captcha && $cfg->bid_opt_recaptcha_public_key) {
            if (!function_exists('recaptcha_get_html')) {
                require(JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'recaptcha' . DS . 'recaptchalib.php');
            }

            return recaptcha_get_html($cfg->bid_opt_recaptcha_public_key);
        }
    }

    static function verify_captcha() {

        $cfg = BidsHelperTools::getConfig();

        if (!$cfg->bid_opt_recaptcha_private_key)
            return true;

        if (!function_exists('recaptcha_get_html')) {
            require_once(JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'recaptcha' . DS . 'recaptchalib.php');
        }

        $recaptcha_challenge_field = JRequest::getVar('recaptcha_challenge_field');
        $recaptcha_response_field = JRequest::getVar('recaptcha_response_field');

        $resp = recaptcha_check_answer($cfg->bid_opt_recaptcha_private_key, $_SERVER["REMOTE_ADDR"], $recaptcha_challenge_field, $recaptcha_response_field);
        return $resp->is_valid;
    }

    static function getRatingsSelect() {

        for ($i=1;$i<=10;$i++) {
            $arr[] = JHTML::_('select.option', $i, "$i");
        }

        return JHTML::_('select.radiolist', $arr, 'rate', null);
    }

    static function loadCountdownJS() {
        $cfg = BidsHelperTools::getConfig();

        if ( !$cfg->bid_opt_enable_countdown ) {
            return;
        }

        JHTML::script( JURI::root().'components/com_bids/js/countdown.js');

        $jdoc =  JFactory::getDocument();
        $js_declaration = "var days='" . JText::_("COM_BIDS_DAYS") . ",';	var expired='" . JText::_("COM_BIDS_EXPIRED") . "';";
        $jdoc->addScriptDeclaration($js_declaration);
    }
}
