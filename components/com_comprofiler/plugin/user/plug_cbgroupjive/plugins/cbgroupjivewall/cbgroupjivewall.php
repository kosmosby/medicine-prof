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
use CB\Plugin\GroupJiveWall\Table\WallTable;
use CB\Plugin\GroupJiveWall\CBGroupJiveWall;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );

$_PLUGINS->registerFunction( 'activity_onQueryActivity', 'activityQuery', 'cbgjWallPlugin' );
$_PLUGINS->registerFunction( 'activity_onBeforeDisplayActivity', 'activityPrefetch', 'cbgjWallPlugin' );
$_PLUGINS->registerFunction( 'activity_onDisplayActivity', 'activityDisplay', 'cbgjWallPlugin' );
$_PLUGINS->registerFunction( 'gj_onAdminMenu', 'adminMenu', 'cbgjWallPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterDeleteGroup', 'deleteGroup', 'cbgjWallPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterCreateUser', 'storeNotifications', 'cbgjWallPlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayGroupEdit', 'editGroup', 'cbgjWallPlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayNotifications', 'editNotifications', 'cbgjWallPlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayGroup', 'showWall', 'cbgjWallPlugin' );

class cbgjWallPlugin extends cbPluginHandler
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

		$join[]				=	'LEFT JOIN ' . $_CB_database->NameQuote( '#__groupjive_plugin_wall' ) . ' AS gj_w'
							.	' ON a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' = ' . $_CB_database->Quote( 'group.wall' )
							.	' AND a.' . $_CB_database->NameQuote( 'item' ) . ' = gj_w.' . $_CB_database->NameQuote( 'id' );

		if ( ! CBGroupJive::isModerator() ) {
			$user			=	CBuser::getMyUserDataInstance();

			$where[]		=	'( ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' = ' . $_CB_database->Quote( 'group.wall' )
							.	' AND gj_w.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL'
							.	' AND ( gj_w.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
							.		' OR ( gj_w.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
							.		' AND ( gj_g.' . $_CB_database->NameQuote( 'type' ) . ' IN ( 1, 2 )'
							.		' OR gj_u.' . $_CB_database->NameQuote( 'status' ) . ' > 0 ) ) ) )'
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' != ' . $_CB_database->Quote( 'groupjive' )
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' != ' . $_CB_database->Quote( 'group.wall' ) . ' ) ) )';
		} else {
			$where[]		=	'( ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' = ' . $_CB_database->Quote( 'group.wall' )
							.	' AND gj_w.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL )'
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' != ' . $_CB_database->Quote( 'groupjive' )
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' != ' . $_CB_database->Quote( 'group.wall' ) . ' ) ) )';
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

		$postIds				=	array();

		foreach ( $rows as $row ) {
			if ( ! ( ( $row->get( 'type' ) == 'groupjive' ) && ( $row->get( 'subtype' ) == 'group.wall' ) ) ) {
				continue;
			}

			$postId				=	(int) $row->get( 'item' );

			if ( $postId && ( ! in_array( $postId, $postIds ) ) ) {
				$postIds[]		=	$postId;
			}
		}

		if ( ! $postIds ) {
			return;
		}

		$replies				=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_wall' ) . " AS r"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS rcb"
								.	' ON rcb.' . $_CB_database->NameQuote( 'id' ) . ' = r.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS rj"
								.	' ON rj.' . $_CB_database->NameQuote( 'id' ) . ' = rcb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE r." . $_CB_database->NameQuote( 'reply' ) . " = p." . $_CB_database->NameQuote( 'id' )
								.	"\n AND rcb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND rcb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND rj." . $_CB_database->NameQuote( 'block' ) . " = 0";

		$query					=	'SELECT p.*'
								.	', ( ' . $replies . ' ) AS _replies'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_wall' ) . " AS p"
								.	"\n WHERE p." . $_CB_database->NameQuote( 'id' ) . " IN " . $_CB_database->safeArrayOfIntegers( $postIds );
		$_CB_database->setQuery( $query );
		$posts					=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJiveWall\Table\WallTable', array( $_CB_database ) );

		if ( ! $posts ) {
			return;
		}

		CBGroupJiveWall::getPost( $posts );
		CBGroupJive::preFetchUsers( $posts );
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
		if ( ! ( ( $row->get( 'type' ) == 'groupjive' ) && ( $row->get( 'subtype' ) == 'group.wall' ) ) ) {
			return;
		}

		$post		=	CBGroupJiveWall::getPost( (int) $row->get( 'item' ) );

		if ( ! $post->get( 'id' ) ) {
			return;
		}

		CBGroupJive::getTemplate( 'activity', true, true, $this->element );

		$insert		=	HTML_groupjiveWallActivity::showWallActivity( $row, $title, $message, $stream, $post, $this );
	}

	/**
	 * Displays backend menu items
	 *
	 * @param array $menu
	 */
	public function adminMenu( &$menu )
	{
		global $_CB_framework;

		$menu[]		=	array(	'title' => CBTxt::T( 'Wall' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'action' => 'showgjwall', 'cid' => $this->getPluginId() ) ), 'icon' => 'cbgj-wall',
								'submenu' => array( array( 'title' => CBTxt::Th( 'Add New Post to Group' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'cid' => $this->getPluginId(), 'table' => 'gjwallbrowser', 'action' => 'editrow' ) ), 'icon' => 'cb-new' ) )
							);
	}

	/**
	 * delete all the posts for the group that was deleted
	 *
	 * @param GroupTable $group
	 */
	public function deleteGroup( $group )
	{
		global $_CB_database;

		$query			=	'SELECT *'
						.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_wall' )
						.	"\n WHERE " . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' );
		$_CB_database->setQuery( $query );
		$posts			=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJiveWall\Table\WallTable', array( $_CB_database ) );

		/** @var WallTable[] $posts */
		foreach ( $posts as $post ) {
			$post->delete();
		}
	}

	/**
	 * store default notifications
	 *
	 * @param \CB\Plugin\GroupJive\Table\UserTable $row
	 * @param Registry                             $notifications
	 */
	public function storeNotifications( $row, &$notifications )
	{
		$notifications->set( 'wall_new', $this->params->get( 'notifications_default_wall_new', 0 ) );
		$notifications->set( 'wall_approve', $this->params->get( 'notifications_default_wall_approve', 0 ) );
		$notifications->set( 'wall_reply', $this->params->get( 'notifications_default_wall_reply', 0 ) );
	}

	/**
	 * render frontend wall group edit params
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

		$enableTooltip		=	cbTooltip( null, CBTxt::T( 'Optionally enable or disable usage of the wall. Group owner and group administrators are exempt from this configuration and can always post. Note existing posts will still be accessible.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['wall']		=	moscomprofilerHTML::selectList( $listEnable, 'params[wall]', 'class="form-control"' . $enableTooltip, 'value', 'text', (int) $this->input( 'post/params.wall', $row->params()->get( 'wall', 1 ), GetterInterface::INT ), 1, false, false );

		return HTML_groupjiveWallParams::showWallParams( $row, $input, $category, $user, $this );
	}

	/**
	 * render frontend wall group notifications edit params
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

		$input['wall_new']			=	moscomprofilerHTML::yesnoSelectList( 'params[wall_new]', 'class="form-control"', (int) $this->input( 'post/params.wall_new', $row->params()->get( 'wall_new', $this->params->get( 'notifications_default_wall_new', 0 ) ), GetterInterface::INT ), CBTxt::T( 'Notify' ), CBTxt::T( "Don't Notify" ), false );
		$input['wall_approve']		=	moscomprofilerHTML::yesnoSelectList( 'params[wall_approve]', 'class="form-control"', (int) $this->input( 'post/params.wall_approve', $row->params()->get( 'wall_approve', $this->params->get( 'notifications_default_wall_approve', 0 ) ), GetterInterface::INT ), CBTxt::T( 'Notify' ), CBTxt::T( "Don't Notify" ), false );
		$input['wall_reply']		=	moscomprofilerHTML::yesnoSelectList( 'params[wall_reply]', 'class="form-control"', (int) $this->input( 'post/params.wall_reply', $row->params()->get( 'wall_reply', $this->params->get( 'notifications_default_wall_reply', 0 ) ), GetterInterface::INT ), CBTxt::T( 'Notify' ), CBTxt::T( "Don't Notify" ), false );

		return HTML_groupjiveWallNotifications::showWallNotifications( $row, $input, $group, $user, $this );
	}

	/**
	 * prepare frontend wall render
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
	public function showWall( &$return, &$group, &$users, &$invites, &$counters, &$buttons, &$menu, &$tabs, $user )
	{
		global $_CB_framework, $_CB_database;

		CBGroupJive::getTemplate( 'wall', true, true, $this->element );

		$canModerate			=	( CBGroupJive::isModerator( $user->get( 'id' ) ) || ( CBGroupJive::getGroupStatus( $user, $group ) >= 2 ) );
		$limit					=	(int) $this->params->get( 'groups_wall_limit', 15 );
		$limitstart				=	$_CB_framework->getUserStateFromRequest( 'gj_group_wall_limitstart{com_comprofiler}', 'gj_group_wall_limitstart' );

		$query					=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_wall' ) . " AS p"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS cb"
								.	' ON cb.' . $_CB_database->NameQuote( 'id' ) . ' = p.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS j"
								.	' ON j.' . $_CB_database->NameQuote( 'id' ) . ' = cb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE p." . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' )
								.	"\n AND p." . $_CB_database->NameQuote( 'reply' ) . " = 0"
								.	"\n AND cb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND cb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND j." . $_CB_database->NameQuote( 'block' ) . " = 0";

		if ( ! $canModerate ) {
			$query				.=	"\n AND ( p." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
								.		' OR p.' . $_CB_database->NameQuote( 'published' ) . ' = 1 )';
		}

		$_CB_database->setQuery( $query );
		$total					=	(int) $_CB_database->loadResult();

		if ( ( ! $total ) && ( ! CBGroupJive::canCreateGroupContent( $user, $group, 'wall' ) ) ) {
			return null;
		}

		$pageNav				=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( 'gj_group_wall_' );

		switch( (int) $this->params->get( 'groups_wall_orderby', 2 ) ) {
			case 1:
				$orderBy		=	'p.' . $_CB_database->NameQuote( 'date' ) . ' ASC';
				break;
			case 3:
				$orderBy		=	'p.' . $_CB_database->NameQuote( 'reply' ) . ' ASC';
				break;
			case 4:
				$orderBy		=	'p.' . $_CB_database->NameQuote( 'reply' ) . ' DESC';
				break;
			case 2:
			default:
				$orderBy		=	'p.' . $_CB_database->NameQuote( 'date' ) . ' DESC';
				break;
		}

		$replies				=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_wall' ) . " AS r"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS rcb"
								.	' ON rcb.' . $_CB_database->NameQuote( 'id' ) . ' = r.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS rj"
								.	' ON rj.' . $_CB_database->NameQuote( 'id' ) . ' = rcb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE r." . $_CB_database->NameQuote( 'reply' ) . " = p." . $_CB_database->NameQuote( 'id' )
								.	"\n AND rcb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND rcb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND rj." . $_CB_database->NameQuote( 'block' ) . " = 0";

		$query					=	'SELECT p.*'
								.	', ( ' . $replies . ' ) AS _replies'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_wall' ) . " AS p"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS cb"
								.	' ON cb.' . $_CB_database->NameQuote( 'id' ) . ' = p.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS j"
								.	' ON j.' . $_CB_database->NameQuote( 'id' ) . ' = cb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE p." . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' )
								.	"\n AND p." . $_CB_database->NameQuote( 'reply' ) . " = 0"
								.	"\n AND cb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND cb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND j." . $_CB_database->NameQuote( 'block' ) . " = 0";

		if ( ! $canModerate ) {
			$query				.=	"\n AND ( p." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
								.		' OR p.' . $_CB_database->NameQuote( 'published' ) . ' = 1 )';
		}

		$query					.=	"\n ORDER BY " . $orderBy;
		if ( $this->params->get( 'groups_wall_paging', 1 ) ) {
			$_CB_database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
		} else {
			$_CB_database->setQuery( $query );
		}
		$rows					=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJiveWall\Table\WallTable', array( $_CB_database ) );

		CBGroupJiveWall::getPost( $rows );
		CBGroupJive::preFetchUsers( $rows );

		$group->set( '_wall', $pageNav->total );

		return array(	'id'		=>	'wall',
						'title'		=>	CBTxt::T( 'Wall' ),
						'content'	=>	HTML_groupjiveWall::showWall( $rows, $pageNav, $group, $user, $this )
					);
	}

	/**
	 * prepare frontend wall replies render
	 *
	 * @param WallTable  $reply
	 * @param GroupTable $group
	 * @param UserTable  $user
	 * @return array|null
	 */
	public function showReplies( $reply, $group, $user )
	{
		global $_CB_framework, $_CB_database;

		CBGroupJive::getTemplate( 'replies', true, true, $this->element );

		$canModerate			=	( CBGroupJive::isModerator( $user->get( 'id' ) ) || ( CBGroupJive::getGroupStatus( $user, $group ) >= 2 ) );
		$limit					=	(int) $this->params->get( 'groups_wall_replies_limit', 15 );
		$limitstart				=	$_CB_framework->getUserStateFromRequest( 'gj_group_wall_replies_limitstart{com_comprofiler}', 'gj_group_wall_replies_limitstart' );

		if ( $reply->get( '_replies' ) ) {
			$query				=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_wall' ) . " AS r"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS cb"
								.	' ON cb.' . $_CB_database->NameQuote( 'id' ) . ' = r.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS j"
								.	' ON j.' . $_CB_database->NameQuote( 'id' ) . ' = cb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE r." . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' )
								.	"\n AND r." . $_CB_database->NameQuote( 'reply' ) . " = " . (int) $reply->get( 'id' )
								.	"\n AND cb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND cb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND j." . $_CB_database->NameQuote( 'block' ) . " = 0";

			if ( ! $canModerate ) {
				$query			.=	"\n AND ( r." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
								.		' OR r.' . $_CB_database->NameQuote( 'published' ) . ' = 1 )';
			}

			$_CB_database->setQuery( $query );
			$total				=	(int) $_CB_database->loadResult();
		} else {
			$total				=	0;
		}

		if ( ( ! $total ) && ( ! CBGroupJive::canCreateGroupContent( $user, $group, 'wall' ) ) ) {
			return null;
		}

		$pageNav				=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setClasses( array( 'cbPaginationLinks' => 'cbPaginationLinks pagination pagination-sm' ) );
		$pageNav->setInputNamePrefix( 'gj_group_wall_replies_' );

		if ( $reply->get( '_replies' ) ) {
			switch( (int) $this->params->get( 'groups_wall_replies_orderby', 2 ) ) {
				case 1:
					$orderBy	=	'r.' . $_CB_database->NameQuote( 'date' ) . ' ASC';
					break;
				case 2:
				default:
					$orderBy	=	'r.' . $_CB_database->NameQuote( 'date' ) . ' DESC';
					break;
			}

			$query				=	'SELECT r.*'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_wall' ) . " AS r"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS cb"
								.	' ON cb.' . $_CB_database->NameQuote( 'id' ) . ' = r.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS j"
								.	' ON j.' . $_CB_database->NameQuote( 'id' ) . ' = cb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE r." . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' )
								.	"\n AND r." . $_CB_database->NameQuote( 'reply' ) . " = " . (int) $reply->get( 'id' )
								.	"\n AND cb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND cb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND j." . $_CB_database->NameQuote( 'block' ) . " = 0";

			if ( ! $canModerate ) {
				$query			.=	"\n AND ( r." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
								.		' OR r.' . $_CB_database->NameQuote( 'published' ) . ' = 1 )';
			}

			$query				.=	"\n ORDER BY " . $orderBy;
			if ( $this->params->get( 'groups_wall_replies_paging', 1 ) ) {
				$_CB_database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
			} else {
				$_CB_database->setQuery( $query );
			}
			$rows				=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJiveWall\Table\WallTable', array( $_CB_database ) );

			CBGroupJiveWall::getPost( $rows );
			CBGroupJive::preFetchUsers( $rows );
		} else {
			$rows				=	array();
		}

		return HTML_groupjiveWallReplies::showReplies( $reply, $rows, $pageNav, $group, $user, $this );
	}
}