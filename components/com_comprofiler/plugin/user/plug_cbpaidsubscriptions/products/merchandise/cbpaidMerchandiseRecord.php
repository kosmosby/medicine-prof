<?php
/**
 * @version $Id: cbpaidMerchandiseRecord.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Merchandises database table class
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 */
class cbpaidMerchandiseRecord extends cbpaidNonRecurringSomething {
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db  A database connector object
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_merchandises', 'id', $db );
	}
	/**
	 * Returns the human name of the record (not translated)
	 *
	 * @return string
	 */
	public function recordName( ) {
		return 'Merchandise';
	}
	/**
	 * Returns subscription part of article number
	 *
	 * @return string   'Sxxxx' where xxxx is the subscription id.
	 */
	public function getArtNoSubId( ) {
		return 'M' . $this->id;
	}
}
