<?php
/**
* @version $Id: cbpaidsubscriptions.install.php 1610 2013-01-09 23:29:17Z brunner $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Installs CBSubs, final part (ECHOs banners)
 *
 * @return void
 */
function plug_cbpaidsubscriptions_install(){
	global $_CB_framework, $_CB_database;
	
	$installedVersion	=	'4.0.0-rc.1';		//CBSUBS_VERSION_AUTOMATICALLY_SET_DO_NOT_EDIT!!!
	
?>
<div style="width:100%;text-align:center">

	<div>
		<img alt="CBSubs Logo" height="300" width="300" src="<?php echo $_CB_framework->getCfg( 'live_site' ); ?>/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/icons/normal/cbsubs_logo_300.jpg" />
	</div>
	<h1>CBSubs <?php echo $installedVersion; ?></h1>
    <p><strong>Copyright &copy; 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved</strong></p>
    <p><strong>CBSubs is a Trademark of Lightning MultiCom SA, Switzerland, and its licensors. CB, Community Builder and Joomlapolis are also a trademark of joomlapolis.com and may not be used without permission from the trademark holders.</strong></p>
    <p>The copyright holders have spent massive time on this software and are continuing to improve it. A corresponding membership at Joomlapolis.com is required for ongoing maintenance and support.</p>
    <p>All copyright statements must be kept.</p> 
    <p><em>Official site: <a href="http://www.joomlapolis.com?pk_campaign=in-cb&amp;pk_kwd=installed-cbsubs">www.joomlapolis.com</a></em></p>
		
</div>

<?php 

	// Temporary fix in CBSubs 1.2.2 only for CBSubs 1.2.1 bug:

	if ( ( checkJversion() >= 1 ) && ( $installedVersion == '1.2.2' ) ) {

		$sql	=	"UPDATE #__users"
				.	" SET password = concat(md5(concat(password,'12345678901234567890123456789012')),':','12345678901234567890123456789012')"
				.	" WHERE ( LENGTH(password) < 65 )"
				.	" AND ( LENGTH(password) > 3 )"
				.	" AND ( registerDate > '2011-07-13 12:00:00' )";
		$_CB_database->setQuery( $sql );
		$_CB_database->query();
	}

	// Temporary fix in CBSubs 1.3.0 for CBSubs 1.2.x bug:

	// Ogone gateway was not transfering address from basket:
	$sql		=	'UPDATE #__cbsubs_payments p'
				.	' LEFT JOIN #__cbsubs_payment_baskets b ON p.payment_basket_id = b.id'
				.	' SET '
				.	' p.`address_street` = b.`address_street`,'
				.	' p.`address_city` = b.`address_city`,'
				.	' p.`address_state` = b.`address_state`,'
				.	' p.`address_zip` = b.`address_zip`,'
				.	' p.`address_country` = b.`address_country`,'
				.	' p.`address_country_code` = b.`address_country_code`,'
				.	' p.`payer_business_name` = b.`payer_business_name`,'
				.	' p.`payer_email` = b.`payer_email`,'
				.	' p.`contact_phone` = b.`contact_phone`,'
				.	' p.`vat_number` = b.`vat_number`'
				.	" WHERE p.payment_method IN ('ogone','swisspostfinance')"
				.	' AND ISNULL(p.`address_country_code`);';
	$_CB_database->setQuery( $sql );
	$_CB_database->query();

	cbimport( 'cb.adminfilesystem' );
	$adminFS	=	cbAdminFileSystem::getInstance();
	$cbsubsDir	=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions';

	// Remove old 2Checkout files if there is no 2Checkout gateway configured, as TwoCheckout is not part anymore in CBSubs 1.3:
	$sql		=	'SELECT COUNT(*) FROM #__cbsubs_gateway_accounts'
				.	' WHERE gateway_type = "processors.twocheckout";';
	$_CB_database->setQuery( $sql );
	$hasTwoCheckoutInstalled	=	$_CB_database->loadResult();
	$twocheckoutdir				=	$cbsubsDir . '/processors/twocheckout';
	if ( ( $hasTwoCheckoutInstalled == 0 ) && file_exists( $twocheckoutdir ) ) {
		$adminFS->deldir( $twocheckoutdir . '/' );
	}

	// Remove old 1.x and 2.x files:
	$oldfiles	=	array(	'admin.cbpaidsubscriptions.ctrl.php',
							'cbpaidsubscriptions.condition.php',
							'cbpaidsubscriptions.countries.php',
							'cbpaidsubscriptions.crosstotalizer.php',
							'cbpaidsubscriptions.ctrl.php',
							'cbpaidsubscriptions.currency.php',
							'cbpaidsubscriptions.gui.php',
							'cbpaidsubscriptions.guisubs.php',
							'cbpaidsubscriptions.importer.php',
							'cbpaidsubscriptions.scheduler.php',
							'cbpaidsubscriptions.sql.php',
							'cbpaidsubscriptions.userparams.php'
						 );
	foreach( $oldfiles as $file ) {
		$pathFile	=	$cbsubsDir . '/' . $file;
		if ( $file && file_exists( $pathFile ) ) {
			$adminFS->unlink( $pathFile );
		}
	}

}
