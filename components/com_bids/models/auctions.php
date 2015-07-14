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



// no direct access
defined('_JEXEC') or die('Restricted access');

class bidsModelAuctions extends JTheFactoryListModel {

    protected $auctions,$pagination;
    private $_searchWithCurrency;

    protected $order_fields = array(
        'start_date' => 'a.start_date',
        'start_price' => 'a.initial_price',
        'bin_price' => 'a.BIN_price',
        'end_date' => 'a.end_date',
        'username' => 'u.username',
        'highest_bid' => 'highest_bid',

        'title' => 'a.title',
        'hits' => 'a.hits',
        'catname' => 'cat.title',
        'id' => 'a.id',
        'nr_bidders' => 'nr_bidders',
        'nr_bids' => 'nr_bids'
    );

    public function setFilters() {

        $app = JFactory::getApplication();

        $this->resetFilters('reset');

        if($app->input->get('advancedSearchReset')) {
            $app->setUserState( $this->context.'.filters', null );
        }

        $knownFilters = array(
            'keyword'=>array('type'=>'string'),
            'indesc'=>array('type'=>'int'),
            'inarch'=>array('type'=>'int'),
            'filter_bidtype'=>array('type'=>'int'),
            'filter_type'=>array('type'=>'string'),
            'filter_archive'=>array('type'=>'string'),
            'users'=>array('type'=>'array'),
            'username'=>array('type','string'),
            'cat'=>array('type'=>'int'),
            'tagid'=>array('type'=>'string'),
            'tagnames'=>array('type'=>'string'),
            'afterd'=>array('type'=>'string'),
            'befored'=>array('type'=>'string'),
            'auction_nr'=>array('type'=>'string'),
            'startprice'=>array('type'=>'float'),
            'endprice'=>array('type'=>'float'),
            'currency'=>array('type'=>'string'),

            'filter_order'=>array('type'=>'string','default'=>'start_date'),
            'filter_order_Dir'=>array('type'=>'string','default'=>'DESC')
        );

        foreach($knownFilters as $keyName=>$attribs) {
            $default = isset($attribs['default']) ? $attribs['default'] : null;
            $type = isset($attribs['type']) ? $attribs['type'] : 'none';

            $value = $app->getUserStateFromRequest($this->context . '.filters.' . $keyName, $keyName, $default, $type );
            if(!empty($value)) {
                $this->setState('filters.'.$keyName, $value );
            }
        }

        //this sets the model's filters according to custom fields
        $profile = BidsHelperTools::getUserProfileObject();
        parent::setCustomFilters($profile);
    }

