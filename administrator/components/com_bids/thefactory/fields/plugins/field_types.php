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

class FactoryFieldTypes extends JObject
{

	var $type_name 		= null;
	var $class_name 	= null;
	var $has_options	= null;
	var $multiple		= null;
	var $sql_type 		= null;
	var $length 		= null;
    var $store_as_id    = false;
    
	var $_params	 	= null; //parameter definitions

	function getCSSClass($field)
    {
		$css_classes = array();
		if($field->css_class)
			$css_classes[] = $field->css_class;
		if($field->compulsory==1)
			$css_classes[] = "required";
		if($field->validate_type)
            $css_classes[] = "validate-".strtolower($field->validate_type);
		return implode(" ", $css_classes);
	}

	function getValue($field,$source_array){
		return JArrayHelper::getValue($source_array,$field->db_name);
	}
    function getSearchHTML($field,$fieldvalue=null)
    {
        //abstract
        return $field->db_name;
    }
    function getFieldHTML($field,$fieldvalue=null)
    {
        //abstract
        return $field->db_name;
    }
	function getTemplateHTML($field,$fieldvalue){
		return $fieldvalue;
	}
	function getSQLFilter( $field, $filter,$tableAlias=null ){
        $cfg= CustomFieldsFactory::getConfig();
		$db =  JFactory::getDBO();
        if($tableAlias)
            $table_alias=$tableAlias.".";
        else
            $table_alias=isset($cfg['aliases'][$field->own_table])?($cfg['aliases'][$field->own_table]."."):"";


        $sql=" ".$table_alias.$field->db_name."=".$db->quote($filter);

        return $sql;
	}
    function htmlSearchLabel($field, $searchValue) {
        return $searchValue;
    }
}
