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

class FieldType_textArea extends FactoryFieldTypes
{
	var $type_name 		= "Simple Text Area";
	var $class_name 	= "textArea";
	var $has_options	= 0;
	var $multiple		= 0;
	var $sql_type 		= "text";
	var $length 		= null;
    var $store_as_id    = false;
    var $_params=array(
        "cols"=>"numeric",
        "rows"=>"numeric"
    );

    function getFieldHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $cols=$field->getParam('cols',60);
        $rows=$field->getParam('rows',15);
        $style_attributes=($field->style_attr)?"style='{$field->style_attr}'":"";

        $fieldvalue=str_replace("<br />", PHP_EOL, $fieldvalue);
        $fieldvalue=str_replace("<br/>", PHP_EOL, $fieldvalue);
        $fieldvalue=str_replace("<br>", PHP_EOL, $fieldvalue);
        
		return "<textarea name='{$field->db_name}' class='{$css_class}' rows='$rows' cols='$cols' id='{$fieldid}' $style_attributes >{$fieldvalue}</textarea>";

    }
    function getSearchHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $input_name=$field->page.'%'.$field->db_name;
		return "<input name='{$input_name}' class='{$css_class}' id='{$fieldid}' type='text' value='{$fieldvalue}' />";
    }

}


?>
