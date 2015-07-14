<?php
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CB_framework, $mainframe;

if ( defined( 'JPATH_ADMINISTRATOR' ) ) {
	if ( ! file_exists( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) {
		return;
	}

	require_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
} else {
	if ( ! file_exists( $mainframe->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/plugin.foundation.php' ) ) {
		return;
	}

	require_once( $mainframe->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/plugin.foundation.php' );
}

cbimport( 'cb.html' );

if ( ! file_exists( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbactivity/cbactivity.class.php' ) ) {
	return;
}

require_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbactivity/cbactivity.class.php' );

$plugin						=	cbactivityClass::getPlugin();

if ( ! $plugin ) {
	return;
}

$exclude					=	$plugin->params->get( 'general_exclude', null );
$display					=	(int) $params->get( 'activity_display', 1 );
$avatar						=	(int) $params->get( 'activity_avatar', 0 );
$cutOff						=	(int) $params->get( 'activity_cut_off', 5 );
$limit						=	(int) $params->get( 'activity_limit', 10 );
$titleLimit					=	(int) $params->get( 'activity_title_length', 100 );
$descLimit					=	(int) $params->get( 'activity_desc_length', 100 );
$imgThumbnails				=	(int) $params->get( 'activity_img_thumbnails', 1 );
$user						=	CBuser::getUserDataInstance( $_CB_framework->myId() );
$now						=	$_CB_framework->getUTCNow();

outputCbJs( 1 );
outputCbTemplate( 1 );

cbactivityClass::getTemplate( array( 'module', 'jquery', 'activity' ) );
HTML_cbactivityJquery::loadJquery( 'module', $user, $plugin );

switch( $display ) {
	case 2: // Connections Only
		$where				=	array( 'b.referenceid', '=', (int) $user->get( 'id' ), 'b.accepted', '=', 1, 'b.pending', '=', 0 );
		break;
	case 3: // Self Only
		$where				=	array( 'user_id', '=', (int) $user->get( 'id' ) );
		break;
	case 4: // Connections and Self
		$where				=	array( 'user_id', '=', (int) $user->get( 'id' ), array( 'b.referenceid', '=', (int) $user->get( 'id' ), 'b.accepted', '=', 1, 'b.pending', '=', 0 ) );
		break;
	default: // Everyone
		$where				=	array();
		break;
}

switch( $cutOff ) {
	case 2: // 1 Day
		$cutOffTimestamp	=	$_CB_framework->getUTCTimestamp( '-1 DAY', $now );
		break;
	case 3: // 1 Week (7 Days)
		$cutOffTimestamp	=	$_CB_framework->getUTCTimestamp( '-1 WEEK', $now );
		break;
	case 4: // 2 Weeks
		$cutOffTimestamp	=	$_CB_framework->getUTCTimestamp( '-2 WEEK', $now );
		break;
	case 5: // 1 Month (30 Days)
		$cutOffTimestamp	=	$_CB_framework->getUTCTimestamp( '-1 MONTH', $now );
		break;
	case 6: // 3 Months
		$cutOffTimestamp	=	$_CB_framework->getUTCTimestamp( '-3 MONTH', $now );
		break;
	case 7: // 6 Months
		$cutOffTimestamp	=	$_CB_framework->getUTCTimestamp( '-6 MONTH', $now );
		break;
	case 8: // 1 Year (365 Days)
		$cutOffTimestamp	=	$_CB_framework->getUTCTimestamp( '-1 YEAR', $now );
		break;
	default: // No Limit
		$cutOffTimestamp	=	false;
		break;
}

if ( $cutOffTimestamp ) {
	array_unshift( $where, 'date', '>=', $_CB_framework->getUTCDate( 'Y-m-d H:i:s', $cutOffTimestamp ) );
}

if ( $exclude ) {
	$exclude				=	explode( ',', $exclude );

	cbArrayToInts( $exclude );

	if ( $exclude ) {
		array_unshift( $where, 'user_id', '!IN', $exclude );
	}
}

$rows						=	cbactivityData::getActivity( $where, null, $limit );

if ( $rows ) {
	echo HTML_cbactivityModule::showActivityModule( $rows, $avatar, $titleLimit, $descLimit, $imgThumbnails, $user, $plugin );
}
?>