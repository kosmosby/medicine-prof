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

class bidsViewCategories extends BidsSmartyView {

    function display($tpl=null) {

        $task = JRequest::getCmd('task');

        $model = $this->getModel('bidscategory');
        $categories = $model->get('categories');

        BidsHelperView::prepareCategoryTree($categories,$task);

        $firstcat = reset($categories);
        $letterFilter = JHtml::_('bidcategories.letterFilter', $firstcat->parent_id);

        $this->assign("categories", $categories);
        $this->assign("letterFilter", $letterFilter);

        JHTML::_("behavior.tooltip");
        JHTML::script(JURI::root().'components/'.APP_EXTENSION.'/js/jquery/jquery.js');
        JHTML::script(JURI::root().'components/'.APP_EXTENSION.'/js/jquery/jquery.noconflict.js');

        parent::display($tpl);
    }
}
