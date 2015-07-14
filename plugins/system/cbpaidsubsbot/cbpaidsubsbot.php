<?php
/**
* CBSubs paidsubs mambot
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @Copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
* @version $Id: cbpaidsubsbot.php 1548 2012-12-03 09:32:47Z beat $
**/
if ( ! ( defined( '_VALID_MOS' ) or defined( '_JEXEC' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

if ( JFactory::getApplication()->getClientId() != 0 ) {
	// Execute only in front-end:
	return;
}

define( '_CBSUBS_BOT_VERSION', '4.0.0-rc.1' );		//CBSUBS_VERSION_AUTOMATICALLY_SET_DO_NOT_EDIT!!!

$CBSUBS_BOT_FILE	=	JPATH_SITE . DIRECTORY_SEPARATOR
	. 'components' . DIRECTORY_SEPARATOR
	. 'com_comprofiler' . DIRECTORY_SEPARATOR
	. 'plugin' . DIRECTORY_SEPARATOR
	. 'user' . DIRECTORY_SEPARATOR
	. 'plug_cbpaidsubscriptions' . DIRECTORY_SEPARATOR
	. 'cbpaidsubscriptions.sysplug.php';

// check if file exists and is readable, to avoid any issue if it's not:
if ( is_readable( $CBSUBS_BOT_FILE ) ) {
	// executes system-independant CBSubs startup file:
	/** @noinspection PhpIncludeInspection */
	include_once $CBSUBS_BOT_FILE;
}
