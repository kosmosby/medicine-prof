<?php 
 error_reporting(0);
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
JFormHelper::loadFieldClass('list');
class JFormFieldtemp extends JFormFieldList
{
/**
* The form field type.
*
* @var	string
* @since	1.6
*/
public $type = 'temp';

/**
* Method to get the field options.
*
* @return	array	The field option objects.
* @since	1.6
*/
protected function getOptions()
{
// Get the database object and a new query object.
		$path=str_replace('administrator','',JPATH_BASE);
		$path=$path.'components'.DS.'com_awdwall'.DS.'images'.DS;	
		$ldirs = listdirs($path);
		
		//print_r($ldirs);
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
					$options[] = array('value' => $newstr, 'text' => $newstr);
					}
				}
			}
		}
// Merge any additional options in the XML definition.
$options = array_merge(parent::getOptions(), $options);

return $options;
}
}

function listdirs($dir) {
    $dirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($dirs as $dir) {
            $alldirs[] = $dir; 
    }
    return $alldirs;
}
?>