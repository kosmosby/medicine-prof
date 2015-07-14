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

jimport('joomla.html.parameter');
class JTheFactoryFieldsTable extends JTable {

	var $id					= null;
	var $name				= null;
	var $db_name			= null;
	var $page				= null;
	var $ftype				= null;
	var $compulsory			= null;
    var $categoryfilter     = null;
	var $status		        = null;
	var $own_table			= null;
	var $validate_type		= null;
	var $css_class			= null;
	var $style_attr			= null;
	var $params				= null;
    var $search				= null;
	var $ordering			= null;
	var $help				= null;

	function __construct( &$db ) {
		parent::__construct( '#__'.APP_PREFIX.'_fields', 'id', $db );
	}
	/**
	 *
	 * Store Custom Field
	 *
	 **/
	function store($forceCreateField=false)
    {
        $db= $this->getDbo();

		$field_type_obj = CustomFieldsFactory::getFieldType($this->ftype);
		$existing_fields = $db->getTableFields($this->own_table);

    	$must_create=$forceCreateField ||
    	    (!isset($existing_fields[$this->own_table][$this->db_name]));

        if (is_array($this->params))
            $this->params=join("\n",$this->params);

        $res=parent::store();

        if($res && $must_create)
        {
            if($sqlfield_len=$field_type_obj->length)
                $sqlfield_len="($sqlfield_len)";
            $sqlfield_type=$field_type_obj->sql_type;
            $db->setQuery("ALTER TABLE {$this->own_table} ADD {$this->db_name} {$sqlfield_type} {$sqlfield_len};");
            $res=$db->query();

        }
        return $res;
	}

	function delete($oid=null){

		$this->load($oid);

		// Core Test: Some fields can not be deletable!
        $db= $this->getDbo();
	// DELETE actual field from table
        
        if( !in_array($this->db_name , array("title","start_date","end_date","id","userid","user_id")) ){
            $db->setQuery("ALTER TABLE $this->own_table DROP `$this->db_name` ;");
            $db->query();
        }

	// DELETE all it's options
		$db->setQuery("DELETE FROM #__".APP_PREFIX."_fields_options WHERE fid = '{$this->id}'");
		$db->query();

	// DELETE all it's assignings to categories
		$db->setQuery("DELETE FROM #__".APP_PREFIX."_fields_categories WHERE fid = '{$this->id}'");
		$db->query();

     // DELETE all it's assignings to positions
		$db->setQuery("DELETE FROM #__".APP_PREFIX."_fields_positions WHERE fieldid = '{$this->id}'");
    	$db->query();

		parent::delete($oid);

	}

	/**
	 *
	 * Custom Field Options methods
	 *
	 **/
	function store_option($option)
    {
        $db= $this->getDbo();
		$db->setQuery("SELECT MAX(`ordering`) from #__".APP_PREFIX."_fields_options where fid = '{$this->id}'");
        $ordering=(int)$db->loadResult() + 1;
        
		$option = $db->getEscaped($option);
		$db->setQuery("INSERT INTO #__".APP_PREFIX."_fields_options SET `fid` = '{$this->id}', `option_name` ='{$option}', `ordering`={$ordering}");
		$db->query();
        
	}

	function del_option($id_opt)
	{
        $db= $this->getDbo();
		$db->setQuery("DELETE FROM #__".APP_PREFIX."_fields_options WHERE id =".
		    $db->quote($id_opt)." AND fid=".$db->quote($this->id)
		);
		return $db->query();
	}

	function update_option($id_opt, $value)
	{
        $db= $this->getDbo();
		$db->setQuery("UPDATE #__".APP_PREFIX."_fields_options SET option_name =".$db->quote($value)
		    ." WHERE id =".$db->quote($id_opt));
		return $db->query();
	}

	function getOptions($fieldid=null)
    {
        $db= $this->getDbo();
        if(!$fieldid) $fieldid=$this->id;
        if (!$fieldid) return array();
		$db->setQuery("SELECT * FROM #__".APP_PREFIX."_fields_options WHERE fid = '{$fieldid}' order by `ordering`");
		return $db->loadObjectList();
	}
    function getParam($paramname,$defaultval=null)
    {
        $paramobj=new JParameter($this->params);
        return $paramobj->get($paramname,$defaultval);
    }
	function getValues($section, $owner_id){
        $cfg= CustomFieldsFactory::getConfig();
        $db= $this->getDbo();
		$db->setQuery("SELECT * FROM ".$cfg['tables'][$section]." WHERE ".$cfg['pk'][$section]." = '$owner_id' ");
		$ret = $db->loadObjectList();
		return (count($ret))?$ret[0]:null;
	}

    function setDefaults()
    {
        $this->search=0;
        $this->compulsory=0;
        $this->categoryfilter=0;
        $this->status=1;
        
        $this->validate_type=null;
        $types=CustomFieldsFactory::getFieldTypesList();
        $this->ftype=$types[0];
        $pages=CustomFieldsFactory::getPagesList();
        $this->page=$pages[0];

    }
    function getHTMLId()
    {
        if ($this->page)
            $fieldid=$this->page."_".$this->db_name;
        elseif($this->db_name)
            $fieldid="cfield_".$this->db_name;
        else
            return null;
        
        $fieldid=strtolower($fieldid);
        $fieldid=str_replace(
            array(" ","'",'"',"(",")",".","!","?"),
            "_",$fieldid
        );
        return $fieldid;
    }
    function check()
    {
        $errors=array();
        if(!$this->db_name)
            $errors[]=JText::_("FACTORY_FIELD_DB_NAME_MUST_BE_PROVIDED");
        if(!$this->name)
            $errors[]=JText::_("FACTORY_FIELD_NAME_MUST_BE_PROVIDED");
        if (!$this->id && $this->db_name){
            $db= $this->getDbo();
    		$db->setQuery("SELECT count(*) FROM `".$this->getTableName()."` WHERE own_table='{$this->own_table}' and db_name='$this->db_name' ");
    		if ($db->loadResult())
                $errors[]=JText::_("FACTORY_FIELD_NAME_MUST_BE_UNIQUE_FOR_SECTION");
        }
        return $errors;
    }
}

?>
