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

class FieldType_image extends FactoryFieldTypes
{
	var $type_name 		= "Image";
	var $class_name 	= "image";
	var $has_options	= 0;
	var $multiple		= 0;
    var $sql_type       = "varchar";
    var $length         = 80;
    var $store_as_id    = false;
    var $_params=array(
        "file_extensions"=>"text",
        "upload_path"=>"text",
        "thumbnail_width"=>"numeric",
        "thumbnail_height"=>"numeric",
        "image_width"=>"numeric",
        "image_height"=>"numeric"
    );

    function getFieldHTML($field,$fieldvalue=null)
    {
        $css_class = $this->getCSSClass($field);
        $style_attributes=($field->style_attr)?"style='{$field->style_attr}'":"";
        $fieldid=$field->getHTMLId();
		$upload_path = $field->getParam('upload_path','media'.DS.APP_EXTENSION.DS.'files'.DS.$field->db_name);

        if ($fieldvalue)
        {
            $url_path=str_replace(DS,'/',$upload_path);

            $ret = "<img src='".JURI::root()."$url_path/$fieldvalue' width='150' />
                    <input type='hidden'  name='{$field->db_name}' id='{$fieldid}' value='{$fieldvalue}' />";
			if($field->compulsory)
            {
				$ret .= "<br /> ".JText::_("FACTORY_REPLACE_WITH").":  <br />";
				$ret .= "<br /> <input type='file' name='{$field->db_name}_replace' id='{$fieldid}' $style_attributes />";
            }
			else
				$ret .= "<input type='checkbox' value='1' name='{$field->db_name}_delete' /> ".JText::_("FACTORY_REMOVE");
			return $ret;
            
        }else
			return " <input type='file' name='{$field->db_name}' id='{$fieldid}' $style_attributes />";
    }

	function getValue($field,$source_array){
        jimport('joomla.filesystem.file');

        $upload_path = $field->getParam('upload_path','media'.DS.APP_EXTENSION.DS.'files'.DS.$field->db_name);
        $allowed_extensions=explode(',',$field->getParam('file_extensions','jpg,gif,jpeg,png'));

        $delete_file = JArrayHelper::getValue($source_array,"{$field->db_name}_delete", 0,"INT");

        if ( $delete_file ){
			return "";
		}

        $file=JRequest::getVar($field->db_name.'_replace',null,'files');
        if(!$file['name'])
            $file=JRequest::getVar($field->db_name,null,'files');

        $fname=$file['name'];
        if (! is_uploaded_file($file['tmp_name']) )
            return null;
        $ext = strtolower( JFile::getExt( $fname ) );
        if (!in_array($ext,$allowed_extensions))
            return null;
        $file_name = JFile::makesafe( 'custom-'.trim($field->db_name).'-'.time().".$ext");
        JFile::upload($file['tmp_name'],$upload_path.DS.$file_name);

		return $file_name;
	}


   	function getTemplateHTML($field,$fieldvalue)
    {      
        if (!$fieldvalue) return "";

        JHTML::_("behavior.modal");
        $upload_path = $field->getParam('upload_path','media'.DS.APP_EXTENSION.DS.'files'.DS.$field->db_name);
        $url_path=str_replace(DS,'/',$upload_path);

		$css_atribs = array(
            "border:none",
            "vertical-align:middle"
        );
        $css_atribs[]="width:".$field->getParam("thumbnail_width",150);
        $css_atribs[]="height:".$field->getParam("thumbnail_height",150);

        $rel="{handler: 'iframe', size: {x: ".$field->getParam("image_width","800").", y: ".$field->getParam("image_height","600")."}}";
		$css = "style='".implode(";",$css_atribs)."' ";

		return "<a class='modal' rel=\"{$rel}\" href='".JURI::root()."{$url_path}/{$fieldvalue}' ><img src='".JURI::root()."{$url_path}/{$fieldvalue}' {$css} /></a>";
  }


}


?>
