<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment Amazon
 * @version $Id: helper.php 8316 2014-09-22 15:24:16Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 */
abstract class amazonHelper {
	var $amazonData = null;
	var $plugin = null;
	var $_currentMethod = null;

	public function __construct ($amazonData, $method) {
		$this->amazonData = $amazonData;
		$this->_currentMethod = $method;
	}

	function getAmazonResponseState ($status) {
		$amazonResponseState = new stdClass();

		if ($status->isSetState()) {
			$amazonResponseState->amazon_response_state = $status->getState();
		}
		if ($status->isSetReasonCode()) {
			$amazonResponseState->amazon_response_reasonCode = $status->getReasonCode();
		}
		if ($status->isSetReasonDescription()) {
			$amazonResponseState->amazon_response_reasonDescription = $status->getReasonDescription();
		}


		return $amazonResponseState;
	}

	function getVmReferenceId ($referenceId) {
		$pos = strrpos($referenceId, '-');
		if ($pos === false) {
			return $referenceId;
		} else {
			return substr($referenceId, 0, $pos);
		}

	}

	public function onNotificationNextOperation ($order, $payments, $amazonState) {
		return false;
	}

	protected abstract function getContents ();

	function tableStart ($title) {
		$contents = '<table class="adminlist table">';
		$contents .= '	<tr><th colspan="3">';
		$contents .= $title;
		$contents .= '</th></tr>';
		return $contents;
	}

	function tableEnd () {
		$contents = '</table>';
		return $contents;
	}

	function getRow ($title, $value) {
		$contents = '<tr><td></td><td>';

		$contents .= $title;
		$contents .= '</td><td>';

		$contents .= $value;
		$contents .= '</td></tr>';
		return $contents;
	}

	function getRowFirstCol ($title) {
		$contents = '<tr><td colspan="3">';
		$contents .= $title;
		$contents .= '</td><tr>';

		return $contents;
	}


	public function getContentsResponseMetadata ($responseMetadata) {
		$contents = '';
		if ($responseMetadata->isSetRequestId()) {
			$contents .= '<tr><td>';
			$contents .= "RequestId: ";
			$contents .= '</td><td>';

			$contents .= $responseMetadata->getRequestId();
			$contents .= '</td><td>';
			$contents .= '</td><td>';
			$contents .= '</td></tr>';

		}
		return $contents;

	}

	public function getContentsResponseHeaderMetadata ($responseHeaderMetadata) {
		$contents = '';
		$contents .= '<tr><td>';
		$contents .= "ResponseHeaderMetadata: ";
		$contents .= '</td><td>';

		$contents .= $responseHeaderMetadata;
		$contents .= '</td><td>';
		$contents .= '</td><td>';
		$contents .= '</td></tr>';

		return $contents;

	}
}