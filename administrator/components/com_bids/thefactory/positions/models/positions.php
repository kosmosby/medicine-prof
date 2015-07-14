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
 * @subpackage: positions
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class JTheFactoryModelPositions extends JModel
{
    var $context='positions';
    var $_tablename=null;
    var $_tablename_fields=null;
    function __construct()
    {
        $this->context=APP_EXTENSION."_positions.";
        $this->_tablename='#__'.APP_PREFIX.'_fields_positions';
        $this->_tablename_fields='#__'.APP_PREFIX.'_fields';

        parent::__construct();
    }
    function getFieldsForPage($page)
    {
        $db= $this->getDbo();
        $db->setQuery("select f.* from `{$this->_tablename_fields}` f
            left join `{$this->_tablename}` p on p.fieldid=f.id
            where p.templatepage='$page' and f.`status`=1
            order by p.`ordering`
        ");
        return $db->loadObjectList();

    }
    function getFieldsForPosition($pagename,$positionname)
    {
        $db= $this->getDbo();
        $db->setQuery("select f.* from `{$this->_tablename_fields}` f
            left join `{$this->_tablename}` p on p.fieldid=f.id
            where p.templatepage='{$pagename}'
            and p.position='{$positionname}' and f.`status`=1
            order by p.`ordering`
        ");
        return $db->loadObjectList();
    }
    function addFieldsToPosition($pagename,$positionname,$fields)
    {
        if (!count($fields)) return;
        $db= $this->getDbo();
        $i=1;
        foreach($fields as $field){
            $db->setQuery("insert into `{$this->_tablename}`
                  set fieldid='{$field}',
                  templatepage='{$pagename}',
                  position='{$positionname}',
                  ordering='$i'
            ");
            $db->query();
            $i++;
        }
    }
    function deleteFieldsFromPosition($pagename,$positionname,$fields=null)
    {
        $w="";
        if ($fields){
            if (is_array($fields)){
                $ids=array();
                foreach($fields as $field){
                    if (is_object($field))
                        $ids[]=$field->id;
                    else
                        $ids[]=$field;
                }
                $w=" and fieldid in (".implode(",",$ids).")";
            }else{
                $w=" and fieldid='$fields'";
            }

        }
        $db= $this->getDbo();
        $db->setQuery("delete from `{$this->_tablename}` where
              templatepage='{$pagename}' and position='{$positionname}'
              {$w}
        ");
        $db->query();
    }
    function getAllFields($fieldpage=null)
    {
        $w=" where f.`status`=1 ";
        if($fieldpage){
            $w.= " and f.page='$fieldpage' ";
        }
        
        $db= $this->getDbo();
        $db->setQuery("select f.* from `{$this->_tablename_fields}` f
            $w
            order by f.`ordering`
        ");
        return $db->loadObjectList();
    }

}
