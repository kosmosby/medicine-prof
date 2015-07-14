<?php
/**
 * @version 2.0
 * @package JomWALL-CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();
error_reporting(0);
/**
 * Renders a multiple item select element
 *
 */
 
class JElementUsergroup extends JElement
{
	var    $_name = 'Usergroup';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$ctrl  = $control_name .'['. $name .']';
		$options = array ();
		foreach ($node->children() as $option){
			$val   = $option->attributes('value');
			$text  = $option->data();
			$options[]= array('usergroup' => $option->attributes('value'), 'text' => $option->data());
		}
		$attribs = ' ';
		$db = & JFactory::getDBO();
	   $db = JFactory::getDbo();
       $query = 'SELECT CONCAT( REPEAT(\'..\', COUNT(parent.id) - 1), node.title) as text, node.id as value'
          . ' FROM #__usergroups AS node, #__usergroups AS parent'
          . ' WHERE node.lft BETWEEN parent.lft AND parent.rgt'
          . ' GROUP BY node.id'
          . ' ORDER BY node.lft';
		$db->setQuery($query);
		$rows = $db->loadAssocList();
		foreach ($rows as $row){
			$options[] = array('usergroup' => $row['value'], 'text' => $row['text']);
		}
		 $attribs   .= 'size="'.count($options).'"';
		 $attribs .= ' multiple="multiple"';
		 $ctrl .= '[]';
		if($options){
			if(!is_array($value)){
				$value = explode(',', $value);
			}
			return JHTML::_('select.genericlist', $options, $ctrl, $attribs, 'usergroup', 'text', $value, $control_name.$name);
		}
		
	}
	
	

}
