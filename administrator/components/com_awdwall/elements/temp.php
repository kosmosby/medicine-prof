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
 
class JElementTemp extends JElement
{
   /**
        * Element name
        *
        * @access       protected
        * @var          string
        */
	var    $_name = 'temp';

	function fetchElement($name, $value, &$node, $control_name)
	{
		
		// Base name of the HTML control.
		$ctrl  = $control_name .'['. $name .']';
		jimport('joomla.filesystem.file');
		// Construct an array of the HTML OPTION statements.
		$options = array ();
		foreach ($node->children() as $option){
			$val   = $option->attributes('value');
			$text  = $option->data();
			$options[]= array('temp' => $option->attributes('value'), 'title' => $option->data());
		}


		$path=str_replace('administrator','',JPATH_BASE);
		$path=$path.'components'.DS.'com_awdwall'.DS.'images'.DS;	
		$ldirs = listdirs($path);
		
		foreach($ldirs as $ldir)
		{
		   $folders[] =str_replace($path.'/','',$ldir);
			
		}

		$dir=str_replace('administrator','',JPATH_BASE);
		$dir=$dir.'components'.DS.'com_awdwall'.DS.'css';
		$needle='style_';
		$length = strlen($needle);
		
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			if($filename != "." && $filename != "..")
			{
				if(substr($filename, 0, $length) === $needle)
				{
					$order='';
					$newstr='';
					$order   = array("style_", ".css");
					$replace = '';
					$newstr = str_replace($order, $replace, $filename);
					if(in_array($newstr,$folders))
					{
					$files[] = $newstr ;
					$options[] = array('temp' => $newstr, 'title' => $newstr);
					}
				}
			}
		}
			
		// Render the HTML SELECT list.
		if($options){
			if(!is_array($value)){
				$value = explode(',', $value);
			}
			return JHTML::_('select.genericlist', $options, $ctrl, $attribs, 'temp', 'title', $value, $control_name.$name);
		}
	}
	
	

}
function listdirs($dir) {
    $dirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($dirs as $dir) {
            $alldirs[] = $dir; 
    }
    return $alldirs;
}