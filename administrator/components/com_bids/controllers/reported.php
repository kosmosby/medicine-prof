<?php

defined('_JEXEC') or die('Restricted access');

class JBidsAdminControllerReported extends JController
{
    var $table='';
    function __construct()
    {
        parent::__construct();
        $this->table='`#__'.APP_PREFIX.'_report_auctions`';

        JLoader::register('JBidsAdminView', JPATH_COMPONENT_ADMINISTRATOR . DS . 'views' . DS . 'view.php');
    }
    function execute($task)
    {
        if (file_exists(JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.reported.php'))
            require JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.reported.php';

        parent::execute($task);
    }
    function Listing()
    {

		$db		=  JFactory::getDBO();
		$app	= JFactory::getApplication();

		$context			= 'com_bids.reported';
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');

		$activefilter	= $app->getUserStateFromRequest($context.'activefilter', 'activefilter', 0, 'int');

		$options = array();
		$options[] = JHTML::_('select.option',0,JText::_("COM_BIDS_ALL"));
		$options[] = JHTML::_('select.option',1,JText::_("COM_BIDS_UNSOLVED"));
        $options[] = JHTML::_('select.option',2,JText::_("COM_BIDS_SOLVED"));
		$active_html = JHTML::_('select.genericlist', $options, "activefilter",'class="inputbox" onchange="javascript:document.adminForm.submit();" ','value', 'text', $activefilter);

		$where = array();
		if($activefilter){
			$where[]= " solved ='".($activefilter-1)."' ";
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

		$db->setQuery("SELECT a.*,b.title,u.username FROM {$this->table} a
                left join #__bid_auctions b on a.auction_id=b.id
                left join #__users u on a.userid=u.id
            $whereSQL ", $pagination->limitstart, $pagination->limit);
		$rows = $db->loadObjectList();
        //var_dump($db->getQuery());exit;
        $view=$this->getView('reported','html');
        $view->assignRef('reported',$rows);
        $view->assignRef('pagination',$pagination);

        $view->assignRef('active_filter',$active_html);
        $view->display();

    }
    function Toggle()
    {
		$cid	= JRequest::getVar( 'cid', array(), '', 'array' );
		if(count($cid>0)){
			$cids = implode(",",$cid);
    		$db		=  JFactory::getDBO();
			$db->setQuery("UPDATE {$this->table} SET solved = 1- solved WHERE id IN ($cids)");
			$db->query();
		}
		$this->setRedirect("index.php?option=com_bids&task=reported.listing",JText::_("COM_BIDS_REPORTED_TOGGLED"));

    }
    
}
