<?php

defined('_JEXEC') or die('Restricted access');

class JBidsAdminControllerShipping extends JController
{
    var $table='';
    function __construct()
    {
        parent::__construct();
        $this->table='`#__'.APP_PREFIX.'_shipment_zones`';

        JLoader::register('JBidsAdminView', JPATH_COMPONENT_ADMINISTRATOR . DS . 'views' . DS . 'view.php');
    }
    function execute($task)
    {
        if (file_exists(JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.shipping.php'))
            require JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.shipping.php';

        parent::execute($task);
    }
    function Listing()
    {

		$db		=  JFactory::getDBO();
		$app	= JFactory::getApplication();

		$context			= 'com_bids.shippingzones';
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');

		$searchfilter	= $app->getUserStateFromRequest($context.'searchfilter', 'searchfilter', '', 'string');

		$where = array();
		if($searchfilter!=""){
			$where[]= " name LIKE '%".$db->escape($searchfilter)."%' ";
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

        $view=$this->getView('shipping','html');
        $view->assignRef('zones',$rows);
        $view->assignRef('pagination',$pagination);

        $view->assign('search',$searchfilter);
        $view->display();

    }
    function Add()
    {
		$shipping_zone =  JTable::getInstance('bidshipzone');

        $view=$this->getView('shipping','html');
        $view->assignRef('zone',$shipping_zone);
        $view->display('edit');
        
    }   
    function Edit()
    {
		$id = JRequest::getVar("cid");
        if (is_array($id))$id=$id[0];
        
		$shipping_zone =  JTable::getInstance('bidshipzone');
		$shipping_zone->load($id);

        $view=$this->getView('shipping','html');
        $view->assignRef('zone',$shipping_zone);
        $view->display('edit');
        
    } 
    function Save()
    {
		$shipping_zone =  JTable::getInstance('bidshipzone');
		$shipping_zone->bind($_POST);
        if (!$shipping_zone->name){
            JError::raiseWarning(400,JText::_("COM_BIDS_SHIPPING_ZONE_SAVE_ERROR"));
  		    $this->setRedirect("index.php?option=com_bids&task=shipping.listing&tmpl=component");
            return;
        }
		$shipping_zone->store();
		$this->setRedirect("index.php?option=com_bids&task=shipping.listing&tmpl=component",JText::_("COM_BIDS_SHIPPING_ZONE_SAVED"));
        
    }
    function Delete()
    {
		$id = JRequest::getVar("cid");
        if (is_array($id))$id=$id[0];
        
		$shipping_zone =  JTable::getInstance('bidshipzone');
		$shipping_zone->delete($id);
		$this->setRedirect("index.php?option=com_bids&task=shipping.listing&tmpl=component",JText::_("COM_BIDS_SHIPPING_ZONE_DELETED"));
    }
    
}
