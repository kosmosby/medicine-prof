<?php

defined('_JEXEC') or die('Restricted access');

class JBidsAdminControllerIncrements extends JController
{
    var $table='';
    function __construct()
    {
        parent::__construct();
        $this->table='`#__'.APP_PREFIX.'_increment`';

        JLoader::register('JBidsAdminView', JPATH_COMPONENT_ADMINISTRATOR . DS . 'views' . DS . 'view.php');
    }
    function execute($task)
    {
        if (file_exists(JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.increments.php'))
            require JPATH_COMPONENT_ADMINISTRATOR.DS.'toolbar.increments.php';

        parent::execute($task);
    }
    function Listing()
    {

		$db		=  JFactory::getDBO();
		$app	= JFactory::getApplication();

		$context			= 'com_bids.increments';
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');

		$where = array();

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

		$db->setQuery("SELECT * FROM {$this->table} $whereSQL order by min_bid", $pagination->limitstart, $pagination->limit);
		$rows = $db->loadObjectList();

        $view=$this->getView('increments','html');
        $view->assignRef('increments',$rows);
        $view->assignRef('pagination',$pagination);
        $view->display();

    }
    function Add()
    {
		$increment =  JTable::getInstance('bidincrement');

        $view=$this->getView('increments','html');
        $view->assignRef('increment',$increment);
        $view->display('edit');
        
    }   
    function Edit()
    {
		$id = JRequest::getVar("cid");
        if (is_array($id))$id=$id[0];
        
		$increment =  JTable::getInstance('bidincrement');
		$increment->load($id);

        $view=$this->getView('increments','html');
        $view->assignRef('increment',$increment);
        $view->display('edit');
        
    } 
    function Save()
    {
		$increment =  JTable::getInstance('bidincrement');
		$increment->bind($_POST);
        if ($increment->value<0 || $increment->min_bid>$increment->max_bid){
            JError::raiseWarning(400,JText::_("COM_BIDS_INCREMENT_SAVE_ERROR"));
  		    $this->setRedirect("index.php?option=com_bids&task=increments.listing&tmpl=component");
            return;
        }
		$increment->store();
		$this->setRedirect("index.php?option=com_bids&task=increments.listing&tmpl=component",JText::_("COM_BIDS_INCREMENT_SAVED"));
        
    }
    function Delete()
    {
		$id = JRequest::getVar("cid");
        if (is_array($id))$id=$id[0];
        
		$increment =  JTable::getInstance('bidincrement');
		$increment->delete($id);
		$this->setRedirect("index.php?option=com_bids&task=increments.listing&tmpl=component",JText::_("COM_BIDS_INCREMENT_DELETED"));
    }
    
}
