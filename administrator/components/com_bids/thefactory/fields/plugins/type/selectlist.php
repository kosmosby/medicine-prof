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

class FieldType_selectList extends FactoryFieldTypes
{
	var $type_name 		= "Single Value Select List";
	var $class_name 	= "selectList";
	var $has_options	= 1;
	var $multiple		= 0;
	var $sql_type 		= "varchar";
	var $length 		= 200;
    var $store_as_id    = false;
    var $_params=array(
        "size"=>"numeric",
        "width"=>"numeric"
    );
    function getFieldHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $size=$field->getParam('size',1);
        $width=$field->getParam('width',100);

        $style_attributes="style='width:$width;{$field->style_attr}'";
        $options=$field->getOptions();

        $opt_array=array();
        if (!$field->compulsory)
            $opt_array[]=JHTML::_('select.option', '', JText::_(''));
        if(count($options))
            foreach($options as $k => $option)
                $opt_array[]=JHTML::_('select.option', $option->option_name , JText::_($option->option_name) );
        return JHTML::_('select.genericlist',  $opt_array, $field->db_name, "size='$size' class='$css_class' $style_attributes id='$fieldid'", 'value', 'text', $fieldvalue );
    }
    function getSearchHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $size=$field->getParam('size',1);
        $width=$field->getParam('width',100);

        $style_attributes="style='width:$width;{$field->style_attr}'";
        $options=$field->getOptions();

        $opt_array=array();
        $opt_array[]=JHTML::_('select.option', '', JText::_('FACTORY_ANY'));
        if(count($options))
            foreach($options as $k => $option)
                $opt_array[]=JHTML::_('select.option', $option->option_name , JText::_($option->option_name) );
        return JHTML::_('select.genericlist',  $opt_array, $field->page.'%'.$field->db_name, "size='$size' class='$css_class' $style_attributes id='$fieldid'", 'value', 'text', $fieldvalue );
    }

    function getTemplateHTML($field,$fieldvalue){
        return JText::_($fieldvalue);
    }
}


?>
