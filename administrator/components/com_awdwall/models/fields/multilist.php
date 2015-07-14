<?php 
 error_reporting(0);
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
JFormHelper::loadFieldClass('list');
class JFormFieldmultilist extends JFormFieldList
{
/**
* The form field type.
*
* @var	string
* @since	1.6
*/
public $type = 'multilist';

/**
* Method to get the field options.
*
* @return	array	The field option objects.
* @since	1.6
*/
protected function getOptions()
{
		// include language file of com profiler
		include_once('../components/com_comprofiler/plugin/language/default_language/default_language.php');
		
//		// Base name of the HTML control.
//		$ctrl  = $control_name .'['. $name .']';
//
//		// Construct an array of the HTML OPTION statements.
//		$options = array ();
//		foreach ($node->children() as $option){
//			$val   = $option->attributes('value');
//			$text  = $option->data();
//			$options[]= array('fieldid' => $option->attributes('value'), 'title' => $option->data());
//		}

		// Construct the various argument calls that are supported.
//		$attribs = ' ';
//		if($v = $node->attributes('size')){
//			$attribs .= 'size="'.$v.'"';
//		}
//		if ($v = $node->attributes( 'class' )) {
//			$attribs .= 'class="'.$v.'"';
//		}else{
//			$attribs .= 'class="inputbox"';
//		}
//		if($m = $node->attributes('multiple')){
//			$attribs .= ' multiple="multiple"';
//			$ctrl .= '[]';
//		}
		
		// Query items for list.
		$db = & JFactory::getDBO();
		$query 	= 'SELECT * FROM #__comprofiler_fields WHERE published = 1 AND pluginid IN (SELECT id FROM #__comprofiler_plugin WHERE published = 1)';
		$db->setQuery($query);
		$rows = $db->loadAssocList();
		foreach ($rows as $row){
			$options[] = array('value' => $row['fieldid'], 'text' => getLangDefinition($row['title']));
		}
		
	// Merge any additional options in the XML definition.
	$options = array_merge(parent::getOptions(), $options);
	return $options;	
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
?>