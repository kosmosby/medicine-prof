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

class FieldType_editor extends FactoryFieldTypes
{
	var $type_name 		= "Editor";
	var $class_name 	= "editor";
	var $has_options	= 0;
	var $multiple		= 0;
	var $sql_type 		= "text";
	var $length 		= null;
    var $store_as_id    = false;
    var $_params=array(
        "width"=>"text",
        "height"=>"text",
        "cols"=>"numeric",
        "rows"=>"numeric"
    );

    function getFieldHTML($field,$fieldvalue=null)
    {
        /* @var $editor JEditor*/
		$editor = JFactory::getEditor();
        $width=$field->getParam('width','100%');
        $height=$field->getParam('height','60');
        $cols=$field->getParam('cols','80');
        $rows=$field->getParam('rows','60');
        return $editor->display($field->db_name, $fieldvalue, $width,$height,$cols,$rows);
    }
    function getSearchHTML($field,$fieldvalue=null)
    {
        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $input_name=$field->page.'%'.$field->db_name;
        
        return "<input type='text' name='{$input_name}' id='{$fieldid}' class='{$css_class}' value='".trim($fieldvalue)."'>";
    }
}


?>
