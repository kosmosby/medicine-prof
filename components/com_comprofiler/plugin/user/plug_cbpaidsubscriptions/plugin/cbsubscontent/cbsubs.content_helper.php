<?php
/**
* @version $Id: cbsubs.content_helper.php 1465 2012-07-10 17:37:13Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// In 1.6+, there is a bug in ModuleHelper class as _load is called but assigned without reference, so we need to redo a wrapper class to call the protected function:
if ( class_exists( 'JModuleHelper' ) && ( ! is_callable( array( 'JModuleHelper', '_load' ) ) ) ) {
	/**
	 * Module helper class to workaround buggy load function for 1.6+
	 */
	class cbpaidAccessModuleHelper extends JModuleHelper {
		/**
		 * Implements myLoad to overwrite load()
		 *
		 * @return array
		 */
		public static function &myLoad( ) {
			return JModuleHelper::_load();
		}
	}
}
