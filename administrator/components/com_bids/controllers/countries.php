<?php

defined('_JEXEC') or die('Restricted access');

class JBidsAdminControllerCountries extends JController
{
    var $table='';
    function __construct()
    {
        parent::__construct();
        $this->table='`#__'.APP_PREFIX.'_country`';

        JLoader::register('JBidsAdminView', JPATH_COMPONENT_ADMINISTRATOR . DS . 'views' . DS . 'view.php');
    }
    function execute($task)
    {
        if (file_exists(JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.countries.php'))
            require JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.countries.php';

        parent::execute($task);
    }
    function Listing()
    {
		$db		=  JFactory::getDBO();
		$app	= JFactory::getApplication();

		$context			= 'com_bids.countrylist';
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');

		$activefilter	= $app->getUserStateFromRequest($context.'activefilter', 'activefilter', 2, 'int');
		$searchfilter	= $app->getUserStateFromRequest($context.'searchfilter', 'searchfilter', '', 'string');


		$options = array();
		$options[] = JHTML::_('select.option',"2",JText::_("COM_BIDS_ALL"));
		$options[] = JHTML::_('select.option',"1",JText::_("COM_BIDS_PUBLISHED"));
		$options[] = JHTML::_('select.option',"0",JText::_("COM_BIDS_UNPUBLISHED"));
		$active_html = JHTML::_('select.genericlist', $options, "activefilter",'class="inputbox" onchange="javascript:document.adminForm.submit();" ','value', 'text', $activefilter);

		$where = array();
		if($activefilter!="2"){
			$where[]= " active ='{$activefilter}' ";
		}
		if($searchfilter!=""){
			$where[]= " name LIKE '%{$searchfilter}%' ";
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

		$db->setQuery("SELECT * FROM {$this->table} $whereSQL ", $pagination->limitstart, $pagination->limit);
		$rows = $db->loadObjectList();

        $view=$this->getView('country','html');
        $view->assignRef('countries',$rows);
        $view->assignRef('pagination',$pagination);

        $view->assignRef('active_filter',$active_html);
        $view->assign('search',$searchfilter);
        $view->display();

    }
    function Toggle()
    {
		$cid	= JRequest::getVar( 'cid', array(), '', 'array' );
		if(count($cid>0)){
			$cids = implode(",",$cid);
    		$db		=  JFactory::getDBO();
			$db->setQuery("UPDATE {$this->table} SET active = 1- active WHERE id IN ($cids)");
			$db->query();
		}
		$this->setRedirect("index.php?option=com_bids&task=countries.listing",JText::_("COM_BIDS_COUNTRIES_PUBLISHING_TOGGLED"));

    }
    
}
