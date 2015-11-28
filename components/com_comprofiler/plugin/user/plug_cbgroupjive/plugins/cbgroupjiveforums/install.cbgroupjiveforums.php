<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\PluginTable;
use CBLib\Registry\Registry;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

function plug_cbgroupjiveforums_install()
{
	// Grab GJ params to migrate the legacy params:
	$plugin				=	new PluginTable();

	$plugin->load( array( 'element' => 'cbgroupjive' ) );

	$pluginParams		=	new Registry( $plugin->get( 'params' ) );

	if ( ( ! $pluginParams->has( 'forum_id' ) ) || ( $pluginParams->get( 'forum_id' ) == null ) ) {
		return;
	}

	// Migrate forums integration parameters:
	$forums				=	new PluginTable();

	$forums->load( array( 'element' => 'cbgroupjiveforums' ) );

	$forumsParams		=	new Registry( $forums->get( 'params' ) );

	if ( $forumsParams->get( 'migrated' ) ) {
		return;
	}

	$forumsParams->set( 'groups_forums_category', $pluginParams->get( 'forum_id' ) );
	$forumsParams->set( 'groups_forums_paging', $pluginParams->get( 'forum_paging' ) );
	$forumsParams->set( 'groups_forums_limit', $pluginParams->get( 'forum_limit' ) );
	$forumsParams->set( 'groups_forums_search', $pluginParams->get( 'forum_search' ) );
	$forumsParams->set( 'migrated', true );

	$forums->set( 'params', $forumsParams->asJson() );

	$forums->store();
}