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

jimport('joomla.application.component.model');

class bidsModelSuggestions extends JTheFactoryListModel {

    protected $suggestions,$pagination;

    function setPagination() {

        jimport('joomla.html.pagination');
        $app = JFactory::getApplication();
        $jconfig = &JFactory::getConfig();

        $cfg = BidsHelperTools::getConfig();

        $limit = $cfg->bid_opt_nr_items_per_page>0 ? $cfg->bid_opt_nr_items_per_page : $jconfig->getValue('config.list_limit');
        $this->setState('limit', $app->getUserStateFromRequest($this->context.'limit','limit',$limit));
        $this->setState('limitstart', JRequest::getVar('limitstart', 0, 'default', 'int'));
        // In case limit has been changed, adjust limitstart accordingly
        //$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));

        $this->pagination = new JPagination( $this->countTotal(), $this->getState('limitstart'), $this->getState('limit'));
    }

    function setFilters() {
        $app = JFactory::getApplication();

        $this->setState('filters.filter_bidtype', $app->getUserStateFromRequest($this->context . '.filters.filter_bidtype', 'filter_bidtype', 'ASC'));
        $this->setState('filter_order_Dir', $app->getUserStateFromRequest($this->context . '.filters.filter_order_Dir', 'filter_order_Dir', "ASC"));
        $this->setState('filter_order', $app->getUserStateFromRequest($this->context . '.filters.filter_order', 'filter_order', "start_date"));
    }

    function loadSuggestions() {

        $this->setPagination();

        $db = &JFactory::getDBO();
        $db->setQuery($this->buildQuery(),$this->pagination->get('limitstart'),$this->pagination->get('limit'));

        $this->suggestions = $db->loadObjectList();
    }

    function buildQuery() {

        $my = &JFactory::getUser();

        $query = JTheFactoryDatabase::getQuery();

        $query->from('#__bid_suggestions','a');

        $query->select('a.id AS parent_message');
        $query->select('a.bid_price AS suggested_price');
        $query->select('a.modified AS bid_date');
        $query->select('a.status AS accept');
        $query->select('a.parent_id AS resuggest');
        $query->select('a.userid AS author_id');
        $query->select('a.quantity');
        $query->select('b.*');
        $query->select('u.name AS name');
        $query->select('u.username');
        $query->select('ss.userid AS repliedto');
        $query->select('a.quantity AS quantity');
        $query->select('GROUP_CONCAT(`pics`.`picture` ORDER BY `pics`.`ordering`) AS pictures');

        $query->join('left','#__bid_suggestions','ss','`a`.`parent_id`=`ss`.`id`');
        $query->join('left','#__bid_auctions','b','`a`.`auction_id`=`b`.`id`');
        $query->join('left','#__users','u','`u`.`id`=`a`.`userid`');
        $query->join('left','#__bid_pictures', 'pics', '`b`.`id`=`pics`.`auction_id`');

        if ($this->getState('filters.mysuggestions')) {
            $query->where('a.userid='.$my->id);
        } else {
            $query->where('a.userid<>'.$my->id);
        }

        $query->where('b.published=1');


/*        switch ( $this->getState('filters.filter_bidtype') ) {
            case '0':
                $query->where('b.close_offer=0');
                break;
            case '1':
                $query->where('b.close_offer=1');
                break;
        }*/

        return $query;
    }

    private function countTotal() {

        $query = $this->buildQuery();

        $queryCount = JTheFactoryDatabase::getQuery();
        $queryCount->select('1');

        foreach($query->get('from') as $from) {
            $queryCount->from($from['tableName'],$from['tableAlias']);
        }
        foreach($query->get('join') as $type=>$joins) {
            foreach($joins as $join) {
                $queryCount->join($type,$join['tableName'],$join['tableAlias'],$join['joinOn']);
            }
        }
        $queryCount->where($query->get('where'));
        $queryCount->group($query->get('group'));
        $queryCount->having($query->get('having'));

        $db = JFactory::getDbo();
        $db->setQuery( 'SELECT COUNT(1) FROM ('. (string) $queryCount .') AS allAuctions ');

        return $db->loadResult();
    }

}
