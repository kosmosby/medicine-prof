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

abstract class JHTMLCustomFields
{
    static function FieldType_Params(&$params,$paramvalues=null)
    {
        if (!$params || !count($params)) return null;
        $html="
            	<fieldset class=\"adminform\">
            	<legend>".JText::_( 'FACTORY_CUSTOM_FIELD_PARAMETERS' )."</legend>
            	<table class=\"paramlist admintable\" border=\"0\">

        ";

        $paramobj=new JParameter($paramvalues);
        foreach($params as $param=>$paramtype)
        {
            $func='Param_'.$paramtype;
            $html.=self::$func($param,$paramobj->get($param));
        }
        $html.="</table></fieldset>";
        return $html;
    }
    
    static function Param_numeric($param_name,$current_value)
    {
        $label="PARAM_LABEL_".strtoupper($param_name);
        return '<tr><td class="paramlist_key" width="200" align="right">'.
            JText::_($label).'</td>'.
            '<td class="paramlist_value">'.
            '<input type="text" name="params['.$param_name.']" value="'.$current_value.'" size="10" class="inputbox validate-numeric"/>'.
            '</td></tr>';
    }
    static function Param_text($param_name,$current_value)
    {
        $label="PARAM_LABEL_".strtoupper($param_name);
        return '<tr><td class="paramlist_key" width="200" align="right">'.
            JText::_($label).'</td>'.
            '<td class="paramlist_value">'.
            '<input type="text" name="params['.$param_name.']" value="'.$current_value.'" size="10" class="inputbox"/>'.
            '</td></tr>';
    }
    static function EditField(&$field,$row)
    {
        $field_type= CustomFieldsFactory::getFieldType($field->ftype);
        $field->html=$field_type->getFieldHTML($field,$row->{$field->db_name});
        return $field->html;

    }
    static function SearchField(&$field,$row=null)
    {
        $field_type= CustomFieldsFactory::getFieldType($field->ftype);
        $field->html=$field_type->getSearchHTML($field,$row?$row->{$field->db_name}:null);
        return $field->html;
    }
    static function DisplayField(&$field,$row)
    {
        return '<label class="custom_field">'.JText::_($field->name).'</label>: '.$row->{$field->db_name};
    }
    static function DisplaySearchHtml($fieldlist,$style='table')
    {
        $flist=array();
        $field_object= JTable::getInstance('FieldsTable','JTheFactory');
        
        foreach($fieldlist as $field)
        {
            $field_object->bind($field);
            $f=new stdClass();
            $f->field=clone $field;
            $f->value=null;
            $field_type= CustomFieldsFactory::getFieldType($field->ftype);
            $f->html=$field_type->getSearchHTML($field_object);
            $flist[]=$f;
        }
        $func='DisplayFieldsHtml_'.ucfirst($style);
        $html=self::$func($flist);
        return $html;
    }
    
    static function DisplayFieldsHtml(&$row,$fieldlist,$style='table')
    {
        if (!count($fieldlist)) return null;
        $page=$fieldlist[0]->page;
        $cfg= CustomFieldsFactory::getConfig();

        $category_filter=array();
        if($cfg['has_category'][$page]){
            $db= JFactory::getDBO();
            $db->setQuery("SELECT fid FROM #__".APP_PREFIX."_fields_categories WHERE cid = '".$row->cat."'");
            $category_filter = $db->loadResultArray();
        }
        $flist=array();
        $field_object= JTable::getInstance('FieldsTable','JTheFactory');

        foreach($fieldlist as $field)
        {
     
            if($field->categoryfilter && !in_array( $field->id,$category_filter ) )
                continue;

            $field_type= CustomFieldsFactory::getFieldType($field->ftype);
            
            $field_object->bind($field);
            $f=new stdClass();
            $f->field=clone $field;
            $f->value=$row->{$field->db_name};
            $f->html=$field_type->getFieldHTML($field_object,$row->{$field->db_name});
            $flist[]=$f;
        }
        $func='DisplayFieldsHtml_'.ucfirst($style);
        $html=self::$func($flist);
        return $html;
    }

    static function DisplayFieldsHtml_Table($flist)
    {
        if (!count($flist)) return null;
        $html="<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
        foreach($flist as $f)
        {
            $tooltip="";
            if ($f->field->help) $tooltip=JHtml::_('tooltip',$f->field->help);
            $html.="
                <tr>
                    <td><label class='custom_field'>".JText::_($f->field->name)."</label>: $tooltip</td>
                    <td>".$f->html."</td>
                </tr>
            ";
        }
        $html.="</table>";
        return $html;
    }

    static function DisplayFieldsHtml_Divs($flist)
    {
        if (!count($flist)) return null;

        $html = '';

        foreach ($flist as $f) {
            $tooltip = "";
            if ($f->field->help) $tooltip = JHtml::_('tooltip', $f->field->help);

            $html .= '<div class="auction_search_field">
                <label class="auction_lables">
                    <label class="custom_field">' . JText::_($f->field->name) . ':</label>'
                .'</label>
                '. $f->html.'&nbsp;'. $tooltip .
            '</div>';
        }

        return $html;
    }
} 



?>
