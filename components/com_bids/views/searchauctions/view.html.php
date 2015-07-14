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

class bidsViewSearchAuctions extends BidsSmartyView {

    function display() {

        $model = $this->getModel('auctions');

        $lists = array();
        $lists['users'] = JHTML::_('searchform.selectUsers',$model->getState('filters.users') );
        $lists['active_users'] = JHTML::_('searchform.selectActiveUsers', $model->getState('filters.username') );
        $lists['cats'] = JHTML::_('searchform.selectCategory', $model->getState('filters.cat') );
        $lists['tags'] = JHTML::_('searchform.inputTags', $model->getState('filters.tagnames') );
        $lists['after_calendar'] = JHTML::calendar($model->getState('filters.afterd'),'afterd','afterd','%Y-%m-%d');
        $lists['before_calendar'] = JHTML::calendar($model->getState('filters.befored'),'befored','befored','%Y-%m-%d');
        $lists['startprice'] = JHTML::_('searchform.inputPrice', 'startprice', $model->getState('filters.startprice') );
        $lists['endprice'] = JHTML::_('searchform.inputPrice', 'endprice', $model->getState('filters.endprice') );
        $lists['currency'] = JHTML::_('searchform.selectCurrency', 'currency', $model->getState('filters.currency'));
        $lists['inputReset'] = JHTML::_('searchform.inputReset');

        //filters for integration fields

        $profile = BidsHelperTools::getUserProfileObject();
        $integrationArray = $profile->getIntegrationArray();
        foreach($integrationArray as $alias=>$fieldName) {
            if(''!=$fieldName) {
                $lists[$alias] = JHTML::_('searchform._selectProfileIntegrationFilter', $fieldName, $model->getState('filters.user_profile%'.$fieldName) );
            }
        }

        JHTML::_('behavior.calendar');

        JHTML::script(JURI::root() . 'components/com_bids/js/auctions.js');

        $fields = CustomFieldsFactory::getSearchableFieldsList('auctions');
        $fields_html=JHtml::_('customfields.displaysearchhtml',$fields,'divs');

        $this->assign('lists', $lists);
        $this->assign("custom_fields_html", $fields_html );

        parent::display('t_search.tpl');
    }
}
