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

class FieldType_date extends FactoryFieldTypes{

    var $type_name 		= "Date Picker";
    var $class_name 	= "date";
    var $has_options	= false;
    var $multiple		= false;
    var $sql_type 		= "varchar";
    var $length 		= 30;
    var $store_as_id    = false;
    var $_params=array(
        "size"=>"numeric",
        "date_format"=>"text"
    );

    
    function dateFormatConversion($phpformat)
    {
        /* Joomla's calendar format:
        
            %a � abbreviated weekday name
            %A � full weekday name
            %b � abbreviated month name
            %B � full month name
            %C � the century number
            %d � the day of the month (range 01 to 31)
            %e � the day of the month (range 1 to 31)
            %H � hour, range 00 to 23 (24h format)
            %I � hour, range 01 to 12 (12h format)
            %j � day of the year (range 001 to 366)
            %k � hour, range 0 to 23 (24h format)
            %l � hour, range 1 to 12 (12h format)
            %m � month, range 01 to 12
            %o � month, range 1 to 12
            %M � minute, range 00 to 59
            %n � a newline character
            %p � PM or AM
            %P � pm or am
            %s � UNIX time (number of seconds since 1970-01-01)
            %S � seconds, range 00 to 59
            %t � a tab character
            %W � week number
            %u � the day of the week (range 1 to 7, 1 = MON)
            %w � the day of the week (range 0 to 6, 0 = SUN)
            %y � year without the century (range 00 to 99)
            %Y � year with the century
            %% � a literal '%' character
        
        */
        $calendar_format=$phpformat;
        $calendar_format=str_replace('%','%%',$calendar_format);
        $calendar_format=str_replace('Y','%Y',$calendar_format);
        $calendar_format=str_replace('y','%y',$calendar_format);
        $calendar_format=str_replace('d','%d',$calendar_format);
        $calendar_format=str_replace('D','%A',$calendar_format);
        $calendar_format=str_replace('m','%m',$calendar_format);
        $calendar_format=str_replace('F','%B',$calendar_format);
        return $calendar_format;
    }
    
    function getFieldHTML($field,$fieldvalue=null)
    {
		JHTML::_('behavior.calendar');
        /* @var $field JTheFactoryFieldsTable */

        $fieldid=$field->getHTMLId();
        $css_class = $this->getCSSClass($field);
        $style_attributes=($field->style_attr)?"style='{$field->style_attr}'":"";

        $size="size='".$field->getParam('size',15)."'";
        $date_format=$field->getParam('date_format','Y-m-d');
        $date=strtotime($fieldvalue);
        
        $html=JHtml::_('calendar',$date,$field->db_name,$fieldid,self::dateFormatConversion($date_format),"class='{$css_class}' $size $style_attributes");
            
        return $html;
    }    
    function getSearchHTML($field,$fieldvalue=null)
    {
        $f=clone $field;
        $f->db_name=$f->page.'%'.$f->db_name;
        return $this->getFieldHTML($f,$fieldvalue);
    }

}

?>
