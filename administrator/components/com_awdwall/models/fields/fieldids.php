<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
 error_reporting(0);
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
jimport('joomla.form.formfield');

JFormHelper::loadFieldClass('list');

class JFormFieldfieldids extends JFormFieldList {

public $type = 'fieldids';
protected function getOptions()
{
		include_once('../components/com_comprofiler/plugin/language/default_language/default_language.php');
		$db = & JFactory::getDBO();
		$query 	= 'SELECT * FROM #__comprofiler_fields WHERE published = 1 AND pluginid IN (SELECT id FROM #__comprofiler_plugin WHERE published = 1)';
		$db->setQuery($query);
		$rows = $db->loadAssocList();
		foreach ($rows as $row){
		if ( ( strpos( $row['title'], '::' ) === false ) && defined( $row['title'] ) ) {
			$returnText		=	constant( $row['title'] ); 
		} else {
			$returnText		=	$row['title'];
		}
		
			$options[] = array('value' => $row['fieldid'], 'text' => $returnText);
		}
	return $options;	
}

}



