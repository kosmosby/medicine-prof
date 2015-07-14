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


class JHTMLListAuctions {

    function selectCategory($selectedCategory) {

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

        return JHTML::_('select.genericlist', $opts, 'cat','', 'value', 'text', $selectedCategory);
    }

    function selectMyFilter($selected) {

        $opts = array();
		$opts[] = JHTML::_('select.option', '', JText::_('COM_BIDS_ALL') );
        $opts[] = JHTML::_('select.option', 'active', JText::_('COM_BIDS_ACTIVE_OFFERS') );
        $opts[] = JHTML::_('select.option', 'sold', JText::_('COM_BIDS_VIEW_SOLD_ITEMS') );
        $opts[] = JHTML::_('select.option', 'unsold', JText::_('COM_BIDS_VIEW_UNSOLD_ITEMS') );
        $opts[] = JHTML::_('select.option', 'archived', JText::_('COM_BIDS_VIEW_ARCHIVE') );
        $opts[] = JHTML::_('select.option', 'unpublished', JText::_('COM_BIDS_MY_UNPUBLISHED_OFFERS') );
        $opts[] = JHTML::_('select.option', 'banned', JText::_('COM_BIDS_VIEW_BANNED_ITEMS') );

        return JHTML::_('select.genericlist', $opts, 'filter_archive', 'class="" size="1" onchange="document.auctionForm.submit();"', 'value', 'text', $selected);
    }

    function selectOrder($selected) {
        $opts = array();
        $opts[] = JHTML::_('select.option', 'start_date', JText::_('COM_BIDS_SORT_NEWEST'));
        $opts[] = JHTML::_('select.option', 'start_price', JText::_('COM_BIDS_SORT_INITIALPRICE'));
        $opts[] = JHTML::_('select.option', 'bin_price', JText::_('COM_BIDS_SORT_BINPRICE'));
        $opts[] = JHTML::_('select.option', 'end_date', JText::_('COM_BIDS_SORT_END_DATE'));
        $opts[] = JHTML::_('select.option', 'username', JText::_('COM_BIDS_SORT_USERNAME'));
        $opts[] = JHTML::_('select.option', 'highest_bid', JText::_('COM_BIDS_SORT_BIDPRICE'));

        return JHTML::_('select.genericlist', $opts, 'filter_order', 'class="" onchange="document.auctionForm.submit();"', 'value', 'text', $selected);
    }

    function selectBidType($selected) {

        $opts = array();
        $opts[] = JHTML::_('select.option', 0, JText::_('COM_BIDS_FILTER_AVAILABLE'));
        $opts[] = JHTML::_('select.option', 1, JText::_('COM_BIDS_FILTER_ARCHIVE'));

        $user = JFactory::getUser();
        if ($user->id) {
            $opts[] = JHTML::_('select.option', 3, JText::_('COM_BIDS_FILTER_WATCHLIST'));
        }

        return JHTML::_('select.genericlist', $opts, 'filter_bidtype', 'class="" onchange="document.auctionForm.submit();"', 'value', 'text', $selected);
    }

    function selectOrderDir($selected) {

        $imgSrc = JURI::root().'media/system/images/sort_'.(strtolower($selected)=='desc' ? 'desc' : 'asc').'.png';

        $url = 'javascript:document.auctionForm.filter_order_Dir.value=\''.
                    (strtolower($selected) == 'desc' ? 'ASC' : 'DESC').
                '\';document.auctionForm.submit();';

        return Jhtml::link($url, JHtml::image($imgSrc, $selected));

    }

    function inputKeyword($value) {

        return '<input type="text" id="search_box" name="keyword" size="30" value="'.$value.'" />';
    }

    function inputsHiddenFilters($filters) {

        $html = '';

        foreach($filters as $name=>$filter) {
            if(empty($filter)) {
                continue;
            }

            if(is_array($filter)) {
                foreach($filter as $opt) {
                    $html .= '<input type="hidden" name="'.$name.'[]" value="'.$opt.'" />'.PHP_EOL;
                }
            } else {
                $html .= '<input type="hidden" name="'.$name.'" value="'.$filter.'" />'.PHP_EOL;
            }
        }

        return $html;
    }

