<?php 
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: Bids
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.model' );

abstract class bidsModelGeneric extends JTheFactoryListModel
{
	var $_name='Generic';
	var $name='Generic';
    var $context='generic';
    var $knownFilters = array();
    var $order_fields = array();
    
    protected $pagination=null;
    protected $items=null;
    protected $total=null;

    function setFilters() {

        $app	= JFactory::getApplication();
        
        $reset = JRequest::getString('reset', '');
        $this->setState('filters.reset',$reset);
        $this->resetFilters('reset');

        foreach($this->knownFilters as $keyName=>$attribs) {
            $default = isset($attribs['default']) ? $attribs['default'] : null;
            $type = isset($attribs['type'])     ? $attribs['type'] : 'none';
            $value = $app->getUserStateFromRequest($this->context . '.filters.' . $keyName, $keyName, $default, $type );
            if(!empty($value)) {
                $this->setState('filters.'.$keyName, $value );
            }
        }

        $list_limit=BidsHelperTools::getItemsPerPage();

        // Get the pagination request variables
        $this->setState('limit', $app->getUserStateFromRequest($this->context.'.limit','limit', $list_limit, 'int'));
        $this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));
        // In case limit has been changed, adjust limitstart accordingly
        $this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) :0));

        $this->setState('filter_order_Dir', $app->getUserStateFromRequest($this->context.'.filter_order_Dir', 'filter_order_Dir', "ASC"));
        $this->setState('filter_order', $app->getUserStateFromRequest($this->context.'.filter_order','filter_order', "start_date"));

        //this sets the model's filters according to custom fields
        $profile = BidsHelperTools::getUserProfileObject();
        parent::setCustomFilters($profile);


    }
    function setPagination()
    {
    	
        // Lets load the content if it doesn't already exist
        if (empty($this->pagination)) {
            jimport('joomla.html.pagination');
			$this->pagination= new JPagination($this->getTotal(), $this->getState('limitstart'),$this->getState('limit'));
        }
        return $this->pagination;
    }
    abstract function getTotal();
    abstract function buildQuery();
    
    function loadItems()
    {
        $this->setFilters();

		$query = $this->buildQuery();
		$this->items = $this->_getList((string) $query, $this->getState('limitstart'), $this->getState('limit'));
        $this->setPagination();
        
        return $this->items;
    }
     
}
