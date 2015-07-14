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

class FieldType_inputBox extends FactoryFieldTypes
{

	var $type_name 		= "Text Input Field";
	var $class_name 	= "inputBox";
	var $has_options	= 0;
	var $multiple		= 0;
	var $sql_type 		= "varchar";
	var $length 		= 250;
    var $store_as_id    = false;
    var $_params=array(
        "size"=>"numeric"
    );

    function getFieldHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $style_attributes=($field->style_attr)?"style='{$field->style_attr}'":"";
        $size=$field->getParam('size','30');
        return "<input type='text' name='{$field->db_name}' id='{$fieldid}' size='$size' $style_attributes class='{$css_class}' value='".trim($fieldvalue)."'>";
    }    
    function getSearchHTML($field,$fieldvalue=null)
    {
        $f=clone $field;
        $f->db_name=$f->page.'%'.$f->db_name;
        return $this->getFieldHTML($f,$fieldvalue);
    }

}

?>
