<?php
/**
 * @version		$Id: header.php 478 2010-06-16 16:11:42Z joomlaworks $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks, a business unit of Nuevvo Webware Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class JElementHeader3 extends JElement {
	var	$_name = 'header';

	function fetchElement($name, $value, &$node, $control_name){
		// Output
		return '	<div class="link">
    <label id="label-link" class="hasTip"></label>
    <a target="blank" href="http://www.php.net/manual/en/function.date.php">Available formats</a> 
	</div>	';
	}

	function fetchTooltip($label, $description, &$node, $control_name, $name){
		// Output
		return '&nbsp;';
	}

}
