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

/**
 *
 * Table to extend for other tables that accept custom fields
 *  provides html display of fields for a parent item
 **/
class FactoryFieldsTbl extends JTable{

	var $_F_section = null;
    var $_category_field=null;
    var $_system_fields=null;
    var $_validation_errors=array();
    var $_custom_fields=null;

	/**
	 *  Regular loading of a row with custom fields
	 *
	 **/
    function __construct( $table, $key, &$db,$system_fields=null,$category_field=null )
	{
	   
	   $this->_category_field=$category_field;
	   $this->_system_fields=$system_fields;
       
	   parent::__construct($table,$key,$db);
       
       $database= $this->getDbo();
   	   $database->setQuery("SELECT * FROM #__".APP_PREFIX."_fields WHERE own_table = '{$table}' AND status = 1 ORDER BY ordering ");
       $this->_custom_fields=$database->loadObjectList();
       
       $fields=$this->_custom_fields; 
       for($i=0;$i<count($fields);$i++)
       {
            $fieldname=$fields[$i]->db_name;
            $this->$fieldname=null;
   	   }
       
	}
    function getCategoryField()
    {
        return $this->_category_field;
    }
    function getValidationErrors()
    {
        return $this->_validation_errors;

    }
    function check()
    {
        $result=true;
        $this->_validation_errors=array();
        
        if ($this->_category_field)
        {
            $db=$this->getDbo();
            $category=$this->{$this->_category_field};
            //Table has a category field, and we get all fields that are assigned to that category
			$db->setQuery("SELECT fid FROM #__".APP_PREFIX."_fields_categories WHERE cid = '{$category}'");
			$allowed_fields = $db->loadResultArray();
        }
        
        foreach($this->_custom_fields as $field)
        {
            if ($this->_category_field && $field->categoryfilter && !in_array($field->id,$allowed_fields))
            {
                continue;
            }
            $fieldvalue=$this->{$field->db_name};
            if ($field->compulsory && ($fieldvalue===NULL || $fieldvalue=='' ))
            {
                //attention 0 can be a non empty value !
                $this->_validation_errors[]=JText::_("FACTORY_FIELD")." ".$field->name." ".JText::_("FACTORY_IS_REQUIRED"); 
                
            }
            if (!$field->validate_type) 
                continue;
                
            $validator= CustomFieldsFactory::getFieldValidator($field->validate_type);
            
            if (!$validator->validateValue($fieldvalue,$field->params))
            {
                $result=false;
                $this->_validation_errors=array_merge($this->_validation_errors,$validator->$errormessages);
            }        
        }
          
        $result=$result && parent::check();
        return $result;
    }
   	function bind($src, $ignore = array())
    {
        $res=parent::bind($src,$ignore);
        if($src==$_GET || $src==$_POST || $src==$_REQUEST)
        {
    		if (!is_array($ignore)) {
    			$ignore = explode(' ', $ignore);
    		}
            $fieldObj= JTable::getInstance('FieldsTable','JTheFactory');
            
            foreach($this->_custom_fields as $field)
                if (!in_array($field->db_name,$ignore))
                {
                    $fieldObj->bind($field);
                    $fieldPlugin= CustomFieldsFactory::getFieldType($field->ftype);
                    $val=$fieldPlugin->getValue($fieldObj,$src);
                    $this->{$field->db_name}=$val;
                }
        }
        return $res;    
    }
	function store($quicksave=false){

        if (!$quicksave)
        {
            if ($this->_category_field)
            {
                $db= $this->getDbo();
                $category=$this->{$this->_category_field};
                //Table has a category field, and we get all fields that are assigned to that category
    			$db->setQuery("SELECT fid FROM #__".APP_PREFIX."_fields_categories WHERE cid = '{$category}'");
    			$allowed_fields = $db->loadResultArray();
            }
            
            foreach($this->_custom_fields as $field)
            {
                if ($this->_category_field && $field->categoryfilter && !in_array($field->id,$allowed_fields))
                {
                    //Field is not allowed in this category
                    $this->{$field->db_name}=null;
                    continue;
                }
            }
        }        
		$s = parent::store();

		return $s;
	}


}



?>
