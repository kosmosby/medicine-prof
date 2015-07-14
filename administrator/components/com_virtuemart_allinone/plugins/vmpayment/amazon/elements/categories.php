<?php
defined('_JEXEC') or die();

/**
 *
 * @package    VirtueMart
 * @subpackage Plugins  - Elements
 * @author Valérie Isaksen
 * @version $Id: categories.php 8229 2014-08-23 16:56:12Z alatak $
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
if (!class_exists('VmConfig')) {
	require(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
}

if (!class_exists('ShopFunctions')) {
	require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
}
if (!class_exists('TableCategories')) {
	require(JPATH_VM_ADMINISTRATOR . DS . 'tables' . DS . 'categories.php');
}

if (!class_exists('VmElements')) {
	require(JPATH_VM_ADMINISTRATOR . DS . 'elements' . DS . 'vmelements.php');
}
/*
 * This element is used by the menu manager
 * Should be that way
 */
class JElementcategories extends JElement {

	var $_name = 'categories';


	function fetchElement ($name, $value, &$node, $control_name) {
		JPlugin::loadLanguage('com_virtuemart', JPATH_ADMINISTRATOR);
		$categorylist = ShopFunctions::categoryListTree(array($value));
		$class = ($node->attributes('class') ? 'class="' . $node->attributes('class') . '"' : '');

		$html = '<select multiple="true" class="inputbox ' . $class . '"   name="' . $control_name . '[' . $name . ']' . '" >';
		$html .= '<option value="0">' . vmText::_('COM_VIRTUEMART_NONE') . '</option>';
		$html .= $categorylist;
		$html .= "</select>";
		return $html;
	}

}