    private function buildQuery() {

        $user = JFactory::getUser();
        $db = JFactory::getDBO();
        $cfg = BidsHelperTools::getConfig();

        $query = JTheFactoryDatabase::getQuery();

        $query->from('#__bid_auctions','a');

        //SELECTS
        $query->select('`a`.*');
        $query->select('`a`.id AS auctionId');
        //$query->select('`cat`.`id` as cati, `cat`.`catname`, `catsef`.`categories` as catslug');
        $query->select('`cat`.`id` as cati, `cat`.`title` AS categoryname');
        $query->select('`u`.`username`');
        $query->select('GROUP_CONCAT(DISTINCT `t`.`id`) AS tagIds');
        $query->select('GROUP_CONCAT(DISTINCT `t`.`tagname`) AS tagNames');
        $query->select('GROUP_CONCAT(`pics`.`picture` ORDER BY `pics`.`ordering`) AS pictures');
        $query->select('COUNT(DISTINCT bids.userid) AS nr_bidders');
        $query->select(
            'CASE a.auction_type
                WHEN '.AUCTION_TYPE_PUBLIC.' THEN MAX(bids.bid_price)
                WHEN '.AUCTION_TYPE_PRIVATE.' THEN a.initial_price
                WHEN '.AUCTION_TYPE_BIN_ONLY.' THEN a.bin_price
            END AS highest_bid'
        );
        //NEXT ONE IS USED FOR PRICE RANGE SEARCHES
        $query->select(
            'CASE a.auction_type
                WHEN ' . AUCTION_TYPE_PUBLIC . ' THEN IF(MAX(bids.bid_price)>0,MAX(bids.bid_price),a.initial_price)
                WHEN ' . AUCTION_TYPE_PRIVATE . ' THEN a.initial_price
                WHEN ' . AUCTION_TYPE_BIN_ONLY . ' THEN a.bin_price
            END AS current_price'
        );
        $query->select('GROUP_CONCAT(IF(bids.userid='.$user->id.',bids.bid_price,NULL)) AS mybid');
        $query->select('AVG(ru.rating) AS rating_overall');
        $query->select('IF(ru.rate_type=\'auctioneer\',AVG(ru.rating),0) as rating_auctioneer');
        $query->select('IF(ru.rate_type=\'bidder\',AVG(ru.rating),0) as rating_bidder');
        if($cfg->bid_opt_quantity_enabled) {
            $query->select('bids.quantity AS nr_items');
        } else {
            $query->select('1 AS nr_items');
        }

        //JOINS
        $query->join('left','#__bids','bids','`bids`.`auction_id`=`a`.`id`');
        $query->join('left','#__bid_rate','ru','ru.user_rated_id=`a`.`userid`');
        $query->join('left','#__categories','cat','`a`.`cat`=`cat`.`id`');
        $query->join('left','#__bid_tags','t','`a`.`id`=`t`.`auction_id`');
        $query->join('left','#__bid_pictures','pics','`a`.`id`=`pics`.`auction_id`');

        //WHERE conditions
        if ($user->id) {
            $query->select('IF ( `a`.`userid` = \'' . $user->id . '\', 1, 0 ) AS `isMyAuction`');
            $query->select('IF ( `fav_table`.`id`>0, 1, 0 )  AS favorite');
        } else {
            $query->select('0 AS `isMyAuction`');
            $query->select('-1 AS `favorite`');
        }

        if($this->getState('behavior')!='mywatchlist') {
            $query->join('left','#__bid_watchlist','fav_table','`fav_table`.`auction_id`=`a`.`id` AND `fav_table`.`userid`='.$user->id);
        }

        switch($this->getState('behavior')) {

            case 'myauctions':
                $query->where('`a`.`userid` = \'' . $user->id . '\'');

                switch ($this->getState('filters.filter_archive')) {                    
                    case 'active':
                        $query->where('`a`.`close_by_admin`=0');
                        $query->where('`a`.`close_offer`=0');
                        $query->where('`a`.`published`=1');
                        break;
                    case 'unpublished':
                        $query->where('`a`.`close_by_admin`=0');
                        $query->where('`a`.`close_offer`=0');
                        $query->where('`a`.`published`=0');
                        break;
                    case 'archived':
                        $query->where('`a`.`close_offer`=1');
                        break;
                    case 'unsold':
                        $query->where('`a`.`close_by_admin`=0');
                        $query->where('`a`.`close_offer`=1');
                        $query->where('`bids`.`accept`=0 OR `bids`.`accept` IS NULL');
                        break;
                    case 'sold':
                        $query->where('`a`.`close_by_admin`=0');
                        //auction_type=3 --> BIN Only with items sold                    
                        $query->where('(`a`.`close_offer`=1 and `bids`.`accept`=1) OR (`a`.`auction_type`=3 and `a`.`quantity`<`a`.`nr_items`)');
                        break;
					case 'banned':
                        $query->where('`a`.`close_by_admin`=1');
                        break;
                }

                $query->where(array('`cat`.`published`=1','`cat`.`published` IS NULL'),'OR');
                if ($this->getState('filters.cat')) {
                    if (!$cfg->bid_opt_inner_categories) {
                        $query->where(" a.cat= '" . $db->escape($this->getState('filters.cat')) . "' ");
                    } else {
                        $catModel = BidsHelperTools::getCategoryModel();

                        $cTree = $catModel->getCategoryTree($this->getState('filters.cat'),false);
                        $cat_ids = array();
                        if ($cTree) {
                            foreach ($cTree as $cc) {
                                if ($cc->id)
                                    $cat_ids[] = $cc->id;
                            }
                        }
                        if (count($cat_ids)) {
                            $cat_ids = implode(',', $cat_ids);
                        }
                        $query->where(' a.cat IN (' . $db->escape($cat_ids) . ') ');
                    }
                }
                break;

            case 'mybids':
                //need a second join with table #__bids, for filtering purposes
                $query->join('left','#__bids','bids2','`bids2`.`auction_id`=`a`.`id`');
                $query->where('`bids2`.`userid` = \'' . $user->id . '\'');
                //get my proxy
                $query->select('proxy.max_proxy_price AS my_proxy_bid');
                $query->join('left','#__bid_proxy','proxy','`proxy`.`auction_id`=`a`.`id` AND `proxy`.`user_id`='.$user->id);
                $query->select('bids2.modified AS mybid_date');

                switch ($this->getState('filters.filter_bidtype')) {
                    default:
                    case 0:
                        $query->where('`a`.`close_offer`=0');
                        $query->where('`a`.`close_by_admin`=0');
                        break;
                    case 1:
                        $query->where('`a`.`close_by_admin`=0');
                        $query->where('`a`.`close_offer`=1');
                        break;
                }
                break;

            case 'mywonbids':
                $query->where('`bids`.`userid`='.intval($user->id));
                $query->where('`bids`.`accept`=1');
                break;

            case 'mywatchlist':
                $query->join('inner','#__bid_watchlist','fav_table','`fav_table`.`auction_id`=`a`.`id` AND `fav_table`.`userid`='.intval($user->id));
                break;

            case 'listauctions':

                $query->where('`a`.`close_by_admin`=0');

                switch ($this->getState('filters.filter_type')) {
                    default:
                    case 'all':
                        //nothing
                        break;
                    case 'auctions_only':
                        $query->where('a.BIN_price<=0');
                        break;
                    case 'bin_only':
                        $query->where('a.BIN_price>0');
                        break;
                }

                if (!$this->getState('filters.inarch')) {
                    $query->where('`a`.`published`=1');
                    $query->where('a.close_offer=0');
                }

                if ($this->getState('filters.keyword')) {

                    $keyword = $db->escape($this->getState('filters.keyword'));

                    $w = array();
                    $w[] = 'a.title LIKE \'%' . $keyword . '%\'';
                    if ($this->getState('filters.indesc')) {
                        $w[] = 'a.shortdescription LIKE \'%' . $keyword . '%\'';
                        $w[] = 'a.description LIKE \'%' . $keyword . '%\'';
                    }
                    $query->where($w,'OR');
                }

                if ($this->getState('filters.userid')) {
                    $query->where(" a.userid = '" . $db->escape($this->getState('filters.userid')) . "' ");
                }

                $users = (array) $this->getState('filters.users');
                $users = array_filter($users);
                foreach ($users as $k => $u) {
                    $users[$k] = intval($u);
                    if (!$users[$k]) {
                        unset($users[$k]);
                    }
                }
                if (count($users)) {
                    $query->where(' a.userid IN (' . $db->escape(implode(',', $users)) . ') ');
                }

                $username = $this->getState('filters.username');
                if( !empty($username) ) {
                    $query->where(' u.username LIKE \'%' . $db->escape($username) . '%\' ');
                }

                if ( $this->getState('filters.tagnames') ) {
                    $query->join('left','#__bid_tags','search_tagname2','`a`.`id`=`search_tagname2`.`auction_id`');



                    $query->where(' search_tagname2.tagname LIKE \'%' . $db->escape($this->getState('filters.keyword')) . '%\' ');
                }
                if ($this->getState('filters.tagid')) {
                    $query->join('left','#__bid_tags','search_tagid','`a`.`id`=`search_tagid`.`auction_id`');
                    $query->join('left','#__bid_tags','search_tagname','`search_tagid`.`tagname`=`search_tagname`.`tagname`');
                    $query->where(" search_tagname.id='" . $db->escape($this->getState('filters.tagid')) . "'");
                }


                if ($this->getState('filters.auction_nr')) {
                    $query->where(" a.auction_nr ='" . $db->escape($this->getState('filters.auction_nr')) . "' ");
                }

                $query->where(array('`cat`.`published`=1','`cat`.`published` IS NULL'),'OR');
                if ($this->getState('filters.cat')) {
                    if (!$cfg->bid_opt_inner_categories) {
                        $query->where(" a.cat= '" . $db->escape($this->getState('filters.cat')) . "' ");
                    } else {
                        $catModel = BidsHelperTools::getCategoryModel();

                        $cTree=$catModel->getCategoryTree($this->getState('filters.cat'),false);
                        $cat_ids = array();
                        if ($cTree) {
                            foreach ($cTree as $cc) {
                                if ($cc->id)
                                    $cat_ids[] = $cc->id;
                            }
                        }
                        if (count($cat_ids)) {
                            $cat_ids = implode(',', $cat_ids);
                            $query->where(' a.cat IN (' . $db->escape($cat_ids) . ') ');
                        }
                    }
                }

                if ($this->getState('filters.afterd')) {
                    $query->where(' a.start_date>=\'' . $db->escape($this->getState('filters.afterd')) . '\'');
                } elseif(!$this->getState('filters.inarch')) {
                    $query->where(' a.start_date<=UTC_TIMESTAMP()');
                }

                if ($this->getState('filters.befored')) {
                    $query->where('a.end_date<=\'' . $db->escape($this->getState('filters.befored')) . '\'');
                } elseif(!$this->getState('filters.inarch')) {
                    $query->where(' a.end_date>=UTC_TIMESTAMP()');
                }

                if($this->getState('filters.currency')) {
                    $q = $db->getQuery(true);
                    $q->select( '`name`,`convert`' )
                        ->from( '#__bid_currency' )
                    ;
                    $db->setQuery($q);
                    $currencies = $db->loadAssocList('name');

                    if(isset($currencies[$this->getState('filters.currency')])) {

                        $query->select('curr.convert AS auctionCurrencyConversionRate');
                        $query->join('left','#__bid_currency','curr','a.currency=curr.name');

                        $this->_searchWithCurrency = true;

                        $searchCurrencyConversionRate = $currencies[$this->getState('filters.currency')]['convert'];

                        if ($this->getState('filters.startprice')) {
                            $query->having(
                                'current_price>=('.$searchCurrencyConversionRate.'/COALESCE
                                (auctionCurrencyConversionRate,1))*' .
                                $db->escape($this->getState('filters.startprice')) . ' '
                            );
                        }
                        if ($this->getState('filters.endprice')) {
                            $query->having(
                                'current_price<=(' . $searchCurrencyConversionRate . '/COALESCE
                                (auctionCurrencyConversionRate,1))*' .
                                $db->escape($this->getState('filters.endprice')) . ' '
                            );
                        }
                    }
                }

