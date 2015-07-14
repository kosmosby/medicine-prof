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

class FieldType_selectmultiple extends FactoryFieldTypes
{
    var $type_name       = "Multiple Value Select List";
    var $class_name    = "selectmultiple";
    var $has_options   = 1;
    var $multiple	  = 1;
    var $sql_type       = "varchar";
    var $length       = 250;
    var $store_as_id    = false;
    var $_params=array(
        "size"=>"numeric",
        "width"=>"numeric"
    );

	function getValue($field,$source_array){
      $val=  parent::getValue($field,$source_array);
      if (!is_array($val)) $val=array($val);
      return implode(",",$val);
   }
    function getSQLFilter( $field, $filter,$tableAlias ){
        $cfg= CustomFieldsFactory::getConfig();
        $db =  JFactory::getDBO();

        if($tableAlias)
            $table_alias=$tableAlias.".";
        else
            $table_alias=isset($cfg['aliases'][$field->own_table])?($cfg['aliases'][$field->own_table]."."):"";


        $sql=" ".$table_alias.$field->db_name." LIKE ".$db->quote('%'.$filter.'%');

        return $sql;
    }

    function getFieldHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $size=$field->getParam('size',5);
        $width=$field->getParam('width',100);

        if (is_array($fieldvalue))
            $value_arr=$fieldvalue;
        else
            $value_arr=explode(",", $fieldvalue);

        $style_attributes="style='width:{$width}px;{$field->style_attr}'";
        $options=$field->getOptions();

        $opt_array=array();
        if(count($options))
            foreach($options as $k => $option)
                $opt_array[]=JHTML::_('select.option', $option->option_name , JText::_($option->option_name) );
        return JHTML::_('select.genericlist',  $opt_array, $field->db_name.'[]', "multiple size='$size' class='$css_class' $style_attributes id='$fieldid'", 'value', 'text', $value_arr );
    }
    function getSearchHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $size=$field->getParam('size',5);
        $width=$field->getParam('width',100);

        $style_attributes="style='width:{$width}px;{$field->style_attr}'";
        $options=$field->getOptions();

        $opt_array=array();
        if(count($options))
            foreach($options as $k => $option)
                $opt_array[]=JHTML::_('select.option', $option->option_name , JText::_($option->option_name) );
        return JHTML::_('select.genericlist',  $opt_array, $field->page.'%'.$field->db_name, "size='$size' class='$css_class' $style_attributes id='$fieldid'", 'value', 'text', $fieldvalue );

    }
    function htmlSearchLabel($field, $searchValue) {

        $searchValue = (array) $searchValue;

        return implode(',',$searchValue);
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
