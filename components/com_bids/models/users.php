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



defined('_JEXEC') or die( 'Restricted access' );

class bidsModelUsers extends JTheFactoryListModel {

    protected $users;
    protected $pagination;

	public function loadUsers() {

        $app	= JFactory::getApplication();
        $cfg = BidsHelperTools::getConfig();
        $jconfig = &JFactory::getConfig();

        if ( $cfg->bid_opt_nr_items_per_page > 0 )
        	$list_limit = $cfg->bid_opt_nr_items_per_page;
        else
			$list_limit = $jconfig->getValue('config.list_limit');

        // Get the pagination request variables
        $this->setState('limit', $app->getUserStateFromRequest('com_bids.limit','limit', $list_limit, 'int'));
        $this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));
        // In case limit has been changed, adjust limitstart accordingly
        $this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) :0));

        $this->setState('filter_order_Dir', $app->getUserStateFromRequest('com_bids.filter_order_Dir', 'filter_order_Dir', "ASC"));
        $this->setState('filter_order', $app->getUserStateFromRequest('com_bids.filter_order','filter_order', "start_date"));

		$query = $this->buildQuery();
		$list = $this->_getList((string) $query, $this->getState('limitstart'), $this->getState('limit'));

		$this->users = $list;
	}

    function setFilters() {

        $this->resetFilters('reset');

        $app	= JFactory::getApplication();

        $knownFilters = array(
            'search_type'=>array('type'=>'int'),
            'keyword'=>array('type'=>'string'),
            'name'=>array('type'=>'string'),
            'country'=>array('type'=>'string'),
            'city'=>array('type'=>'string'),
            'email'=>array('type'=>'string'),
            'im'=>array('type'=>'string'),
        );

        foreach($knownFilters as $keyName=>$attribs) {
            $default = isset($attribs['default']) ? $attribs['default'] : null;
            $type = isset($attribs['type']) ? $attribs['type'] : 'none';
            $value = $app->getUserStateFromRequest($this->context . '.filters.' . $keyName, $keyName, $default, $type );
            if(!empty($value)) {
                $this->setState('filters.'.$keyName, $value );
            }
        }

        //$this->setCustomFilters();
        $profile = BidsHelperTools::getUserProfileObject();
        parent::setCustomFilters($profile);
    }

    function buildQuery()
    {
        $db = JFactory::getDbo();

        $query = JTheFactoryDatabase::getQuery();
        $query->select('`u`.*,`u`.`id` AS userid');
        $query->select('null AS password');
        $query->select('AVG(r.rating) AS rating_overall');
        $query->select('COUNT(DISTINCT au.id) AS nr_auctions');
        $query->select('COUNT(DISTINCT bi.id) AS nr_bids');

        $query->from('#__users','u');
        $query->join('left','#__bid_rate','r','`u`.`id`=`r`.`user_rated_id`');
        $query->join('left','#__bid_auctions','au','`u`.`id`=`au`.`userid` and au.`published`=1');
        $query->join('left','#__bids','bi','`u`.`id`=`bi`.`userid`');

        $profile = BidsHelperTools::getUserProfileObject();
        $this->buildCustomQuery($query,$profile);

        $queriedTables = $query->getQueriedTables();

        //all the filters are profile related; in order to work, we have to append them to the query AFTER the join has been made with the profile tables, in parent::buildCustomQuery()
        $keyword = $this->getState('filters.keyword');
        $name = $this->getState('filters.name');
        $k = $keyword ? $keyword : ( $name ? $name : null );
        if($k) {
            $s = array();

            $table = $profile->getFilterTable('username');
            $field = $profile->getFilterField('username');
            $alias = array_search($table,$queriedTables);
            $s[] = ' (`'.$alias.'`.`'.$field.'` LIKE \'%'.$db->getEscaped($k).'%\') ';

            $table = $profile->getFilterTable('name');
            $field = $profile->getFilterField('name');
            if($table && $field) {
                $alias = array_search($table,$queriedTables);
                $s[] = ' (`'.$alias.'`.`'.$field.'` LIKE \'%'.$db->getEscaped($k).'%\') ';
            }

            $table = $profile->getFilterTable('surname');
            $field = $profile->getFilterField('surname');;
            if($table && $field) {
                $alias = array_search($table,$queriedTables);
                $s[] = ' (`'.$alias.'`.`'.$field.'` LIKE \'%'.$db->getEscaped($k).'%\') ';
            }


            $query->where($s,'OR');
        }

        switch ($this->getState('filters.search_type')){
            case 1:
                $table = $profile->getFilterTable('isBidder');
                $alias = array_search($table,$queriedTables);
                $query->where( ' (`'.$alias.'`.`name`=1 ' );
                break;
            case 2:
                $table = $profile->getFilterTable('isSeller');
                $alias = array_search($table,$queriedTables);
                $query->where( ' (`'.$alias.'`.`name`=1 ' );
                break;
        }

        $filter_order = $this->getState('filters.filter_order');
        if ($filter_order) {
            $filter_order_Dir = $this->getState('filters.filter_order_Dir');
            $query->order( $db->getEscaped($filter_order . ' ' . $filter_order_Dir ) );
        }

        $query->group('`u`.`id`');

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

    function getPagination() {

        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->countTotal(), $this->getState('limitstart'),$this->getState('limit'));
        }
        return $this->_pagination;
    }
}
