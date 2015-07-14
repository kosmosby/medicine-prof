<?php
defined('_JEXEC') or die('Restricted access');

/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: creditcards.php 8449 2014-10-15 15:30:50Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004-2014 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
/*
 * This class is used by VirtueMart Payment  Plugins
 * which uses JParameter
 * So It should be an extension of JElement
 * Those plugins cannot be configured througth the Plugin Manager anyway.
 */
if (!class_exists('VmConfig')) {
	require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
}
if (!class_exists('ShopFunctions')) {
	require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
}
if (!class_exists('RealexHelperRealex')) {
	require(JPATH_SITE . DS . 'plugins'.DS.'vmpayment'.DS.'realex_hpp_api'.DS.'realex_hpp_api'.DS.'helpers'.DS.'helper.php');
}
/**
 * @copyright    Copyright (C) 2009 Open Source Matters. All rights reserved.
 * @license    GNU/GPL
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a multiple item select element
 *
 */

class JElementCreditCards extends JElement {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */

	var $_name = 'creditcards';

	function fetchElement ($name, $value, &$node, $control_name) {
		JFactory::getLanguage()->load('plg_vmpayment_realex_hpp_api', JPATH_ADMINISTRATOR);

		$creditcards = RealexHelperRealex::getRealexCreditCards();

		$prefix = 'VMPAYMENT_REALEX_HPP_API_CC_';

		$fields = array();
		foreach ($creditcards as $creditcard) {
			$fields[$creditcard]['value'] = $creditcard;
			$fields[$creditcard]['text'] = vmText::_($prefix . strtoupper($fields[$creditcard]['value']));
		}

		$attribs = ' ';
		$attribs .= ' multiple="multiple"';
		$attribs .= ($node->attributes('class') ? ' class="' . $node->attributes('class') . '"' : '');


		return JHTML::_('select.genericlist', $fields, $control_name . '[' . $name . '][]', $attribs, 'value', 'text', $value, $control_name . $name);

	}

}