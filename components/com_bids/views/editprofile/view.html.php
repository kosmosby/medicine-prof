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

class bidsViewEditProfile extends BidsSmartyView {

    function display($tmpl) {

        $database = JFactory::getDBO();
        $my = JFactory::getUser();
        $cfg = BidsHelperTools::getConfig();

        $lists = array();

        $user = BidsHelperTools::getUserProfileObject($my->id);
        JFilterOutput::objectHTMLSafe( $user, ENT_QUOTES );

        $opts = array();
        $opts[] = JHTML::_('select.option', '', JText::_('COM_BIDS_CHOOSE_COUNTRY'));

        $database->setQuery("SELECT name AS value,name AS text FROM `#__bid_country` WHERE active=1 order by name");
        $opts = array_merge($opts, $database->loadObjectList());

        $lists["country"] = JHTML::_('select.genericlist', $opts, 'country', 'class="inputbox required"', 'value', 'text', $user->country);

        $lists['token'] = JHtml::_('form.token');

        $lists["validate_custom_fields"] = " new Array()";
        $lists["validate_custom_fields_count"] = "0";

        $fields = CustomFieldsFactory::getFieldsList("user_profile");
        $fields_html=JHtml::_('customfields.displayfieldshtml',$user,$fields);

        JHTML::_('behavior.formvalidation');
        JHTML::script(JURI::root().'components/com_bids/js/validator/validator.js');

        $formAction =
                ( !$my->id && 'component'==$cfg->bid_opt_registration_mode && 'component'==$cfg->bid_opt_profile_mode ) ?
                (JUri::root().'index.php?option=com_users&task=registration.register') :
                (JUri::root().'/index.php?option=com_bids&task=saveuserdetails') ;

        $this->assign("custom_fields_html", $fields_html );
        $this->assign("user", $user);
        $this->assign("formAction", $formAction);
        $this->assign("lists", $lists);

        parent::display($tmpl);
    }
}
