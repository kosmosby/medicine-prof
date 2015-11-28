<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Registry\Registry;
use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;
use CB\Plugin\GroupJive\CBGroupJive;
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJive\Table\CategoryTable;
use CB\Plugin\GroupJive\Table\NotificationTable;
use CB\Plugin\GroupJiveFile\Table\FileTable;
use CB\Plugin\GroupJiveFile\CBGroupJiveFile;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );

$_PLUGINS->registerFunction( 'activity_onQueryActivity', 'activityQuery', 'cbgjFilePlugin' );
$_PLUGINS->registerFunction( 'activity_onBeforeDisplayActivity', 'activityPrefetch', 'cbgjFilePlugin' );
$_PLUGINS->registerFunction( 'activity_onDisplayActivity', 'activityDisplay', 'cbgjFilePlugin' );
$_PLUGINS->registerFunction( 'gj_onAdminMenu', 'adminMenu', 'cbgjFilePlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterDeleteCategory', 'deleteCategory', 'cbgjFilePlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterDeleteGroup', 'deleteGroup', 'cbgjFilePlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterCreateUser', 'storeNotifications', 'cbgjFilePlugin' );
$_PLUGINS->registerFunction( 'gj_onCanCreateGroupContent', 'canCreate', 'cbgjFilePlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayGroupEdit', 'editGroup', 'cbgjFilePlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayNotifications', 'editNotifications', 'cbgjFilePlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayGroup', 'showFiles', 'cbgjFilePlugin' );

class cbgjFilePlugin extends cbPluginHandler
{
	/** @var PluginTable  */
	public $_gjPlugin	=	null;
	/** @var Registry  */
	public $_gjParams	=	null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $_PLUGINS;

