<?php

defined('_JEXEC') or die('Restricted access');

class JBidsAdminControllerRatings extends JController
{
    var $table='';
    function __construct()
    {
        parent::__construct();
        $this->table='`#__'.APP_PREFIX.'_rate`';

        JLoader::register('JBidsAdminView', JPATH_COMPONENT_ADMINISTRATOR . DS . 'views' . DS . 'view.php');
    }
    function execute($task)
    {
        if (file_exists(JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.ratings.php'))
            require JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.ratings.php';

        parent::execute($task);
    }
    function Listing()
    {

		$db		=  JFactory::getDBO();
		$app	= JFactory::getApplication();

		$context			= 'com_bids.ratings';
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');

		$searchfilter	= $app->getUserStateFromRequest($context.'searchfilter', 'searchfilter', '', 'string');

		$where = array();

		if($searchfilter!="") {
			$where[]= " review LIKE '%{$searchfilter}%' ";
		}

		$whereSQL = "";
		if(count($where)>0){
			$whereSQL = "WHERE ".implode("AND",$where);
		}

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ( $limit != 0 ? (floor($limitstart / $limit) * $limit) : 0 );

		$db->setQuery("SELECT COUNT(*) FROM {$this->table} $whereSQL ");
		$total = $db->loadResult();

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$db->setQuery("SELECT r.*,a.title,u1.username username1,u2.username username2 FROM {$this->table} r 
            left join #__bid_auctions a on r.auction_id=a.id 
            left join #__users u1 on r.voter_id=u1.id
            left join #__users u2 on r.user_rated_id=u2.id ". $whereSQL ,
            $pagination->limitstart, $pagination->limit);
		$rows = $db->loadObjectList();

        $view=$this->getView('ratings','html');
        $view->assignRef('ratings',$rows);
        $view->assignRef('pagination',$pagination);

        $view->assign('search',$searchfilter);
        $view->display();

    }
    function Delete()
    {
		$cid	= JRequest::getVar( 'cid', array(), '', 'array' );
		if(count($cid>0)){
			$cids = implode(",",$cid);
    		$db		=  JFactory::getDBO();
			$db->setQuery("DELETE FROM {$this->table} WHERE id IN ($cids)");
			$db->query();
		}
		$this->setRedirect("index.php?option=com_bids&task=ratings.listing",JText::_("COM_BIDS_REVIEWS_DELETED"));

    }
    
}
