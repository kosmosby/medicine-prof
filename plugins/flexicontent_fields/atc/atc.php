<?php
/**
 * @version 1.0 $Id: textarea.php 1937 2014-08-26 10:27:07Z ggppdk $
 * @package Joomla
 * @subpackage FLEXIcontent
 * @subpackage plugin.textarea
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 *
 * FLEXIcontent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');

class plgFlexicontent_fieldsAtc extends JPlugin
{
	static $field_types = array('atc', 'maintext');
	
	// ***********
	// CONSTRUCTOR
	// ***********
	
	function plgFlexicontent_fieldsAtc( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		JPlugin::loadLanguage('plg_flexicontent_fields_atc', JPATH_ADMINISTRATOR);
	}
	
	
	
	// *******************************************
	// DISPLAY methods, item form & frontend views
	// *******************************************
	
	// Method to create field's HTML display for item form
	function onDisplayField(&$field, &$item)
	{
		// execute the code only if the field type match the plugin type
		if ( !in_array($field->field_type, self::$field_types) ) return;
		
		$field->label = JText::_($field->label);
		$document  = JFactory::getDocument();
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$editor_name = $user->getParam('editor', $app->getCfg('editor'));
		$editor = JFactory::getEditor($editor_name);
		$max_values = 100;
                $default_value_use = $field->parameters->get( 'default_value_use', 0 ) ;
		$default_value     = ($item->version == 0 || $default_value_use > 0) ? $field->parameters->get( 'default_value', '' ) : '';
		$size = 13;
                
                if ( !$field->value ) {
			$cur_val = '';
		} else {
			$cur_val[0] = substr($field->value[0],0,1);
                        $cur_val[1] = substr($field->value[0],1,2);
                        $cur_val[2] = substr($field->value[0],3,1);
                        $cur_val[3] = substr($field->value[0],4,1);
                        $cur_val[4] = substr($field->value[0],5,2);
		}
                //print_r($field->value);
                $fieldname = FLEXI_J16GE ? 'custom['.$field->name.']' : $field->name.'[]';
		$elementid = FLEXI_J16GE ? 'custom_'.$field->name : $field->name;
		
                $css = '
			.mcctable { float:left; margin: 0px; padding: 0px; list-style: none; white-space: nowrap; font-size:85%; width:100%;}
			
			';
                

                
                if ($js)  $document->addScriptDeclaration($js);
		if ($css) $document->addStyleDeclaration($css);
		
		$classes = 'fcfield_textval inputbox'.$required.($inputmask ? ' has_inputmask' : '');
		
		$field->html = $html_body = array();
		$n = 0;
                
                $alpha_arr = range('A', 'Z');
                $options_n = array();
                $options_n[] = JHTML::_('select.option', '--', JText::_('FLEXI_PLEASE_SELECT'));
                for($intA = 1; $intA < 17; $intA++){
                    //$num_arr = printf("%2d", $intA);
                    $options_n[] = JHTML::_('select.option', sprintf("%02d", $intA), sprintf("%02d", $intA));
                }
                $options_a = array();
                $options_a[] = JHTML::_('select.option', '-', JText::_('FLEXI_PLEASE_SELECT'));
                foreach($alpha_arr as $arr){
                    $options_a[] = JHTML::_('select.option', $arr, $arr);
                }
                
                
                $text_field_1 = JHTML::_('select.genericlist',   $options_a, 'custom['.$field->name.'_0]', 'class="'.$classes.'" size="1" style="width:140px;"'.$attribs, 'value', 'text', ($cur_val ? $cur_val[0] : '') );
                $text_field_2 = JHTML::_('select.genericlist',   $options_n, 'custom['.$field->name.'_1]', 'class="'.$classes.'" size="1" style="width:140px;"'.$attribs, 'value', 'text', ($cur_val ? $cur_val[1] : '') );
                $text_field_3 = JHTML::_('select.genericlist',   $options_a, 'custom['.$field->name.'_2]', 'class="'.$classes.'" size="1" style="width:140px;"'.$attribs, 'value', 'text', ($cur_val ? $cur_val[2] : '') );
                $text_field_4 = JHTML::_('select.genericlist',   $options_a, 'custom['.$field->name.'_3]', 'class="'.$classes.'" size="1" style="width:140px;"'.$attribs, 'value', 'text', ($cur_val ? $cur_val[3] : '') );
                $text_field_5 = JHTML::_('select.genericlist',   $options_n, 'custom['.$field->name.'_4]', 'class="'.$classes.'" size="1" style="width:140px;"'.$attribs, 'value', 'text', ($cur_val ? $cur_val[4] : '') );
                
                $html_body[] = '<td>'.JText::_('анатомический индекс').'<br />'.$text_field_1.'</td>'
                            .'<td>'.JText::_('терапевтический индекс 1').'<br />'.$text_field_2.'</td>'
                            .'<td>'.JText::_('терапевтический индекс 2').'<br />'.$text_field_3.'</td>'
                        .'<td>'.JText::_('групповой химический индекс').'<br />'.$text_field_4.'</td>'
                        .'<td>'.JText::_('Индекс МНН').'<br />'.$text_field_5.'</td>';
		
		
                $header_html = '<table class="mcctable">'
                            ;
		
                $_list = "<tbody ><tr>". implode("</tr>\n<tr>", $html_body) ."</tr></tbody>\n";
                $field->html = $header_html . $_list . '</table>';
		
                
                
                
                
	}
	
	
	// Method to create field's HTML display for frontend views
	function onDisplayFieldValue(&$field, $item, $values=null, $prop='display')
	{
		// execute the code only if the field type match the plugin type
		if ( !in_array($field->field_type, self::$field_types) ) return;
		
		$field->label = JText::_($field->label);
		
		// Some variables
		$document = JFactory::getDocument();
		$view = JRequest::setVar('view', JRequest::getVar('view', FLEXI_ITEMVIEW));
		
		// Get field values
		$values = $values ? $values : $field->value;
		// DO NOT terminate yet if value is empty since a default value on empty may have been defined
		
		// Handle default value loading, instead of empty value
		$default_value_use= $field->parameters->get( 'default_value_use', 0 ) ;
		$default_value		= ($default_value_use == 2) ? $field->parameters->get( 'default_value', '' ) : '';
		if ( empty($values) && !strlen($default_value) ) {
			$field->{$prop} = '';
			return;
		} else if ( empty($values) && strlen($default_value) ) {
			$values = array($default_value);
		}
		
		// Prefix - Suffix - Separator parameters, replacing other field values if found
		$opentag		= FlexicontentFields::replaceFieldValue( $field, $item, $field->parameters->get( 'opentag', '' ), 'opentag' );
		$closetag		= FlexicontentFields::replaceFieldValue( $field, $item, $field->parameters->get( 'closetag', '' ), 'closetag' );
		
		// some parameter shortcuts
		$use_html			= $field->parameters->get( 'use_html', 0 ) ;
		
		// Get ogp configuration
		$useogp     = $field->parameters->get('useogp', 0);
		$ogpinview  = $field->parameters->get('ogpinview', array());
		$ogpinview  = FLEXIUtilities::paramToArray($ogpinview);
		$ogpmaxlen  = $field->parameters->get('ogpmaxlen', 300);
		$ogpusage   = $field->parameters->get('ogpusage', 0);
		
		// Apply seperator and open/close tags
		if ($values) {
			$field->{$prop} = $use_html ? $values[0] : nl2br($values[0]);
			$field->{$prop} = $opentag . $field->{$prop} . $closetag;
		} else {
			$field->{$prop} = '';
		}
		
		if ($useogp && $field->{$prop}) {
			if ( in_array($view, $ogpinview) ) {
				switch ($ogpusage)
				{
					case 1: $usagetype = 'title'; break;
					case 2: $usagetype = 'description'; break;
					default: $usagetype = ''; break;
				}
				if ($usagetype) {
					$content_val = flexicontent_html::striptagsandcut($field->{$prop}, $ogpmaxlen);
					$document->addCustomTag('<meta property="og:'.$usagetype.'" content="'.$content_val.'" />');
				}
			}
		}
                
                
                
                //view
                if ( !$field->{$prop} ) {
			
		} else {
			
		}
                
	}
	
	
	
	// **************************************************************
	// METHODS HANDLING before & after saving / deleting field events
	// **************************************************************
	
	// Method to handle field's values before they are saved into the DB
	function onBeforeSaveField( &$field, &$post, &$file, &$item )
	{
		// execute the code only if the field type match the plugin type
		if ( !in_array($field->field_type, self::$field_types) ) return;
		if ( !FLEXI_J16GE && $field->parameters->get( 'use_html', 0 ) ) {
			$rawdata = JRequest::getVar($field->name, '', 'post', 'string', JREQUEST_ALLOWRAW);
			if ($rawdata) $post = $rawdata;
		}
		if ( !is_array($post) && !strlen($post) ) return;
		$str = '';
                for($i=0; $i < 5; $i++){
                    $a = $_POST['custom'][$field->name.'_'.$i];
                    //$rawdata = JRequest::getVar('custom['.$field->name.'_'.$i.']', '', 'post', 'string', JREQUEST_ALLOWRAW);
                
                    $str .= $a;
                }
                $post = $str;
		// Reconstruct value if it has splitted up e.g. to tabs
		if (is_array($post)) {
			$tabs_text = '';
			foreach($post as $tab_text) {
				$tabs_text .= $tab_text;
			}
			$post = & $tabs_text;
		}
	}
	
	
	// Method to take any actions/cleanups needed after field's values are saved into the DB
	function onAfterSaveField( &$field, &$post, &$file, &$item ) {
	}
	
	
	// Method called just before the item is deleted to remove custom item data related to the field
	function onBeforeDeleteField(&$field, &$item) {
	}
	
	
	
	// *********************************
	// CATEGORY/SEARCH FILTERING METHODS
	// *********************************
	
	// Method to display a search filter for the advanced search view
	function onAdvSearchDisplayFilter(&$filter, $value='', $formName='searchForm')
	{
		if ( !in_array($filter->field_type, self::$field_types) ) return;
		
		plgFlexicontent_fieldsAtc::onDisplayFilter($filter, $value, $formName);
	}
	
	
	// Method to display a category filter for the category view
	function onDisplayFilter(&$filter, $value='', $formName='adminForm')
	{
		// execute the code only if the field type match the plugin type
		if ( !in_array($filter->field_type, self::$field_types) ) return;
		
		FlexicontentFields::createFilter($filter, $value, $formName);
	}	
	
	
	// Get item ids having the value(s) of filter
	function getFiltered(&$filter, $value)
	{
		// execute the code only if the field type match the plugin type
		if ( !in_array($filter->field_type, self::$field_types) ) return;
		
		return FlexicontentFields::getFiltered($filter, $value, $return_sql=true);
	}
	
		
 	// Method to get the active filter result (an array of item ids matching field filter, or subquery returning item ids)
	// This is for search view
	function getFilteredSearch(&$field, $value)
	{
		if ( !in_array($field->field_type, self::$field_types) ) return;
		
		return FlexicontentFields::getFilteredSearch($field, $value, $return_sql=true);
	}
	
	
	
	// *************************
	// SEARCH / INDEXING METHODS
	// *************************
	
	// Method to create (insert) advanced search index DB records for the field values
	function onIndexAdvSearch(&$field, &$post, &$item) {
		// execute the code only if the field type match the plugin type
		if ( !in_array($field->field_type, self::$field_types) ) return;
		if ( !$field->isadvsearch && !$field->isadvfilter ) return;
		
		FLEXIUtilities::call_FC_Field_Func('text', 'onIndexAdvSearch', array(&$field, &$post, &$item));
		return true;
	}
	
	
	// Method to create basic search index (added as the property field->search)
	function onIndexSearch(&$field, &$post, &$item)
	{
		if ( !in_array($field->field_type, self::$field_types) ) return;
		if ( !$field->issearch ) return;
		
		FlexicontentFields::onIndexSearch($field, $post, $item, $required_properties=array(), $search_properties=array(), $properties_spacer=' ', $filter_func='strip_tags');
		return true;
	}
	
	
	
	// **********************
	// VARIOUS HELPER METHODS
	// **********************
	
	function _prepareField_as_fieldsAtc(&$field)
	{
		// Field parameters meant to be used by select field are prefixed with 'select_'
		// *** THESE include only parameters used for creating filter's display ***
		$arrays = $field->parameters->toArray();
		foreach($arrays as $k=>$a) {
			$select_ = substr($k, 0, 7);
			if($select_=='select_') {
				$keyname = $select_ = substr($k, 7);
				$field->parameters->set($keyname, $field->parameters->get($k));
			}
		}
		
		if ( !$field->parameters->get('sql_mode_override') ) {
			// Default is to use all text's field values
			$query = "SELECT value, value as text FROM #__flexicontent_fields_item_relations as fir WHERE field_id='{field_id}' AND value != '' GROUP BY value";
		} else {
			// Custom query for value retrieval
			$query = $field->parameters->set('sql_mode_query');
		}
		$query = str_replace('{field_id}', $field->id, $query);
		
		// Set remaining parameters needed for Select Field
		$field->parameters->set('sql_mode', 1);
		$field->parameters->set('field_elements', $query);		
	}
}
