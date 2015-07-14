<?php
/**
 * @version $Id: cbpaidUserWithSubsFields.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * This class is only for defining properly the fields added by CBSubs to the CB UserTable object for the invoicing address information
 */
class cbpaidUserWithSubsFields extends UserTable {
	public $cb_subs_inv_first_name;
	public $cb_subs_inv_last_name;
	public $cb_subs_inv_payer_business_name;
	public $cb_subs_inv_address_street;
	public $cb_subs_inv_address_city;
	public $cb_subs_inv_address_zip;
	public $cb_subs_inv_address_state;
	public $cb_subs_inv_address_country;
	public $cb_subs_inv_contact_phone;
	public $cb_subs_inv_vat_number;
}
