<?php
/**
 * @version 1.6
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
defined('_JEXEC') or die('Restricted access');

class JElementHeader extends JElement {
	var	$_name = 'header';

	function fetchElement($name, $value, &$node, $control_name){
		// Output
//		return '
//		&nbsp;</td></tr><tr><td  colspan="2">
//<table width="100%" border="0" cellspacing="2" cellpadding="2">
//  <tr>
//    <td align="left" valign="top" width="54"><img src="'.JURI::base().'components/com_awdwall/images/defult-global.jpg" /></td>
//    <td align="left" valign="top"><b>Sharing Wall Default Global Setting</b><br>Set Overall Sharing Wall Display Setting for Jomwall </td>
//  </tr>
//</table>
//
//		';
		return '<div class="awdparamheader"><div class="awdparamheaderinner">'.JText::_($value).'</div><div class="awdclr"></div></div>';
	}

	function fetchTooltip($label, $description, &$node, $control_name, $name){
		// Output
		return NULL;
	}

}
