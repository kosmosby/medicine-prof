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

class FieldType_checkbox extends FactoryFieldTypes
{
    var $type_name       = "CheckBox";
    var $class_name    = "checkBox";
    var $has_options   = 1;
    var $multiple	  = 1;
    var $sql_type       = "varchar";
    var $length       = 250;
    var $store_as_id    = false;

    function getSearchHTML($field,$fieldvalue=null)
    {
        $f=clone $field;
        $f->db_name=$f->page.'%'.$f->db_name;
       
        return $this->getFieldHTML($f,$fieldvalue);        
    }
    function getFieldHTML($field,$fieldvalue=null)
    {
        /* @var $field JTheFactoryFieldsTable */
        if ($fieldvalue)
            $value_arr=explode(',',$fieldvalue);
        else
            $value_arr=array();
        $css_class = $this->getCSSClass($field);
        $style_attributes=($field->style_attr)?"style='{$field->style_attr}'":"";
        $fieldid=$field->getHTMLId();

        $checkboxes=array();
        $options = $field->getOptions();
        
        if(count($options))
            foreach($options as $k => $option)
            {
                $selected='';
                if (in_array($option->option_name,$value_arr))
                    $selected="checked='checked'";
                $checkboxes[]="<input type='checkbox' class='{$css_class}' $style_attributes name='{$field->db_name}[]' id='{$fieldid}' $selected value='".trim($option->option_name)."' > ".
                    trim(JText::_($option->option_name) );
             }
   
        return implode("\r\n",$checkboxes);        
    }
	function getValue($field,$source_array){
      $val=  parent::getValue($field,$source_array);
      if (!is_array($val)) $val=array($val);
      return implode(",",$val);
   }
    function htmlSearchLabel($field, $searchValue) {
        $searchValue = (array) $searchValue;
        return implode(',',$searchValue);
    }

    function getSQLFilter( $field, $filter,$tableAlias ){
        $cfg= CustomFieldsFactory::getConfig();
        $db =  JFactory::getDBO();
        $filter=(array)$filter;
        
        if($tableAlias)
            $table_alias=$tableAlias.".";
        else
            $table_alias=isset($cfg['aliases'][$field->own_table])?($cfg['aliases'][$field->own_table]."."):"";
        $sql=array();
        foreach($filter as $v)
            $sql[]="(".$table_alias.$field->db_name." = ".$db->quote($v).")";
        $ret=implode(' OR ',$sql);
        return ($ret)?"($ret)":$ret;
    }

    function getTemplateHTML($field,$fieldvalue){

        $translations = array();
        $values = explode(',',$fieldvalue);
        foreach($values as $v) {
            $translations[] = JText::_($v);
        }

        return implode(',',$translations);
    }
}


?>