                if($user->id) {
                    $query->select('1-MIN(msg.wasread) AS nrNewMessages');
                    $query->join('left','#__bid_messages','msg', 'a.id=msg.auction_id AND msg.userid2='.$user->id);
                }

                break;
        }

        // Featurings first
        if($this->getState('behavior')=='mywatchlist') {
            $query->order('`a`.`end_date` ASC');
        } else {
            $query->order('`a`.`featured`=\'featured\' DESC');
            $query->order('`a`.`featured`=\'none\' DESC');
        }


        // Required ordering filter
        $filter_order = (string) $this->getState('filters.filter_order');
        if ($filter_order) {
            $filter_order_Dir = $this->getState('filters.filter_order_Dir');
            $query->order( $db->escape($this->order_fields[$filter_order] . ' ' . $filter_order_Dir ) );
        } else {
            $query->order('`a`.`end_date` ASC');
        }

        $query->group('`a`.`id`');

        $profile = BidsHelperTools::getUserProfileObject();
        $additionalFields = array('paypalemail');
        //this binds to the query object everything that is related to custom fields
        parent::buildCustomQuery($query,$profile,'`a`.`userid`',$additionalFields);

        return $query;
    }

    private function countTotal() {

        $query = $this->buildQuery();

        $queryCount = JTheFactoryDatabase::getQuery();
        $queryCount->select('a.id');
        $queryCount->select(
                    'CASE a.auction_type
                        WHEN '.AUCTION_TYPE_PUBLIC.' THEN MAX(bids.bid_price)
                        WHEN '.AUCTION_TYPE_PRIVATE.' THEN a.initial_price
                        WHEN '.AUCTION_TYPE_BIN_ONLY.' THEN a.bin_price
                    END AS current_price'
                );

        if ($this->_searchWithCurrency) {
            $queryCount->select('curr.convert AS auctionCurrencyConversionRate');
        }

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

    private function setPagination() {

        jimport('joomla.html.pagination');
        $app = JFactory::getApplication();
        $jconfig = JFactory::getConfig();
        $cfg = BidsHelperTools::getConfig();

        $jinput = $app->input;

        $limit = $cfg->bid_opt_nr_items_per_page>0 ? $cfg->bid_opt_nr_items_per_page : $jconfig->get('config.list_limit');
        $this->setState('limit', $app->getUserStateFromRequest($this->context.'limit','limit',$limit));

        $this->setState('limitstart', $jinput->get('limitstart', 0, 'default', 'int'));
        //In case limit has been changed, adjust limitstart accordingly
        //$this->setState('limitstart', ($this->getState('limit') != $prevLimit ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));

        $this->pagination = new JPagination( $this->countTotal(), $this->getState('limitstart'), $this->getState('limit'));
    }

    public function loadAuctions() {

        $this->setPagination();

        $db = JFactory::getDBO();
        $db->setQuery( (string) $this->buildQuery(), $this->get('pagination')->limitstart, $this->get('pagination')->limit );
        //echo '<pre>';print_r((string)$this->buildQuery());exit;
        $this->auctions = $db->loadObjectList('auctionId');

        //in order to get the correct total price for the "pay now" button, we need all the winning bids for every auction AND all the shipment prices
        $auctionIds = array_keys($this->auctions);
        if('mywonbids'==$this->getState('behavior') && count($auctionIds) ) {

            $q = 'SELECT a.id AS auctionId, b.*
                    FROM #__bid_auctions AS a
                    LEFT JOIN #__bids AS b
                        ON a.id=b.auction_id AND b.accept=1
                  WHERE a.id IN ('.implode(',',$auctionIds).')';
            $db->setQuery($q);

            $wonBids = $db->loadObjectList();
            foreach($wonBids as $b) {
                $this->auctions[$b->auctionId]->wonBids[] = $b;
            }

            //load shipping prices
            $db->setQuery('SELECT sp.*,sz.name
                            FROM #__bid_shipment_prices AS sp
                            LEFT JOIN #__bid_shipment_zones AS sz
                                ON sp.zone=sz.id
                            WHERE sp.auction IN ('.implode(',',$auctionIds).')');

            $shipmentPrices = $db->loadObjectList();

            foreach($shipmentPrices as $sp) {
                $this->auctions[$sp->auction]->shipmentPrices[] = $sp;
            }

        }
    }

    function getOtherAuctionsList($userid,$excludeIds=array(),$limit=5,$random=true) {

        $db = $this->getDBO();

        $excludeIds = (array) $excludeIds;

        $query = '
            SELECT
                a.*,
                b.name,b.username,
                cats.title AS category_title,
                cats.id as cid,
                GROUP_CONCAT(p.picture ORDER BY p.ordering) AS imagelist
            FROM #__bid_auctions AS a
            LEFT JOIN #__users as b ON b.id=a.userid
            LEFT JOIN #__categories AS cats ON a.cat=cats.id
            LEFT JOIN #__bid_pictures AS p ON a.id=p.auction_id
            WHERE
                a.userid='.$db->Quote($userid).
                ' AND a.start_date<=UTC_TIMESTAMP()
                AND a.end_date>=UTC_TIMESTAMP()
                AND a.close_offer=0
                AND a.close_by_admin=0 '.
                (count($excludeIds) ? (' AND a.id NOT IN ('.implode(',',$excludeIds).') ') : '').
            ' GROUP BY a.id '.
            ($random ? ' ORDER BY RAND() ' : '');

        $db->setQuery($query, 0, $limit);
        $rows = $db->loadObjectList();

        //"cast" all records as JTableAuction
        foreach($rows as &$r) {
            $aux = JTable::getInstance('auction');
            $props = get_object_vars($r);
            foreach($props as $k=>$v) {
                $aux->$k = $v;
            }
            $aux->imagelist = explode(',',$r->imagelist);
            $r = $aux;
            unset($aux);
        }

        return $rows;
    }
}
