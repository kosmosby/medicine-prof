<?php
/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: getrealex.php 8384 2014-10-07 14:56:06Z alatak $
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
defined('JPATH_BASE') or die();

/**
 * Renders a label element
 */


class JElementGetRealex extends JElement {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'getRealex';

	function fetchElement ($name, $value, &$node, $control_name) {
		$doc = JFactory::getDocument();
		$doc->addScript(JURI::root(true) . '/plugins/vmpayment/realex_hpp_api/realex_hpp_api/assets/js/admin.js');
		$doc->addStyleSheet(JURI::root(true) . '/plugins/vmpayment/realex_hpp_api/realex_hpp_api/assets/css/admin.css');

		$url = "http://www.realexpayments.com/partner-referral?id=virtuemart";
		$logo = '<img src="http://www.realexpayments.com/images/logo_realex_large.png" width="150"/>';
		$html = '<p><a target="_blank" href="' . $url . '"  >' . $logo . '</a></p>';
		$html .= '<p><a target="_blank" href="' . $url . '" class="signin-button-link">' . vmText::_('VMPAYMENT_REALEX_HPP_API_REGISTER') . '</a>';
		$html .= ' <a target="_blank" href="http://docs.virtuemart.net/manual/shop-menu/payment-methods/realex-hpp-and-api.html" class="signin-button-link">' . vmText::_('VMPAYMENT_REALEX_HPP_API_DOCUMENTATION') . '</a></p>';

		return $html;
	}

}