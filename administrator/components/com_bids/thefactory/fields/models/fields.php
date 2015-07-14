<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: custom_fields
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model'); 

class JTheFactoryModelFields extends JModelLegacy
{
    var $context='fields';
    var $limit=null;
    var $limitstart=null;
    var $ordering=null;
    var $ordering_dir='ASC';
    var $_total=null;
    var $_pagination=null;
    var $_tablename=null;
    var $_tablename_category=null;
    function __construct()
    {
        $this->context=APP_EXTENSION."_fields.";
        $this->_tablename='#__'.APP_PREFIX.'_fields';
        $this->_tablename_category='#__'.APP_PREFIX.'_fields_categories';
        
        parent::__construct();
    }    
    function getLimitFromRequest()
    {
		$app	= JFactory::getApplication();
		$this->limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$this->limitstart	= $app->getUserStateFromRequest($this->context.'limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$this->limitstart = ( $this->limit != 0 ? (floor($this->limitstart / $this->limit) * $this->limit) : 0 );

        
    }
    function getOrdering()
    {
		$app	= JFactory::getApplication();
		$this->ordering	= $app->getUserStateFromRequest($this->context.'ordering', 'ordering', 'ordering', 'string');
		$this->ordering_dir	= $app->getUserStateFromRequest($this->context.'ordering_dir', 'ordering_dir', 'ASC', 'string');
    }

    function getFields($enabled_filter=false,$filter_page=null)
    {
        $db= $this->getDbo();
        $this->getOrdering();
        $this->getLimitFromRequest();
        $where='where 1=1';
        if($filter_page) $where.=" and page='{$filter_page}'";
        if($enabled_filter) $where.=" and `status`='1'";

        $db->setQuery("SELECT COUNT(*) FROM `".$this->_tablename."` {$where}");
        $this->_total=$db->loadResult();

		$db->setQuery("SELECT * FROM `".$this->_tablename."` {$where} ORDER BY page ASC, {$this->ordering} {$this->ordering_dir}",
            $this->limitstart,$this->limit);
		return $db->loadObjectList();
    }
    function getPagination()
    {
        if($this->_pagination) return $this->_pagination;
		jimport('joomla.html.pagination');
		$this->_pagination = new JPagination($this->_total, $this->limitstart, $this->limit);
        return $this->_pagination;
    }
    function getAssignedCats(&$field)
    {
        $db= $this->getDbo();
		$db->setQuery("SELECT cid FROM `".$this->_tablename_category."` where `fid`='{$field->id}'");
		return $db->loadResultArray();
    }
    function setAssignedCats(&$field,$catids)
    {
        $db= $this->getDbo();
        // delete category assignments
        $sql = "DELETE FROM #__".APP_PREFIX."_fields_categories WHERE fid = '{$field->id}'";
        $db->setQuery($sql);
        $db->query();

        if (!count($catids)) retun; //no categories assigned
        // add category assignemtns
        $inserts = array();
        foreach ($catids as $catid){
            $inserts[] = " ('{$field->id}', '{$catid}')";
        }
        if (count($inserts))
        {
            $sql = "INSERT INTO #__".APP_PREFIX."_fields_categories (`fid`,`cid`) VALUES ".implode(",",$inserts);
            $db->setQuery($sql);
            $db->query();
        }
    }
    function hasAssignedCat(&$field,$catid)
    {
        $db= $this->getDbo();
		$db->setQuery("SELECT count(*) FROM `".$this->_tablename_category."` where `fid`='{$field->id}' and `cid`='$catid'");
		return $db->loadResult()>0;
    }
    
}

?>
