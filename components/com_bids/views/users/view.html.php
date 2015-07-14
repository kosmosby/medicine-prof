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

class bidsViewUsers extends BidsSmartyView {

    function display($tpl=null) {


        $app = JFactory::getApplication();
        $uri = JFactory::getURI();
        $params = $app->getParams();

        $model = $this->getModel('users');

        $items = $model->get('users');
        if ($items)
            for ($key = 0; $key < count($items); $key++) {
                $user = $items[$key];
                $user->rownr = $key;
                $user->link = JRoute::_("index.php?option=com_bids&task=userdetails&id={$user->id}");
                $items[$key] = $user;
            }

        //add alternate feed link
        if ($params->get('show_feed_link', 1) == 1) {
            $document = JFactory::getDocument();
            $link = '&format=feed&limitstart=';
            $attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
            $document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
            $attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
            $document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
        }

        $filters = $model->getFilters();
        $pagination = $model->getPagination();

        $this->assign("action", JRoute::_(JFilterOutput::ampReplace($uri->toString())));
        $this->assign("users", $items);
        $this->assign("sfilters", $filters);

        $this->assign("pagination", $pagination);

        if ($params->get('show_page_title', 1)) {
            $page_title = $this->escape($params->get('page_title', "Show users"));
            $this->assign("page_title", $page_title);
        }
        $this->assign("pageclass_sfx", $params->get('pageclass_sfx'));

        JHTML::_("behavior.modal");
        JHTML::script( JURI::root().'components/com_bids/js/startup.js' );

        parent::display($tpl);
    }
}
