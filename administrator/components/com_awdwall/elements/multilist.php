<?php
/**
 * @version 2.4
 * @package JomWALL-CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();
 
/**
 * Renders a multiple item select element
 *
 */
 
class JElementMultiList extends JElement
{
   /**
        * Element name
        *
        * @access       protected
        * @var          string
        */
	var    $_name = 'MultiList';

	function fetchElement($name, $value, &$node, $control_name)
	{
		// include language file of com profiler
		include_once('../components/com_comprofiler/plugin/language/default_language/default_language.php');
		
		// Base name of the HTML control.
		$ctrl  = $control_name .'['. $name .']';

		// Construct an array of the HTML OPTION statements.
		$options = array ();
		foreach ($node->children() as $option){
			$val   = $option->attributes('value');
			$text  = $option->data();
			$options[]= array('fieldid' => $option->attributes('value'), 'title' => $option->data());
		}

		// Construct the various argument calls that are supported.
		$attribs = ' ';
		if($v = $node->attributes('size')){
			$attribs .= 'size="'.$v.'"';
		}
		if ($v = $node->attributes( 'class' )) {
			$attribs .= 'class="'.$v.'"';
		}else{
			$attribs .= 'class="inputbox"';
		}
		if($m = $node->attributes('multiple')){
			$attribs .= ' multiple="multiple"';
			$ctrl .= '[]';
		}
		
		// Query items for list.
		$db = & JFactory::getDBO();
		$query 	= 'SELECT * FROM #__comprofiler_fields WHERE published = 1 AND pluginid IN (SELECT id FROM #__comprofiler_plugin WHERE published = 1)';
		$db->setQuery($query);
		$rows = $db->loadAssocList();
		foreach ($rows as $row){
			$options[] = array('fieldid' => $row['fieldid'], 'title' => $this->getLangDefinition($row['title']));
		}
		// Render the HTML SELECT list.
		if($options){
			if(!is_array($value)){
				$value = explode(',', $value);
			}
			return JHTML::_('select.genericlist', $options, $ctrl, $attribs, 'fieldid', 'title', $value, $control_name.$name);
		}
	}
	
	function getLangDefinition($text) {
		
		if ( ( strpos( $text, '::' ) === false ) && defined( $text ) ) {
			$returnText		=	constant( $text ); 
		} else {
			$returnText		=	$text;
		}
		return $returnText;
	}
	

}
