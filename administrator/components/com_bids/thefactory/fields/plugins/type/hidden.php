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

class FieldType_hidden extends FactoryFieldTypes
{
	var $type_name 		= "Hidden field";
	var $class_name 	= "hidden";
	var $has_options	= 0;
	var $multiple		= 0;
	var $sql_type 		= "varchar";
	var $length 		= 250;
    var $store_as_id    = false;
    var $_params=array(
        "default_value"=>"text"
    );

    function getFieldHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
		return "<input type='hidden' name='{$field->db_name}' id='{$fieldid}' value={$fieldvalue} />". $fieldvalue ;
    }
    function getSearchHTML($field,$fieldvalue=null)
    {
        $fieldid=($field->field_id)?($field->field_id):($field->db_name);
        $css_class = $this->getCSSClass($field);
        $input_name=$field->page.'%'.$field->db_name;
        return "<input type='text' name='{$input_name}' id='{$fieldid}' class='{$css_class}' value='".trim($fieldvalue)."'>";
    }
}



?>
