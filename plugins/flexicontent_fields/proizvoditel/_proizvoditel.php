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

class plgFlexicontent_fieldsProizvoditel extends JPlugin
{
	static $field_types = array('proizvoditel', 'maintext');
	
	// ***********
	// CONSTRUCTOR
	// ***********
	
	function plgFlexicontent_fieldsProizvoditel( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		JPlugin::loadLanguage('plg_flexicontent_fields_proizvoditel', JPATH_ADMINISTRATOR);
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
			$field->value = array();
			$field->value[0] = JText::_($default_value);
		} else {
			$tmp_val = unserialize($field->value[0]);
                        $field->value[0] = '';
			for($intA = 0; $intA < count($tmp_val['country']); $intA++){
                            $field->value[$intA]['country'] = $tmp_val['country'][$intA];
                            $field->value[$intA]['naimen'] = $tmp_val['naimen'][$intA];
                            $field->value[$intA]['vypusk'] = $tmp_val['vypusk'][$intA];
                            $field->value[$intA]['reg'] = $tmp_val['reg'][$intA];
                            $field->value[$intA]['date'] = $tmp_val['date'][$intA];
                        }
                        //var_dump($field->value[0]);
		}
                
                $fieldname = FLEXI_J16GE ? 'custom['.$field->name.'][country][]' : $field->name.'[]';
		$elementid = FLEXI_J16GE ? 'custom_'.$field->name : $field->name;
		
		$js = "";
                //multiple edit
                $document->addScript( JURI::root(true).'/components/com_flexicontent/assets/js/sortables.js' );
			
			// Add the drag and drop sorting feature
			$js .= "
			jQuery(document).ready(function(){
				jQuery('#sortables_".$field->id."').sortable({
					handle: '.fcfield-drag',
					containment: 'parent',
					tolerance: 'pointer'
				});
			});
			";
			
			//if ($max_values) FLEXI_J16GE ? JText::script("FLEXI_FIELD_MAX_ALLOWED_VALUES_REACHED", true) : fcjsJText::script("FLEXI_FIELD_MAX_ALLOWED_VALUES_REACHED", true);
			$js .= "
			var uniqueRowNum".$field->id."	= ".count($field->value).";  // Unique row number incremented only
			var rowCount".$field->id."	= ".count($field->value).";      // Counts existing rows to be able to limit a max number of values
			var maxValues".$field->id." = ".$max_values.";

			function addField".$field->id."(el) {
				if((rowCount".$field->id." >= maxValues".$field->id.") && (maxValues".$field->id." != 0)) {
					alert(Joomla.JText._('FLEXI_FIELD_MAX_ALLOWED_VALUES_REACHED') + maxValues".$field->id.");
					return 'cancel';
				}
				
				var thisField 	 = jQuery(el).prev().children().last().children().last();

				var thisNewField = thisField.clone();
				
				jQuery(thisNewField).find('input').val('');  /* First element is the value input field, second is e.g remove button */
                                jQuery(thisNewField).find('input').last().val('".JText::_( 'FLEXI_REMOVE_VALUE' )."');
				var has_inputmask = jQuery(thisNewField).find('input.has_inputmask').length != 0;
				if (has_inputmask)  jQuery(thisNewField).find('input.has_inputmask').inputmask();
				
				var has_select2 = jQuery(thisNewField).find('div.select2-container').length != 0;
				if (has_select2) {
					jQuery(thisNewField).find('div.select2-container').remove();
					jQuery(thisNewField).find('select.use_select2_lib').select2();
				}
				
				jQuery(thisNewField).css('display', 'none');
				jQuery(thisNewField).insertAfter( jQuery(thisField) );

				var input = jQuery(thisNewField).find('input').first();
				input.attr('id', '".$elementid."_'+uniqueRowNum".$field->id.");
				";
			
			/*if ($field->field_type=='textselect') $js .= "
				thisNewField.parent().find('select.fcfield_textselval').val('');
				";*/
			
			$js .= "
				
				
				jQuery(thisNewField).show('slideDown');
				
				rowCount".$field->id."++;       // incremented / decremented
				uniqueRowNum".$field->id."++;   // incremented only
                                     
			}

