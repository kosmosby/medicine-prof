<?php

defined('_JEXEC') or die('Restricted access');

class JBidsAdminControllerMessages extends JController
{
    var $table='';
    function __construct()
    {
        parent::__construct();
        $this->table='`#__'.APP_PREFIX.'_messages`';

        JLoader::register('JBidsAdminView', JPATH_COMPONENT_ADMINISTRATOR . DS . 'views' . DS . 'view.php');
    }
    function execute($task)
    {
        if (file_exists(JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.messages.php'))
            require JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.messages.php';

        parent::execute($task);
    }
    function Listing()
    {

		$db		=  JFactory::getDBO();
		$app	= JFactory::getApplication();

		$context			= 'com_bids.messages';
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');

		$activefilter	= $app->getUserStateFromRequest($context.'activefilter', 'activefilter', null, 'int');
		$searchfilter	= $app->getUserStateFromRequest($context.'searchfilter', 'searchfilter', '', 'string');


		$options = array();
		$options[] = JHTML::_('select.option',"",JText::_("COM_BIDS_ALL"));
		$options[] = JHTML::_('select.option',"1",JText::_("COM_BIDS_PUBLISHED"));
		$options[] = JHTML::_('select.option',"2",JText::_("COM_BIDS_UNPUBLISHED"));
		$active_html = JHTML::_('select.genericlist', $options, "activefilter",'class="inputbox" onchange="javascript:document.adminForm.submit();" ','value', 'text', $activefilter);

		$where = array();
		if($activefilter){
			 $where[]=(($activefilter=='1')?"":"NOT"). " (published ='1' and close_offer='0' and close_by_admin='0')";
		}
		if($searchfilter!=""){
			$where[]= " m.message LIKE '%{$searchfilter}%' ";
		}

		$whereSQL = "";
		if(count($where)>0){
			$whereSQL = "WHERE ".implode("AND",$where);
		}

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ( $limit != 0 ? (floor($limitstart / $limit) * $limit) : 0 );

		$db->setQuery("SELECT COUNT(*) FROM {$this->table} m
            left join #__bid_auctions a on m.auction_id=a.id
             $whereSQL ");
		$total = $db->loadResult();

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$db->setQuery("SELECT m.*,a.title,u1.username as username1,u2.username as username2 FROM {$this->table} m
            left join #__bid_auctions a on m.auction_id=a.id
            left join #__users u1 on m.userid1=u1.id 
            left join #__users u2 on m.userid2=u2.id 
         $whereSQL ", $pagination->limitstart, $pagination->limit);
		$rows = $db->loadObjectList();

        $view = $this->getView('messages','html');
        $view->assignRef('messages',$rows);
        $view->assignRef('pagination',$pagination);

        $view->assignRef('active_filter',$active_html);
        $view->assign('search',$searchfilter);

        $view->display();

    }
    function publish() {
        $this->toggle(1);
    }

    function unpublish() {
        $this->toggle(0);
    }
    function Toggle($published=null)
    {
        if(!isset($published)) {
            $published = '1-published';
        }
		$cid	= JRequest::getVar( 'cid', array(), '', 'array' );
		if(count($cid>0)){
			$cids = implode(",",$cid);
    		$db		=  JFactory::getDBO();
			$db->setQuery("UPDATE {$this->table} SET published = {$published} WHERE id IN ($cids)");
			$db->query();
		}
		$this->setRedirect($_SERVER['HTTP_REFERER'],JText::_("COM_BIDS_MESSAGES_PUBLISHING_TOGGLED"));
    }
    function Delete()
    {
		$cid	= JRequest::getVar( 'cid', array(), '', 'array' );
		if(count($cid>0)){
			$cids = implode(",",$cid);
    		$db	=  JFactory::getDBO();
			$db->setQuery("DELETE from {$this->table} WHERE id IN ($cids)");
			$db->query();
		}
		$this->setRedirect($_SERVER['HTTP_REFERER'],JText::_("COM_BIDS_MESSAGES_DELETED"));
    }
}