    function htmlLabelFilters($filters) {

        $database = JFactory::getDBO();

        $searchstrings = array();
        if ( $filters->get('keyword') ) {
            $searchstrings[JText::_('COM_BIDS_FILTER_KEYWORD')] = $filters->get('keyword');
        }

        if ($filters->get('userid')) {
            $u = JFactory::getUser($filters->get('userid'));
            if($u && !$u->block) {
                $searchstrings[JText::_('COM_BIDS_FILTER_USERS')] = $u->username;
            }
        }

        if($filters->get('users')) {
            $users = array_filter($filters->get('users'));
            foreach ($users as $k => $u) {
                $users[$k] = intval($u);
                if (!$users[$k]) {
                    unset($users[$k]);
                }
            }
            if( count($users) ) {
                $database->setQuery('SELECT username FROM #__users WHERE id IN ('.$database->escape(implode(',',$users)).')');
                $usernames = $database->loadResultArray();
                $searchstrings[JText::_('COM_BIDS_FILTER_USERS')] = implode(',',$usernames);
            }
        }

        $username = $filters->get('username');
        if (!empty($username)) {
            $searchstrings[JText::_('COM_BIDS_FILTER_USERS')] = $username;
        }

        if ($filters->get('cat')) {
            $database->setQuery("SELECT title FROM #__categories WHERE id='" . $database->getEscaped($filters->get('cat')) . "'");
            $catname = $database->loadResult();
            $searchstrings[JText::_('COM_BIDS_FILTER_CATEGORY')] = $catname;
        }
        if ($filters->get('afterd')) {
            $searchstrings[JText::_('COM_BIDS_FILTER_START_DATE')] = $filters->get('afterd');
        }

        if ($filters->get('befored')) {
            $searchstrings[JText::_('COM_BIDS_FILTER_END_DATE')] = $filters->get('befored');
        }

        if ($filters->get('tagid')) {
            $database->setQuery('SELECT tagname FROM #__bid_tags WHERE id='.$database->quote($filters->get('tagid')));
            $searchstrings[JText::_('COM_BIDS_FILTER_TAGS')] = $database->loadResult();
        }
        if ($filters->get('tagnames')) {
            $searchstrings[JText::_('COM_BIDS_FILTER_TAGS_LIKE')] = $filters->get('tagnames');
        }

        if ($filters->get('auction_nr')) {
            $searchstrings[JText::_('COM_BIDS_FILTER_AUCTION_NUMBER')] = $filters->get('auction_nr');
        }

        if ($filters->get('inarch')) {
            $searchstrings[JText::_('COM_BIDS_FILTER_ARCHIVE')] = ($filters->get('inarch')==1) ? JText::_('COM_BIDS_YES') : JText::_('COM_BIDS_NO');
        }

        if ($filters->get('filter_rated')) {
            $searchstrings[JText::_('COM_BIDS_FILTER_RATED')] = JText::_('COM_BIDS_UNRATED');
        }

        if ($filters->get('country')) {
            $searchstrings[JText::_('COM_BIDS_FILTER_COUNTRY')] = $filters->get('country');
        }

        if ($filters->get('city')) {
            $searchstrings[JText::_('COM_BIDS_FILTER_CITY')] = $filters->get('city');
        }

        if ($filters->get('area')) {
            $searchstrings[JText::_('COM_BIDS_FILTER_AREA')] = $filters->get('area');
        }

        if ($filters->get('currency')) {
            if ($filters->get('startprice')) {
                $searchstrings[JText::_('COM_BIDS_PRICE_GT')] = $filters->get('startprice').' '.$filters->get
                ('currency');
            }

            if ($filters->get('endprice')) {
                $searchstrings[JText::_('COM_BIDS_PRICE_LT')] = $filters->get('endprice').' '.$filters->get
                ('currency');
            }
        }

        //integration filter labels
        $profile = BidsHelperTools::getUserProfileObject();
        $integrationArray = $profile->getIntegrationArray();
        foreach($integrationArray as $alias=>$fieldName) {
            if($fieldName) {
                if($fValue = $filters->get('user_profile%'.$fieldName)) {
                    //$searchstrings[JText::_( strtoupper('COM_BIDS_'.$alias) )] = $fValue;
                }
            }
        }

        //custom fields filter labels
        $searchableFields = CustomFieldsFactory::getSearchableFieldsList();
        foreach($searchableFields as $field) {
            $requestKey = $field->page.'%'.$field->db_name;
            if($filters->get($requestKey)) {
                $ftype = CustomFieldsFactory::getFieldType( $field->ftype ? $field->ftype : 'inputbox');
                $searchstrings[JText::_($field->name)] = $ftype->htmlSearchLabel($field, $filters->get($requestKey));;
            }
        }

        return $searchstrings;
    }

    function linkResetFilters($task) {

        $img = JHTML::image(JURI::root().'components/com_bids/images/rm_filter.png','reset_filters','border="0" height="8" style="position:relative;top:1px"');

        return JHTML::link('index.php?option=com_bids&task='.$task.'&reset=all',$img,'title="'.JTEXT::_('COM_BIDS_REMOVE_FILTER').'"');
    }

    static function DisplayFieldsHtml(&$row,$fieldlist,$style='div')
    {
        if (!count($fieldlist)) return null;
        $page=$fieldlist[0]->page;
        $cfg= CustomFieldsFactory::getConfig();

        $category_filter=array();
        if($cfg['has_category'][$page]){
            $db= JFactory::getDBO();
            $db->setQuery("SELECT fid FROM #__".APP_PREFIX."_fields_categories WHERE cid = '".$row->cat."'");
            $category_filter = $db->loadResultArray();
        }
        $flist=array();
        $field_object= JTable::getInstance('FieldsTable','JTheFactory');

        foreach($fieldlist as $field)
        {

            if($field->categoryfilter && !in_array( $field->id,$category_filter ) )
                continue;

            $field_type= CustomFieldsFactory::getFieldType($field->ftype);

            $field_object->bind($field);
            $f=new stdClass();
            $f->field=clone $field;
            $f->value=$row->{$field->db_name};
            $f->html=$field_type->getFieldHTML($field_object,$row->{$field->db_name});
            $flist[]=$f;
        }
        $func='DisplayFieldsHtml_'.ucfirst($style);
        $html=self::$func($flist);
        return $html;
    }

    static function DisplayFieldsHtml_Div($flist)
    {
        if (!count($flist)) return null;
        $html = '<div>';
        foreach($flist as $f)
        {
            $tooltip='';
            if ($f->field->help) $tooltip=JHtml::_('tooltip',$f->field->help);
            $html .=
                '<div class="auction_edit_field_container">
                    <div class="auction_edit_field_label bids_custom_field">'.JText::_($f->field->name).': '.$tooltip.'</div>
                    <div class="auction_edit_field_input">'.$f->html.'</div>
                    <div style="clear: both;"></div>
                </div>';
        }
        $html .= '</div>';
        return $html;
    }
}
