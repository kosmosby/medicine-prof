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

class bidsViewSearchUsers extends BidsSmartyView {

    function display() {
        $app = JFactory::getApplication();
        // Get the page/component configuration
        $params = $app->getParams();
        $database = JFactory::getDBO();

        $searchType = $params->get("user_type");
        $this->assign("search_type", $searchType);


        if ($params->get('show_page_title', 1)) {

            $page_title = $this->escape($params->get('page_title'));
            if ($page_title == "") {
                $page_title = JText::_("COM_BIDS_SEARCH_USERS");
            }
            $this->assign("page_title", $page_title);
        }

        $extra_field_list = $params->get('extra_fields', array());

        $lists['country']["label"] = "Country";
        $lists['country']["html"] = BidsHelperHtml::selectCountry("country");

        JHTML::_('behavior.calendar');

        $profileMode = BidsHelperTools::getProfileMode();
        if('component'==$profileMode) {
            $fields = CustomFieldsFactory::getSearchableFieldsList('user_profile');
            $lists['custom_fields'] = JHtml::_('customfields.displaysearchhtml',$fields);
            //$this->assign('customFilters', $customFilters);
        }

        $lists["city"]["label"] = JText::_("COM_BIDS_CITY");
        $lists["city"]["html"] = '<input type="text" name="city" />';
        $lists["name"]["label"] = JText::_('COM_BIDS_NAME');
        $lists["name"]["html"] = '<input type="text" name="name" />';

        $this->assign('lists', $lists);

        $this->assign("search_fields", $extra_field_list);

        parent::display('elements/search/t_search_users.tpl');
    }
}
