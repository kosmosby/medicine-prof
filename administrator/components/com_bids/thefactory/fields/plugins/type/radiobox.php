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

class FieldType_radioBox extends FactoryFieldTypes
{
	var $type_name 		= "Radio Button Options";
	var $class_name 	= "radioBox";
	var $has_options	= 1;
	var $multiple		= 0;
	var $sql_type 		= "varchar";
	var $length 		= 250;
    var $store_as_id    = false;

    function getFieldHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $style_attributes=($field->style_attr)?"style='{$field->style_attr}'":"";

        $options = $field->getOptions();

        $opt_array=array();
        if(count($options))
            foreach($options as $k => $option)
                $opt_array[] = JHTML::_('select.option', $option->option_name , JText::_($option->option_name) );
        return JHTML::_('select.radiolist',  $opt_array, $field->db_name, "class='$css_class' $style_attributes id='$fieldid'", 'value', 'text', $fieldvalue );
    }
    function getSearchHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $style_attributes=($field->style_attr)?"style='{$field->style_attr}'":"";

        $options = $field->getOptions();

        $opt_array=array();
        $opt_array[]=JHTML::_('select.option', '', JText::_('FACTORY_ANY'));
        if(count($options))
            foreach($options as $k => $option)
                $opt_array[] = JHTML::_('select.option', $option->option_name , JText::_($option->option_name) );
        return JHTML::_('select.radiolist',  $opt_array, $field->page.'%'.$field->db_name, "class='$css_class' $style_attributes id='$fieldid'", 'value', 'text', $fieldvalue );
    }
    function getSQLFilter( $field, $filter,$tableAlias=null ){
        $cfg= CustomFieldsFactory::getConfig();
        $db =  JFactory::getDBO();

        if($tableAlias)
            $table_alias=$tableAlias.".";
        else
            $table_alias=isset($cfg['aliases'][$field->own_table])?($cfg['aliases'][$field->own_table]."."):"";



        $sql=" ".$table_alias.$field->db_name." LIKE ".$db->quote('%'.$filter.'%');

        return $sql;
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
