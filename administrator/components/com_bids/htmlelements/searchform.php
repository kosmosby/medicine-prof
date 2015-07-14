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



class JHTMLSearchForm {

    function inputKeyword($value) {
        
    }

    function selectCategory($selected=null) {

        $cfg = BidsHelperTools::getConfig();
        $cat = JModel::getInstance('bidscategory','bidsModel');

        $cat->loadCategoryTree();
        $treeCat = $cat->get('categories');

        $spacer = '&nbsp;&nbsp;&nbsp;';
        $opts = array();

        $opts[] = JHTML::_('select.option',0,JText::_('COM_BIDS_ALL_CATEGORIES'));
        foreach($treeCat as $c) {
            $text = '&nbsp;'.str_repeat($spacer,$c->depth).$c->title.PHP_EOL;
            $opts[] = JHTML::_('select.option',$c->id,$text, 'value', 'text', ($cfg->bid_opt_leaf_posting_only && $c->nrSubcategories) );
        }

        return JHTML::_('select.genericlist', $opts, 'cat','', 'value', 'text', $selected);
    }

    function selectUsers($selected=null) {

        $database =  JFactory::getDBO();
        $my =  JFactory::getUser();

        $query = "select u.id AS value, u.username AS text
                from #__users u
				where u.id != '$my->id' and
				u.usertype!='Super Administrator' and u.usertype!='Administrator' and u.block!=1";
        $database->setQuery($query);
        $users = $database->loadObjectList();

        $useropts = array_merge(array(JHTML::_('select.option', '', JText::_('COM_BIDS_ALL') )), $users);

        return JHTML::_('select.genericlist', $useropts, 'users[]', 'class="inputbox"  style="width:190px;" size="10" multiple', 'value', 'text', $selected);
    }

    function selectActiveUsers($selected=null) {

        return '<input type="text" name="username" class="inputbox" value="'.$selected.'" />';

    }

    function _selectProfileIntegrationFilter($filterName, $filterValue=null) {

        static $filters = array();

        if(!isset($filters[$filterName])) {
            $db = JFactory::getDbo();

            $profile = BidsHelperTools::getUserProfileObject();

            $tableField = $profile->getFilterField($filterName);
            $tableName = $profile->getFilterTable($filterName);

            $query = 'SELECT DISTINCT `'.$tableField.'` AS value, `'.$tableField.'` AS text FROM `'.$tableName.'`'.
                     //no empty values
                     'WHERE `'.$tableField.'`<>\'\'';
            $db->setQuery($query);
            $rows = array();
            $rows[] = JHTML::_('select.option', '', JText::_('COM_BIDS_ALL') );
            $rows = array_merge($rows, $db->loadObjectList());

            $filters[$filterName] = JHTML::_('select.genericlist', $rows, 'user_profile%'.$tableField, 'class="inputbox"', 'value', 'text', $filterValue);
        }

        return $filters[$filterName];
    }

    function inputPrice($name,$price) {

        $price = $price ? number_format($price,2) : '';

        return '<input type="text" name="'.$name.'" value="'.$price.'" size="5" />';
    }

    function selectCurrency($selected) {

        $db = JFactory::getDbo();
        $q = $db->getQuery(true);
        $q->select('name AS value, name AS text')
            ->from('#__bid_currency')
            ->order('`default` DESC, ordering ASC');
        $db->setQuery($q);
        $currencies = $db->loadAssocList();

        return JHtml::_('select.genericlist',$currencies,'currency','','value','text',$selected);
    }

    function inputTags($value) {

        $html = '<input type="text" name="tagnames" class="inputbox" size="30" value="'.$value.'" />';

        return $html;
    }

    function inputReset() {
        return '<input type="hidden" name="advancedSearchReset" value="1" />';
    }
}
