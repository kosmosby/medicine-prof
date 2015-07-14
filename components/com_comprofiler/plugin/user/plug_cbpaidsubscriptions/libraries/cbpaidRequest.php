<?php
/**
 * @version $Id: cbpaidRequest.php 1551 2012-12-03 10:52:03Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Request handling class
 * For now, only for IP addresses
 */
class cbpaidRequest {
	/**
	 * Gets an array of IP addresses taking in account the proxys on the way.
	 * An array is needed because FORWARDED_FOR can be facked as well.
	 *
	 * @return array of IP addresses, first one being host, and last one last proxy (except fackings)
	 */
	public static function getIParray() {
		global $_SERVER;

		$ip_adr_array = array();
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'],',')) {
				$ip_adr_array +=  explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
			} else {
				$ip_adr_array[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}
		$ip_adr_array[] = $_SERVER['REMOTE_ADDR'];
		return $ip_adr_array;
	}
	/**
	 * Gets a comma-separated list of IP addresses taking in account the proxys on the way.
	 * An array is needed because FORWARDED_FOR can be facked as well.
	 *
	 * @return string of IP addresses, first one being host, and last one last proxy (except fackings)
	 */
	public static function getIPlist() {
		return addslashes(implode(",", self::getIParray()));
	}
}