			function deleteField".$field->id."(el)
			{
				if(rowCount".$field->id." <= 1) return;
				var row = jQuery(el).closest('tr');
				jQuery(row).hide('slideUp', function() { this.remove(); } );
				rowCount".$field->id."--;
                                   
			}
			";
			
			$css = '
			#sortables_'.$field->id.' { float:left; margin: 0px; padding: 0px; list-style: none; white-space: nowrap; }
			#sortables_'.$field->id.' li {
				clear: both;
				display: block;
				list-style: none;
				height: auto;
				position: relative;
			}
			#sortables_'.$field->id.' li.sortabledisabled {
				background : transparent url(components/com_flexicontent/assets/images/move3.png) no-repeat 0px 1px;
			}
			#sortables_'.$field->id.' li input { cursor: text;}
			#add'.$field->name.' { margin-top: 5px; clear: both; display:block; }
			#sortables_'.$field->id.' li .admintable { text-align: left; }
			#sortables_'.$field->id.' li:only-child span.fcfield-drag, #sortables_'.$field->id.' li:only-child input.fcfield-button { display:none; }
                            .td_main_proizvod label {font-size:75%;font-weight: normal;}
			';
			
			$remove_button = '<input class="fcfield-button" type="button" value="'.JText::_( 'FLEXI_REMOVE_VALUE' ).'" onclick="deleteField'.$field->id.'(this);" />';
			$move2 	= '<span class="fcfield-drag">'.JHTML::image( JURI::base().'components/com_flexicontent/assets/images/move2.png', JText::_( 'FLEXI_CLICK_TO_DRAG' ) ) .'</span>';

                
                if ($js)  $document->addScriptDeclaration($js);
		if ($css) $document->addStyleDeclaration($css);
		$db = JFactory::getDBO();
		$db->setQuery("SELECT country_name as name, country_name as id FROM #__comprofiler_countries ORDER BY country_name");
                $countries = $db->loadObjectList();
		$classes = 'fcfield_textval inputbox'.$required.($inputmask ? ' has_inputmask' : '');
		
		$field->html = $html_body = array();
		$n = 0;
                
		foreach ($field->value as $value)
		{
			$elementid_n = $elementid.'_'.$n;
			
			//$text_field = '<input '. $validate_mask .' id="'.$elementid_n.'" name="custom['.$field->name.'][0][country][]" class="'.$classes.'" type="text" size="'.$size.'" value="'.$value["country"].'" '.$attribs.' />';
                        $text_field = JHTML::_('select.genericlist',   $countries, 'custom['.$field->name.'][0][country][]', 'class="'.$classes.'" size="1" style="width:360px;"'.$attribs, 'id', 'name', $value["country"] );
	
			$text_field_naimen = '<textarea style="width:360px;" rows="7" name="custom['.$field->name.'][0][naimen][]">'.$value["naimen"].'</textarea>';
                        $text_field_vypusk = '<input name="custom['.$field->name.'][0][vypusk][]" class="'.$classes.'" type="text" size="57" value="'.$value["vypusk"].'" '.$attribs.' />';
			$text_field_reg = '<input name="custom['.$field->name.'][0][reg][]" class="'.$classes.'" type="text" size="'.$size.'" value="'.$value["reg"].'" '.$attribs.' />';
			$text_field_date = '<input name="custom['.$field->name.'][0][date][]" class="'.$classes.'" type="text" size="'.$size.'" value="'.$value["date"].'" '.$attribs.' />';
			
                        
			$html_body[] = '<td style="border:1px solid #333; padding:5px;" class="td_main_proizvod">'
                                        .'<table width="100%">'
                                        .'<tr><td colspan="2"><label style="width:180px;">'.JText::_("Страна").':</label> '.$text_field.'</td></tr>'
                                        .'<tr><td colspan="2"><label style="width:180px;vertical-align:top;">'.JText::_("Наименование").':</label> '.$text_field_naimen.'</td></tr>'
                                        .'<tr><td colspan="2"><label style="width:180px;">'.JText::_("Форма выпуска").':</label> '.$text_field_vypusk.'</td></tr>'
                                        .'<tr><td><label style="width:180px;">'.JText::_("Регистрационный №").':</label> '.$text_field_reg.'</td>'
                                        .'<td><label style="width:180px;">'.JText::_("Дата окончания регистрации").':</label> '.$text_field_date.'</td></tr></table>'
                                    .'</td>'
                                    .'<td>'.$remove_button.'</td>';
			
			$n++;
			//if (!$multiple) break;  // multiple values disabled, break out of the loop, not adding further values even if the exist
		}
		
                $header_html = '<table class=\"fcfield-sortables\" id=\"sortables_'.$field->id.'\">'
                            ;
		
                $_list = "<tbody ><tr>". implode("</tr>\n<tr>", $html_body) ."</tr></tbody>\n";
                $field->html = $header_html . $_list . '</table>
                        <input type="button" class="fcfield-addvalue" onclick="addField'.$field->id.'(this);" value="'.JText::_( 'FLEXI_ADD_VALUE' ).'" />
                ';
		
                
                
                
                
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
			$tmp_val = unserialize($field->{$prop});
                        //var_dump($tmp_val);
                        $header_html = '<table class="flexitable">'
                            .'<thead>'
                                .'<tr>'
                                    .'<th style="font-size:80%;">'.JText::_("Страна").'</th>'
                                    .'<th style="font-size:80%;">'.JText::_("Наименование").'</th>'
                                    .'<th style="font-size:80%;">'.JText::_("Форма выпуска").'</th>'
                                    .'<th style="font-size:80%;">'.JText::_("Регистрационный №").'</th>'
                                    .'<th style="font-size:80%;">'.JText::_("Дата окончания регистрации").'</th>'
                                .'</tr>'
                            .'</thead>'
                            .'<tbody>';
			for($intA = 0; $intA < count($tmp_val['country']); $intA++){
                            $header_html .= '<tr>';
                            $header_html .= '<td>' . $tmp_val['country'][$intA] . '</td>';
                            $header_html .= '<td>' . $tmp_val['naimen'][$intA] . '</td>';
                            $header_html .= '<td>' . $tmp_val['vypusk'][$intA] . '</td>';
                            $header_html .= '<td>' . $tmp_val['reg'][$intA] . '</td>';
                            $header_html .= '<td>' . $tmp_val['date'][$intA] . '</td>';
                            $header_html .= '<tr>';
                        }
                        $header_html .= '</tbody>';
                        $header_html .= '</table>';
                        $field->{$prop} = $header_html;
                        //var_dump($field->{$prop});
                        //var_dump($field->value[0]);
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
		// execute the code only if the field type match the plugin type
		if ( !in_array($filter->field_type, self::$field_types) ) return;
		
		$filter->parameters->set( 'display_filter_as_s', 1 );  // Only supports a basic filter of single text search input
		FlexicontentFields::createFilter($filter, $value, $formName);
	}
	
	
 	// Method to get the active filter result (an array of item ids matching field filter, or subquery returning item ids)
	// This is for search view
	function getFilteredSearch(&$field, $value)
	{
		if ( !in_array($field->field_type, self::$field_types) ) return;
		
		$field->parameters->set( 'display_filter_as_s', 1 );  // Only supports a basic filter of single text search input
		return FlexicontentFields::getFilteredSearch($field, $value, $return_sql=true);
	}
	
	
	
	// *************************
	// SEARCH / INDEXING METHODS
	// *************************
	
	// Method to create (insert) advanced search index DB records for the field values
	function onIndexAdvSearch(&$field, &$post, &$item) {
		if ( !in_array($field->field_type, self::$field_types) ) return;
		if ( !$field->isadvsearch && !$field->isadvfilter ) return;
		
		FlexicontentFields::onIndexAdvSearch($field, $post, $item, $required_properties=array(), $search_properties=array(), $properties_spacer=' ', $filter_func='strip_tags');
		return true;
	}
	
	
	// Method to create basic search index (added as the property field->search)
	function onIndexSearch(&$field, &$post, &$item)
	{
		if ( !in_array($field->field_type, self::$field_types) ) return;
		if ( !$field->issearch ) return;
		$pst = array();
                if(isset($post[0]['country'])){
                    foreach($post[0]['country'] as $country)
                        $pst[] = $country;
                    
                    //$post = $pst;
                }
                
		FlexicontentFields::onIndexSearch($field, $pst, $item, $required_properties=array(), $search_properties=array(), $properties_spacer=' ', $filter_func='strip_tags');
		return true;
	}
	
	
	
	// **********************
	// VARIOUS HELPER METHODS
	// **********************
	
	// Method to parse a given text for tabbing code
	function parseTabs(&$field, &$item) {
		$editorarea_per_tab = $field->parameters->get('editorarea_per_tab', 0);

		$start_of_tabs_pattern = $field->parameters->get('start_of_tabs_pattern');
		$end_of_tabs_pattern = $field->parameters->get('end_of_tabs_pattern');
		
		$start_of_tabs_default_text = $field->parameters->get('start_of_tabs_default_text');  // Currently unused
		$default_tab_list = $field->parameters->get('default_tab_list');                      // Currently unused
		
		$title_tab_pattern = $field->parameters->get('title_tab_pattern');
		$start_of_tab_pattern = $field->parameters->get('start_of_tab_pattern');
		$end_of_tab_pattern = $field->parameters->get('end_of_tab_pattern');
		
		$field_value = & $field->value[0];
		$field->tabs_detected = false;
		
		// MAKE MAIN TEXT FIELD OR TEXTAREAS TABBED
		if ( $editorarea_per_tab ) {
			
			//echo 'tabs start: ' . preg_match_all('/'.$start_of_tabs_pattern.'/u', $field_value ,$matches) . "<br />";
			//print_r ($matches); echo "<br />";
			
			//echo 'tabs end: ' . preg_match_all('/'.$end_of_tabs_pattern.'/u', $field_value ,$matches) . "<br />";
			//print_r ($matches); echo "<br />";
			
			$field->tabs_detected = preg_match('/' .'(.*)('.$start_of_tabs_pattern .')(.*)(' .$end_of_tabs_pattern .')(.*)'. '/su', $field_value ,$matches);
			
			if ($field->tabs_detected) {
				$field->tab_info = new stdClass();
				$field->tab_info->beforetabs = $matches[1];
				$field->tab_info->tabs_start = $matches[2];
				$insidetabs = $matches[3];
				$field->tab_info->tabs_end   = $matches[4];
				$field->tab_info->aftertabs  = $matches[5];
				
				//echo 'tab start: ' . preg_match_all('/'.$start_of_tab_pattern.'/u', $insidetabs ,$matches) . "<br />";
				//echo "<pre>"; print_r ($matches); echo "</pre><br />";									
				
				//echo 'tab end: ' . preg_match_all('/'.$end_of_tab_pattern.'/u', $insidetabs ,$matches) . "<br />";
				//print_r ($matches); echo "<br />";
				
				$tabs_count = preg_match_all('/('.$start_of_tab_pattern .')(.*?)(' .$end_of_tab_pattern .')/su', $insidetabs ,$matches) . "<br />";
				
				if ($tabs_count) {
					$tab_startings = $matches[1];
					
					foreach ($tab_startings as $i => $v) {
						$title_matched = preg_match('/'.$title_tab_pattern.'/su', $tab_startings[$i] ,$title_matches) . "<br />";
						//echo "<pre>"; print_r($title_matches); echo "</pre>";
						$tab_titles[$i] = $title_matches[1];
					}
					
					$tab_contents = $matches[2];
					$tab_endings = $matches[3];
					//foreach ($tab_titles as $tab_title) echo "$tab_title &nbsp; &nbsp; &nbsp;";
				} else {
					echo "FALIED while parsing tabs<br />";
					$field->tabs_detected = 0;
				}
				
				$field->tab_info->tab_startings = & $tab_startings;
				$field->tab_info->tab_titles    = & $tab_titles;
				$field->tab_info->tab_contents  = & $tab_contents;
				$field->tab_info->tab_endings   = & $tab_endings;
			}
		}
	}
}
