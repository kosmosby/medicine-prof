<?php
/**
 * @version $Id: cbpaidProductUndefined.php 1546 2012-12-02 23:16:25Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Non-abstract class used to create a new product
 * Used in cbpaidInstanciator::getObject(s)
 */
class cbpaidProductUndefined extends cbpaidProduct
{
	/**
	 * Creates a subscription object of the appropriate class to the product
	 *
	 * @return cbpaidSomething
	 */
	public function & newSubscription( ) {
		return null;
	}
}
