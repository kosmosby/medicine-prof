<?php
/**
 *
 * Amazon payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: ipnurl.php 8421 2014-10-13 18:01:34Z alatak $
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

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a label element
 */

class JElementIpnURL extends JElement {
	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'ipnurl';

	function fetchElement ($name, $value, &$node, $control_name) {
		$cid = vRequest::getvar('cid', NULL, 'array');
		if (is_Array($cid)) {
			$virtuemart_paymentmethod_id = $cid[0];
		} else {
			$virtuemart_paymentmethod_id = $cid;
		}

		$http = JURI::root() . 'index.php?option=com_virtuemart&view=vmplg&task=notify&nt=ipn&tmpl=component&pm=' . $virtuemart_paymentmethod_id;

		$https = str_replace('http://', 'https://', $http);

		$class = ($node->attributes('class') ? 'class="' . $node->attributes('class') . '"' : 'class="text_area"');
		if ($node->attributes('editable') == 'true') {
			$size = ($node->attributes('size') ? 'size="' . $node->attributes('size') . '"' : '');

			return '<input type="text" name="' . $control_name . '[' . $name . ']" id="' . $control_name . $name . '" value="' . $value . '" ' . $class . ' ' . $size . ' />';
		} else {
			$string = "<div " . $class . ">";
			$string .= '<div class="ipn-sandbox">' . $http . ' <br /></div>';
			if (strcmp($https,$http) !==0){
				$string .= '<div class="ipn-sandbox">' . vmText::_('VMPAYMENT_AMAZON_OR') . '<br /></div>';
				$string .= $https;
				$string .= "</div>";
			}

			return $string;
		}
	}
}