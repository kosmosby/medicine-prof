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

class bidsViewSelectCategory extends BidsSmartyView {

    function display($tpl=null) {

        $catModel = $this->getModel();
        $items = $catModel->get('categories');

        $task = JRequest::getCmd('task');
        if ($task == "selectcat" || $task == "newauction") {
            $task = "edit";
        }

        $this->assign("task", $task);
        $this->assign("categories", $items);

        parent::display($tpl);
    }
}