		if ( ! $this->_gjPlugin ) {
			$this->_gjPlugin	=	$_PLUGINS->getLoadedPlugin( 'user', 'cbgroupjive' );
			$this->_gjParams	=	$_PLUGINS->getPluginParams( $this->_gjPlugin );
		}
	}

	/**
	 * @param bool                        $count
	 * @param array                       $select
	 * @param array                       $where
	 * @param array                       $join
	 * @param CB\Plugin\Activity\Activity $stream
	 */
	public function activityQuery( $count, &$select, &$where, &$join, &$stream )
	{
		global $_CB_database;

		$join[]				=	'LEFT JOIN ' . $_CB_database->NameQuote( '#__groupjive_plugin_file' ) . ' AS gj_f'
							.	' ON a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' = ' . $_CB_database->Quote( 'group.file' )
							.	' AND a.' . $_CB_database->NameQuote( 'item' ) . ' = gj_f.' . $_CB_database->NameQuote( 'id' );

		if ( ! CBGroupJive::isModerator() ) {
			$user			=	CBuser::getMyUserDataInstance();

			$where[]		=	'( ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' = ' . $_CB_database->Quote( 'group.file' )
							.	' AND gj_f.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL'
							.	' AND ( gj_f.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
							.		' OR ( gj_f.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
							.		' AND ( gj_g.' . $_CB_database->NameQuote( 'type' ) . ' IN ( 1, 2 )'
							.		' OR gj_u.' . $_CB_database->NameQuote( 'status' ) . ' > 0 ) ) ) )'
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' != ' . $_CB_database->Quote( 'groupjive' )
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' != ' . $_CB_database->Quote( 'group.file' ) . ' ) ) )';
		} else {
			$where[]		=	'( ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' = ' . $_CB_database->Quote( 'group.file' )
							.	' AND gj_f.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL )'
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' != ' . $_CB_database->Quote( 'groupjive' )
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' != ' . $_CB_database->Quote( 'group.file' ) . ' ) ) )';
		}
	}

	/**
	 * @param string                                   $return
	 * @param CB\Plugin\Activity\Table\ActivityTable[] $rows
	 * @param CB\Plugin\Activity\Activity              $stream
	 * @param int                                      $output 0: Normal, 1: Raw, 2: Inline, 3: Load, 4: Save
	 */
	public function activityPrefetch( &$return, &$rows, $stream, $output )
	{
		global $_CB_database;

		$fileIds				=	array();

		foreach ( $rows as $row ) {
			if ( ! ( ( $row->get( 'type' ) == 'groupjive' ) && ( $row->get( 'subtype' ) == 'group.file' ) ) ) {
				continue;
			}

			$fileId				=	(int) $row->get( 'item' );

			if ( $fileId && ( ! in_array( $fileId, $fileIds ) ) ) {
				$fileIds[]		=	$fileId;
			}
		}

		if ( ! $fileIds ) {
			return;
		}

		$query					=	'SELECT f.*'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_file' ) . " AS f"
								.	"\n WHERE f." . $_CB_database->NameQuote( 'id' ) . " IN " . $_CB_database->safeArrayOfIntegers( $fileIds );
		$_CB_database->setQuery( $query );
		$files					=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJiveFile\Table\FileTable', array( $_CB_database ) );

		if ( ! $files ) {
			return;
		}

		CBGroupJiveFile::getFile( $files );
		CBGroupJive::preFetchUsers( $files );
	}

	/**
	 * @param CB\Plugin\Activity\Table\ActivityTable $row
	 * @param null|string                            $title
	 * @param null|string                            $date
	 * @param null|string                            $message
	 * @param null|string                            $insert
	 * @param null|string                            $footer
	 * @param array                                  $menu
	 * @param array                                  $extras
	 * @param CB\Plugin\Activity\Activity            $stream
	 * @param int                                    $output 0: Normal, 1: Raw, 2: Inline, 3: Load, 4: Save
	 */
	public function activityDisplay( &$row, &$title, &$date, &$message, &$insert, &$footer, &$menu, &$extras, $stream, $output )
	{
		if ( ! ( ( $row->get( 'type' ) == 'groupjive' ) && ( $row->get( 'subtype' ) == 'group.file' ) ) ) {
			return;
		}

		$file		=	CBGroupJiveFile::getFile( (int) $row->get( 'item' ) );

		if ( ! $file->get( 'id' ) ) {
			return;
		}

		CBGroupJive::getTemplate( 'activity', true, true, $this->element );

		$insert		=	HTML_groupjiveFileActivity::showFileActivity( $row, $title, $message, $stream, $file, $this );
	}

	/**
	 * Displays backend menu items
	 *
	 * @param array $menu
	 */
	public function adminMenu( &$menu )
	{
		global $_CB_framework;

		$menu[]		=	array(	'title' => CBTxt::T( 'Files' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'action' => 'showgjfiles', 'cid' => $this->getPluginId() ) ), 'icon' => 'cbgj-files',
								'submenu' => array( array( 'title' => CBTxt::Th( 'Add New File to Group' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'cid' => $this->getPluginId(), 'table' => 'gjfilesbrowser', 'action' => 'editrow' ) ), 'icon' => 'cb-new' ) )
							);
	}

	/**
	 * delete the local files and the directory when a category is deleted
	 *
	 * @param CategoryTable $category
	 */
	public function deleteCategory( $category )
	{
		global $_CB_framework;

		CBGroupJive::deleteDirectory( $_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/cbgroupjivefile/' . (int) $category->get( 'id' ) );
	}

	/**
	 * delete all the files for the group that was deleted
	 *
	 * @param GroupTable $group
	 */
	public function deleteGroup( $group )
	{
		global $_CB_framework, $_CB_database;

		$query			=	'SELECT *'
						.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_file' )
						.	"\n WHERE " . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' );
		$_CB_database->setQuery( $query );
		$files			=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJiveFile\Table\FileTable', array( $_CB_database ) );

		/** @var FileTable[] $files */
		foreach ( $files as $file ) {
			$file->delete();
		}

		CBGroupJive::deleteDirectory( $_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/cbgroupjivefile/' . (int) $group->get( 'category' ) . '/' . (int) $group->get( 'id' ) );
	}

	/**
	 * store default notifications
	 *
	 * @param \CB\Plugin\GroupJive\Table\UserTable $row
	 * @param Registry                             $notifications
	 */
	public function storeNotifications( $row, &$notifications )
	{
		$notifications->set( 'file_new', $this->params->get( 'notifications_default_file_new', 0 ) );
		$notifications->set( 'file_approve', $this->params->get( 'notifications_default_file_approve', 0 ) );
	}

	/**
	 * check file create limit
	 *
	 * @param bool       $access
	 * @param string     $param
	 * @param GroupTable $group
	 * @param UserTable  $user
	 */
	public function canCreate( &$access, $param, $group, $user )
	{
		global $_CB_database;

		if ( $param == 'file' ) {
			$createLimit				=	(int) $this->params->get( 'groups_file_create_limit', 0 );

			if ( $createLimit ) {
				static $count			=	array();

				$countId				=	md5( $user->get( 'id' ) . $group->get( 'id' ) );

				if ( ! isset( $count[$countId] ) ) {
					$query				=	'SELECT COUNT(*)'
										.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_file' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
										.	"\n AND " . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' );
					$_CB_database->setQuery( $query );
					$count[$countId]	=	(int) $_CB_database->loadResult();
				}

				if ( $count[$countId] >= $createLimit ) {
					$access				=	false;
				}
			}
		}
	}

	/**
	 * render frontend file group edit params
	 *
	 * @param string        $return
	 * @param GroupTable    $row
	 * @param array         $input
	 * @param CategoryTable $category
	 * @param UserTable     $user
	 * @return string
	 */
	public function editGroup( &$return, &$row, &$input, $category, $user )
	{
		CBGroupJive::getTemplate( 'group_edit', true, true, $this->element );

		$listEnable			=	array();
		$listEnable[]		=	moscomprofilerHTML::makeOption( 0, CBTxt::T( 'Disable' ) );
		$listEnable[]		=	moscomprofilerHTML::makeOption( 1, CBTxt::T( 'Enable' ) );
		$listEnable[]		=	moscomprofilerHTML::makeOption( 2, CBTxt::T( 'Enable, with Approval' ) );

		$enableTooltip		=	cbTooltip( null, CBTxt::T( 'Optionally enable or disable usage of files. Group owner and group administrators are exempt from this configuration and can always upload files. Note existing files will still be accessible.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['file']		=	moscomprofilerHTML::selectList( $listEnable, 'params[file]', 'class="form-control"' . $enableTooltip, 'value', 'text', (int) $this->input( 'post/params.file', $row->params()->get( 'file', 1 ), GetterInterface::INT ), 1, false, false );

		return HTML_groupjiveFileParams::showFileParams( $row, $input, $category, $user, $this );
	}

	/**
	 * render frontend file group notifications edit params
	 *
	 * @param string            $return
	 * @param NotificationTable $row
	 * @param array             $input
	 * @param GroupTable        $group
	 * @param UserTable         $user
	 * @return string
	 */
	public function editNotifications( &$return, &$row, &$input, $group, $user )
	{
		CBGroupJive::getTemplate( 'notifications', true, true, $this->element );

		$listToggle					=	array();
		$listToggle[]				=	moscomprofilerHTML::makeOption( '0', CBTxt::T( "Don't Notify" ) );
		$listToggle[]				=	moscomprofilerHTML::makeOption( '1', CBTxt::T( 'Notify' ) );

		$input['file_new']			=	moscomprofilerHTML::yesnoSelectList( 'params[file_new]', 'class="form-control"', (int) $this->input( 'post/params.file_new', $row->params()->get( 'file_new', $this->params->get( 'notifications_default_file_new', 0 ) ), GetterInterface::INT ), CBTxt::T( 'Notify' ), CBTxt::T( "Don't Notify" ), false );
		$input['file_approve']		=	moscomprofilerHTML::yesnoSelectList( 'params[file_approve]', 'class="form-control"', (int) $this->input( 'post/params.file_approve', $row->params()->get( 'file_approve', $this->params->get( 'notifications_default_file_approve', 0 ) ), GetterInterface::INT ), CBTxt::T( 'Notify' ), CBTxt::T( "Don't Notify" ), false );

		return HTML_groupjiveFileNotifications::showFileNotifications( $row, $input, $group, $user, $this );
	}

	/**
	 * prepare frontend files render
	 *
	 * @param string     $return
	 * @param GroupTable $group
	 * @param string     $users
	 * @param string     $invites
	 * @param array      $counters
	 * @param array      $buttons
	 * @param array      $menu
	 * @param cbTabs     $tabs
	 * @param UserTable  $user
	 * @return array|null
	 */
	public function showFiles( &$return, &$group, &$users, &$invites, &$counters, &$buttons, &$menu, &$tabs, $user )
	{
		global $_CB_framework, $_CB_database;

		CBGroupJive::getTemplate( 'files', true, true, $this->element );

		$canModerate			=	( CBGroupJive::isModerator( $user->get( 'id' ) ) || ( CBGroupJive::getGroupStatus( $user, $group ) >= 2 ) );
		$limit					=	(int) $this->params->get( 'groups_file_limit', 15 );
		$limitstart				=	$_CB_framework->getUserStateFromRequest( 'gj_group_file_limitstart{com_comprofiler}', 'gj_group_file_limitstart' );
		$search					=	$_CB_framework->getUserStateFromRequest( 'gj_group_file_search{com_comprofiler}', 'gj_group_file_search' );
		$where					=	null;

		if ( $search && $this->params->get( 'groups_file_search', 1 ) ) {
			$where				.=	"\n AND ( f." . $_CB_database->NameQuote( 'title' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
								.	" OR f." . $_CB_database->NameQuote( 'filename' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
								.	" OR f." . $_CB_database->NameQuote( 'description' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false ) . " )";
		}

		$searching				=	( $where ? true : false );

		$query					=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_file' ) . " AS f"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS cb"
								.	' ON cb.' . $_CB_database->NameQuote( 'id' ) . ' = f.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS j"
								.	' ON j.' . $_CB_database->NameQuote( 'id' ) . ' = cb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE f." . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' )
								.	"\n AND cb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND cb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND j." . $_CB_database->NameQuote( 'block' ) . " = 0";

		if ( ! $canModerate ) {
			$query				.=	"\n AND ( f." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
								.		' OR f.' . $_CB_database->NameQuote( 'published' ) . ' = 1 )';
		}

		$query					.=	$where;
		$_CB_database->setQuery( $query );
		$total					=	(int) $_CB_database->loadResult();

		if ( ( ! $total ) && ( ! $searching ) && ( ! CBGroupJive::canCreateGroupContent( $user, $group, 'file' ) ) ) {
			return null;
		}

		$pageNav				=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( 'gj_group_file_' );

		switch( (int) $this->params->get( 'groups_file_orderby', 2 ) ) {
			case 1:
				$orderBy		=	'f.' . $_CB_database->NameQuote( 'date' ) . ' ASC';
				break;
			case 3:
				$orderBy		=	'f.' . $_CB_database->NameQuote( 'filename' ) . ' ASC';
				break;
			case 4:
				$orderBy		=	'f.' . $_CB_database->NameQuote( 'filename' ) . ' DESC';
				break;
			case 2:
			default:
				$orderBy		=	'f.' . $_CB_database->NameQuote( 'date' ) . ' DESC';
				break;
		}

		$query					=	'SELECT f.*'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_file' ) . " AS f"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS cb"
								.	' ON cb.' . $_CB_database->NameQuote( 'id' ) . ' = f.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS j"
								.	' ON j.' . $_CB_database->NameQuote( 'id' ) . ' = cb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE f." . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' )
								.	"\n AND cb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND cb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND j." . $_CB_database->NameQuote( 'block' ) . " = 0";

		if ( ! $canModerate ) {
			$query				.=	"\n AND ( f." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
								.		' OR f.' . $_CB_database->NameQuote( 'published' ) . ' = 1 )';
		}

		$query					.=	$where
								.	"\n ORDER BY " . $orderBy;
		if ( $this->params->get( 'groups_file_paging', 1 ) ) {
			$_CB_database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
		} else {
			$_CB_database->setQuery( $query );
		}
		$rows					=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJiveFile\Table\FileTable', array( $_CB_database ) );

		$input					=	array();

		$input['search']		=	'<input type="text" name="gj_group_file_search" value="' . htmlspecialchars( $search ) . '" onchange="document.gjGroupFileForm.submit();" placeholder="' . htmlspecialchars( CBTxt::T( 'Search Files...' ) ) . '" class="form-control" />';

		CBGroupJiveFile::getFile( $rows );
		CBGroupJive::preFetchUsers( $rows );

		$group->set( '_files', $pageNav->total );

		return array(	'id'		=>	'file',
						'title'		=>	CBTxt::T( 'Files' ),
						'content'	=>	HTML_groupjiveFile::showFiles( $rows, $pageNav, $searching, $input, $counters, $group, $user, $this )
					);
	}
}