<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;
use CBLib\Language\CBTxt;
use CB\Database\Table\UserTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\FieldTable;
use CB\Plugin\GroupJive\CBGroupJive;
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJive\Table\InviteTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );
$_PLUGINS->loadPluginGroup( 'user/plug_cbgroupjive/plugins' );

$_PLUGINS->registerUserFieldParams();
$_PLUGINS->registerUserFieldTypes( array(	'groupautojoin'			=>	'cbgjField',
											'groupmultiautojoin'	=>	'cbgjField'
										));

$_PLUGINS->registerFunction( 'activity_onQueryActivity', 'activityQuery', 'cbgjPlugin' );
$_PLUGINS->registerFunction( 'activity_onBeforeDisplayActivity', 'activityPrefetch', 'cbgjPlugin' );
$_PLUGINS->registerFunction( 'activity_onDisplayActivity', 'activityDisplay', 'cbgjPlugin' );
$_PLUGINS->registerFunction( 'onAfterDeleteUser', 'deleteUser', 'cbgjPlugin' );
$_PLUGINS->registerFunction( 'onAfterUserRegistration', 'acceptInvites', 'cbgjPlugin' );
$_PLUGINS->registerFunction( 'onAfterNewUser', 'acceptInvites', 'cbgjPlugin' );
$_PLUGINS->registerFunction( 'mod_onCBAdminMenu', 'adminMenu', 'cbgjPlugin' );

class cbgjPlugin extends cbPluginHandler
{

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

		$join[]				=	'LEFT JOIN ' . $_CB_database->NameQuote( '#__groupjive_groups' ) . ' AS gj_g'
							.	' ON a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND ( ( a.' . $_CB_database->NameQuote( 'subtype' ) . ' = ' . $_CB_database->Quote( 'group' )
							.	' AND a.' . $_CB_database->NameQuote( 'item' ) . ' = gj_g.' . $_CB_database->NameQuote( 'id' ) . ' )'
							.	' OR ( a.' . $_CB_database->NameQuote( 'subtype' ) . ' != ' . $_CB_database->Quote( 'group' )
							.	' AND a.' . $_CB_database->NameQuote( 'parent' ) . ' = gj_g.' . $_CB_database->NameQuote( 'id' ) . ' ) )';

		if ( ! CBGroupJive::isModerator() ) {
			$user			=	CBuser::getMyUserDataInstance();

			$join[]			=	'LEFT JOIN ' . $_CB_database->NameQuote( '#__groupjive_categories' ) . ' AS gj_c'
							.	' ON gj_c.' . $_CB_database->NameQuote( 'id' ) . ' = gj_g.' . $_CB_database->NameQuote( 'category' );

			$join[]			=	'LEFT JOIN ' . $_CB_database->NameQuote( '#__groupjive_users' ) . ' AS gj_u'
							.	' ON gj_u.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
							.	' AND gj_u.' . $_CB_database->NameQuote( 'group' ) . ' = gj_g.' . $_CB_database->NameQuote( 'id' );

			$join[]			=	'LEFT JOIN ' . $_CB_database->NameQuote( '#__groupjive_invites' ) . ' AS gj_i'
							.	' ON gj_i.' . $_CB_database->NameQuote( 'group' ) . ' = gj_g.' . $_CB_database->NameQuote( 'id' )
							.	' AND gj_i.' . $_CB_database->NameQuote( 'accepted' ) . ' = ' . $_CB_database->Quote( '0000-00-00 00:00:00' )
							.	' AND ( ( gj_i.' . $_CB_database->NameQuote( 'email' ) . ' = ' . $_CB_database->Quote( $user->get( 'email' ) )
							.	' AND gj_i.' . $_CB_database->NameQuote( 'email' ) . ' != "" )'
							.	' OR ( gj_i.' . $_CB_database->NameQuote( 'user' ) . ' = ' . (int) $user->get( 'id' )
							.	' AND gj_i.' . $_CB_database->NameQuote( 'user' ) . ' > 0 ) )';

			$where[]		=	'( ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND gj_g.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL'
							.	' AND ( gj_g.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
							.		' OR ( gj_g.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
							.		' AND ( gj_g.' . $_CB_database->NameQuote( 'type' ) . ' IN ( 1, 2 )'
							.		' OR gj_u.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 0, 1, 2, 3 )'
							.		' OR gj_i.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL ) ) )'
							.	' AND ( ( gj_c.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
							.		' AND gj_c.' . $_CB_database->NameQuote( 'access' ) . ' IN ' . $_CB_database->safeArrayOfIntegers( CBGroupJive::getAccess( (int) $user->get( 'id' ) ) ) . ' )'
							.		( $this->params->get( 'groups_uncategorized', 1 ) ? ' OR gj_g.' . $_CB_database->NameQuote( 'category' ) . ' = 0 ) )' : ' ) )' )
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' != ' . $_CB_database->Quote( 'groupjive' ) . ' ) )';
		} else {
			$where[]		=	'( ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND gj_g.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL )'
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' != ' . $_CB_database->Quote( 'groupjive' ) . ' ) )';
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

		$groupIds				=	array();

		foreach ( $rows as $row ) {
			if ( $row->get( 'type' ) != 'groupjive' ) {
				continue;
			} elseif ( $row->get( 'subtype' ) == 'category' ) {
				continue;
			}

			if ( $row->get( 'subtype' ) == 'group' ) {
				$groupId		=	(int) $row->get( 'item' );
			} else {
				$groupId		=	(int) $row->get( 'parent' );
			}

			if ( $groupId && ( ! in_array( $groupId, $groupIds ) ) ) {
				$groupIds[]		=	$groupId;
			}
		}

		if ( ! $groupIds ) {
			return;
		}

		$user					=	CBuser::getMyUserDataInstance();

		$users					=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_users' ) . " AS uc"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS uccb"
								.	' ON uccb.' . $_CB_database->NameQuote( 'id' ) . ' = uc.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS ucj"
								.	' ON ucj.' . $_CB_database->NameQuote( 'id' ) . ' = uccb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE uc." . $_CB_database->NameQuote( 'group' ) . " = g." . $_CB_database->NameQuote( 'id' )
								.	"\n AND uccb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND uccb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND ucj." . $_CB_database->NameQuote( 'block' ) . " = 0";

		if ( ! $this->params->get( 'groups_users_owner', 1 ) ) {
			$users				.=	"\n AND uc." . $_CB_database->NameQuote( 'status' ) . " != 4";
		}

		$query					=	'SELECT g.*'
								.	', c.' . $_CB_database->NameQuote( 'name' ) . ' AS _category_name'
								.	', u.' . $_CB_database->NameQuote( 'status' ) . ' AS _user_status'
								.	', i.' . $_CB_database->NameQuote( 'id' ) . ' AS _invite_id'
								.	', ( ' . $users . ' ) AS _users'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_groups' ) . " AS g"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_categories' ) . " AS c"
								.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = g.' . $_CB_database->NameQuote( 'category' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_users' ) . " AS u"
								.	' ON u.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
								.	' AND u.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_invites' ) . " AS i"
								.	' ON i.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	' AND i.' . $_CB_database->NameQuote( 'accepted' ) . ' = ' . $_CB_database->Quote( '0000-00-00 00:00:00' )
								.	' AND ( ( i.' . $_CB_database->NameQuote( 'email' ) . ' = ' . $_CB_database->Quote( $user->get( 'email' ) )
								.	' AND i.' . $_CB_database->NameQuote( 'email' ) . ' != "" )'
								.	' OR ( i.' . $_CB_database->NameQuote( 'user' ) . ' = ' . (int) $user->get( 'id' )
								.	' AND i.' . $_CB_database->NameQuote( 'user' ) . ' > 0 ) )'
								.	"\n WHERE g." . $_CB_database->NameQuote( 'id' ) . " IN " . $_CB_database->safeArrayOfIntegers( $groupIds );
		$_CB_database->setQuery( $query );
		$groups					=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJive\Table\GroupTable', array( $_CB_database ) );

		if ( ! $groups ) {
			return;
		}

		CBGroupJive::getGroup( $groups );
		CBGroupJive::preFetchUsers( $groups );
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
		if ( $row->get( 'type' ) != 'groupjive' ) {
			return;
		}

		$row->set( '_links', false );

		if ( ! in_array( $row->get( 'subtype' ), array( 'group', 'group.join', 'group.leave' ) ) ) {
			return;
		}

		if ( $row->get( 'subtype' ) == 'group' ) {
			$groupId	=	(int) $row->get( 'item' );
		} else {
			$groupId	=	(int) $row->get( 'parent' );
		}

		$group			=	CBGroupJive::getGroup( $groupId );

		if ( ! $group->get( 'id' ) ) {
			return;
		}

		CBGroupJive::getTemplate( 'activity' );

		$insert			=	HTML_groupjiveActivity::showActivity( $row, $title, $message, $stream, $group, $this );
	}

	/**
	 * Deletes data when a user is deleted
	 *
	 * @param  UserTable $user
	 * @param  int       $status
	 */
	public function deleteUser( $user, $status )
	{
		global $_CB_database;

		if ( $this->params->get( 'general_delete', 1 ) ) {
			$query		=	'SELECT *'
						.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_groups' )
						.	"\n WHERE " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' );
			$_CB_database->setQuery( $query );
			$groups		=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJive\Table\GroupTable', array( $_CB_database ) );

			/** @var GroupTable[] $groups */
			foreach ( $groups as $group ) {
				$group->delete();
			}
		}
	}

	/**
	 * Auto accepts invites on registration
	 *
	 * @param  UserTable $user
	 */
	public function acceptInvites( $user )
	{
		global $_CB_database;

		if ( $this->params->get( 'groups_invites_accept', 1 ) ) {
			$query					=	'SELECT *'
									.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_invites' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'accepted' ) . ' = ' . $_CB_database->Quote( '0000-00-00 00:00:00' )
									.	"\n AND ( ( " . $_CB_database->NameQuote( 'email' ) . ' = ' . $_CB_database->Quote( $user->get( 'email' ) )
									.	' AND ' . $_CB_database->NameQuote( 'email' ) . ' != "" )'
									.	' OR ( ' . $_CB_database->NameQuote( 'user' ) . ' = ' . (int) $user->get( 'id' )
									.	' AND ' . $_CB_database->NameQuote( 'user' ) . ' > 0 ) )';
			$_CB_database->setQuery( $query );
			$invites				=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJive\Table\InviteTable', array( $_CB_database ) );

			$notified				=	array();

			/** @var InviteTable[] $invites */
			foreach ( $invites as $invite ) {
				if ( $invite->accept() && ( ! in_array( $invite->get( 'user_id' ), $notified ) ) ) {
					CBGroupJive::sendNotifications( 'invite_accept', CBTxt::T( 'Group invite accepted' ), CBTxt::T( 'Your group [group] invite to [user] has been accepted!' ), $invite->group(), $user, (int) $invite->get( 'user_id' ), $notified );

					$notified[]		=	$invite->get( 'user_id' );
				}
			}
		}
	}

	/**
	 * Displays backend menu items
	 *
	 * @param array $menu
	 * @param bool  $disabled
	 */
	public function adminMenu( &$menu, $disabled )
	{
		global $_CB_framework, $_PLUGINS;

		$gjMenu					=	array();

		$gjMenu['component']	=	array(	'title' => CBTxt::T( 'GroupJive' ) );
		$gjMenu['menu']			=	array(	array(	'title' => CBTxt::T( 'Categories' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'action' => 'showgjcategories', 'cid' => $this->getPluginId() ) ), 'icon' => 'cbgj-categories',
													'submenu' => array( array( 'title' => CBTxt::Th( 'Add New Category' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'cid' => $this->getPluginId(), 'table' => 'gjcategoriesbrowser', 'action' => 'editrow' ) ), 'icon' => 'cb-new' ) )
											),
											array(	'title' => CBTxt::T( 'Groups' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'action' => 'showgjgroups', 'cid' => $this->getPluginId() ) ), 'icon' => 'cbgj-groups',
													'submenu' => array( array( 'title' => CBTxt::Th( 'Add New Group' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'cid' => $this->getPluginId(), 'table' => 'gjgroupsbrowser', 'action' => 'editrow' ) ), 'icon' => 'cb-new' ) )
											),
											array(	'title' => CBTxt::T( 'GROUP_USERS', 'Users' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'action' => 'showgjusers', 'cid' => $this->getPluginId() ) ), 'icon' => 'cbgj-users',
													'submenu' => array( array( 'title' => CBTxt::Th( 'Add New User to Group' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'cid' => $this->getPluginId(), 'table' => 'gjusersbrowser', 'action' => 'editrow' ) ), 'icon' => 'cb-new' ) )
											),
											array(	'title' => CBTxt::T( 'Invites' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'action' => 'showgjinvites', 'cid' => $this->getPluginId() ) ), 'icon' => 'cbgj-invites',
													'submenu' => array( array( 'title' => CBTxt::Th( 'Invite New User to Group' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'cid' => $this->getPluginId(), 'table' => 'gjinvitesbrowser', 'action' => 'editrow' ) ), 'icon' => 'cb-new' ) )
											)
										);

		$_PLUGINS->trigger( 'gj_onAdminMenu', array( &$gjMenu['menu'] ) );

		$gjMenu['menu'][]		=	array(	'title' => CBTxt::T( 'Notifications' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'action' => 'showgjnotifications', 'cid' => $this->getPluginId() ) ), 'icon' => 'cbgj-invites' );
		$gjMenu['menu'][]		=	array(	'title' => CBTxt::T( 'Configuration' ), 'link' => $_CB_framework->backendViewUrl( 'editrow', true, array( 'table' => 'pluginsbrowser', 'action' => 'editrow', 'cid' => $this->getPluginId() ) ), 'icon' => 'cbgj-config' );

		$menu['gj']				=	$gjMenu;
	}
}

class cbgjTab extends cbTabHandler
{

	/**
	 * prepare frontend tab render
	 *
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param int       $ui
	 * @return null|string
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		global $_CB_framework, $_CB_database;

		if ( ! ( $tab->params instanceof ParamsInterface ) ) {
			$tab->params		=	new Registry( $tab->params );
		}

		$viewer					=	CBuser::getMyUserDataInstance();
		$isModerator			=	CBGroupJive::isModerator( $viewer->get( 'id' ) );
		$isOwner				=	( $viewer->get( 'id' ) == $user->get( 'id' ) );

		CBGroupJive::getTemplate( 'tab' );

		$limit					=	(int) $tab->params->get( 'tab_limit', 30 );
		$limitstart				=	$_CB_framework->getUserStateFromRequest( 'gj_tab_limitstart{com_comprofiler}', 'gj_tab_limitstart' );
		$search					=	$_CB_framework->getUserStateFromRequest( 'gj_tab_search{com_comprofiler}', 'gj_tab_search' );
		$where					=	null;

		if ( $search && $tab->params->get( 'tab_search', 1 ) ) {
			$where				.=	"\n AND ( g." . $_CB_database->NameQuote( 'name' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
								.	" OR g." . $_CB_database->NameQuote( 'description' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false ) . " )";
		}

		$searching				=	( $where ? true : false );

		$query					=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_groups' ) . " AS g";

		if ( ! $isModerator ) {
			$query				.=	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_categories' ) . " AS c"
								.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = g.' . $_CB_database->NameQuote( 'category' );
		}

		$query					.=	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_users' ) . " AS u"
								.	' ON u.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	' AND u.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_invites' ) . " AS i"
								.	' ON i.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	' AND i.' . $_CB_database->NameQuote( 'accepted' ) . ' = ' . $_CB_database->Quote( '0000-00-00 00:00:00' )
								.	' AND ( ( i.' . $_CB_database->NameQuote( 'email' ) . ' = ' . $_CB_database->Quote( $user->get( 'email' ) )
								.	' AND i.' . $_CB_database->NameQuote( 'email' ) . ' != "" )'
								.	' OR ( i.' . $_CB_database->NameQuote( 'user' ) . ' = ' . (int) $user->get( 'id' )
								.	' AND i.' . $_CB_database->NameQuote( 'user' ) . ' > 0 ) )';

		if ( $isOwner ) {
			$query				.=	"\n WHERE ( g." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' );

			if ( ! $isModerator ) {
				$query			.=		' OR ( g.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
								.		' AND ( u.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 0, 1, 2, 3 )'
								.		' OR i.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL ) ) )';
			} else {
				$query			.=		' OR u.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 0, 1, 2, 3 )'
								.		' OR i.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL )';
			}
		} else {
			$query				.=	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_users' ) . " AS mu"
								.	' ON mu.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	' AND mu.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $viewer->get( 'id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_invites' ) . " AS mi"
								.	' ON mi.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	' AND mi.' . $_CB_database->NameQuote( 'accepted' ) . ' = ' . $_CB_database->Quote( '0000-00-00 00:00:00' )
								.	' AND ( ( mi.' . $_CB_database->NameQuote( 'email' ) . ' = ' . $_CB_database->Quote( $viewer->get( 'email' ) )
								.	' AND mi.' . $_CB_database->NameQuote( 'email' ) . ' != "" )'
								.	' OR ( mi.' . $_CB_database->NameQuote( 'user' ) . ' = ' . (int) $viewer->get( 'id' )
								.	' AND mi.' . $_CB_database->NameQuote( 'user' ) . ' > 0 ) )'
								.	"\n WHERE ( g." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' );

			if ( ! $isModerator ) {
				$query			.=		' OR ( g.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
								.		' AND u.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 1, 2, 3 ) ) )'
								.	"\n AND ( g." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $viewer->get( 'id' )
								.		' OR ( g.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
								.		' AND ( g.' . $_CB_database->NameQuote( 'type' ) . ' IN ( 1, 2 )'
								.		' OR mu.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 0, 1, 2, 3 )'
								.		' OR mi.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL ) ) )';
			} else {
				$query			.=		' OR u.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 1, 2, 3 ) )';
			}
		}

		if ( ! $isModerator ) {
			$query				.=	"\n AND ( ( c." . $_CB_database->NameQuote( 'published' ) . " = 1"
								.		' AND c.' . $_CB_database->NameQuote( 'access' ) . ' IN ' . $_CB_database->safeArrayOfIntegers( CBGroupJive::getAccess( (int) $user->get( 'id' ) ) )
								.		' AND c.' . $_CB_database->NameQuote( 'access' ) . ' IN ' . $_CB_database->safeArrayOfIntegers( CBGroupJive::getAccess( (int) $viewer->get( 'id' ) ) ) . ' )'
								.		( $this->params->get( 'groups_uncategorized', 1 ) ? ' OR g.' . $_CB_database->NameQuote( 'category' ) . ' = 0 )' : ' )' );
		}

		$query					.=	$where;
		$_CB_database->setQuery( $query );
		$total					=	(int) $_CB_database->loadResult();

		if ( ( ! $total ) && ( ! $searching ) && ( ( ! $isOwner ) || ( $isOwner && ( ! CBGroupJive::canCreateGroup( $user ) ) ) ) && ( ! Application::Config()->get( 'showEmptyTabs', 1 ) ) ) {
			return null;
		}

		$pageNav				=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( 'gj_tab_' );

		switch( (int) $tab->params->get( 'tab_orderby', 1 ) ) {
			case 2:
				$orderBy		=	'g.' . $_CB_database->NameQuote( 'ordering' ) . ' DESC';
				break;
			case 3:
				$orderBy		=	'g.' . $_CB_database->NameQuote( 'date' ) . ' ASC';
				break;
			case 4:
				$orderBy		=	'g.' . $_CB_database->NameQuote( 'date' ) . ' DESC';
				break;
			case 5:
				$orderBy		=	'g.' . $_CB_database->NameQuote( 'name' ) . ' ASC';
				break;
			case 6:
				$orderBy		=	'g.' . $_CB_database->NameQuote( 'name' ) . ' DESC';
				break;
			case 7:
				$orderBy		=	$_CB_database->NameQuote( '_users' ) . ' ASC';
				break;
			case 8:
				$orderBy		=	$_CB_database->NameQuote( '_users' ) . ' DESC';
				break;
			case 1:
			default:
				$orderBy		=	'g.' . $_CB_database->NameQuote( 'ordering' ) . ' ASC';
				break;
		}

		$users					=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_users' ) . " AS uc"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS uccb"
								.	' ON uccb.' . $_CB_database->NameQuote( 'id' ) . ' = uc.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS ucj"
								.	' ON ucj.' . $_CB_database->NameQuote( 'id' ) . ' = uccb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE uc." . $_CB_database->NameQuote( 'group' ) . " = g." . $_CB_database->NameQuote( 'id' )
								.	"\n AND uccb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND uccb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND ucj." . $_CB_database->NameQuote( 'block' ) . " = 0";

		if ( ! $isModerator ) {
			$users				.=	"\n AND ( g." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $viewer->get( 'id' )
								.		( ! $isOwner ? ' OR mu.' . $_CB_database->NameQuote( 'status' ) . ' >= 2' : null )
								.		' OR uc.' . $_CB_database->NameQuote( 'status' ) . ' >= 1 )';
		}

		if ( ! $this->params->get( 'groups_users_owner', 1 ) ) {
			$users				.=	"\n AND uc." . $_CB_database->NameQuote( 'status' ) . " != 4";
		}

		$query					=	'SELECT g.*'
								.	', c.' . $_CB_database->NameQuote( 'name' ) . ' AS _category_name';

		if ( $isOwner ) {
			$query				.=	', u.' . $_CB_database->NameQuote( 'status' ) . ' AS _user_status'
								.	', i.' . $_CB_database->NameQuote( 'id' ) . ' AS _invite_id';
		} else {
			$query				.=	', mu.' . $_CB_database->NameQuote( 'status' ) . ' AS _user_status'
								.	', mi.' . $_CB_database->NameQuote( 'id' ) . ' AS _invite_id';
		}

		$query					.=	', ( ' . $users . ' ) AS _users'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_groups' ) . " AS g"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_categories' ) . " AS c"
								.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = g.' . $_CB_database->NameQuote( 'category' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_users' ) . " AS u"
								.	' ON u.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
								.	' AND u.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_invites' ) . " AS i"
								.	' ON i.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	' AND i.' . $_CB_database->NameQuote( 'accepted' ) . ' = ' . $_CB_database->Quote( '0000-00-00 00:00:00' )
								.	' AND ( ( i.' . $_CB_database->NameQuote( 'email' ) . ' = ' . $_CB_database->Quote( $user->get( 'email' ) )
								.	' AND i.' . $_CB_database->NameQuote( 'email' ) . ' != "" )'
								.	' OR ( i.' . $_CB_database->NameQuote( 'user' ) . ' = ' . (int) $user->get( 'id' )
								.	' AND i.' . $_CB_database->NameQuote( 'user' ) . ' > 0 ) )';

		if ( $isOwner ) {
			$query				.=	"\n WHERE ( g." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' );

			if ( ! $isModerator ) {
				$query			.=		' OR ( g.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
								.		' AND ( u.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 0, 1, 2, 3 )'
								.		' OR i.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL ) ) )';
			} else {
				$query			.=		' OR u.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 0, 1, 2, 3 )'
								.		' OR i.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL )';
			}
		} else {
			$query				.=	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_users' ) . " AS mu"
								.	' ON mu.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $viewer->get( 'id' )
								.	' AND mu.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_invites' ) . " AS mi"
								.	' ON mi.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	' AND mi.' . $_CB_database->NameQuote( 'accepted' ) . ' = ' . $_CB_database->Quote( '0000-00-00 00:00:00' )
								.	' AND ( ( mi.' . $_CB_database->NameQuote( 'email' ) . ' = ' . $_CB_database->Quote( $viewer->get( 'email' ) )
								.	' AND mi.' . $_CB_database->NameQuote( 'email' ) . ' != "" )'
								.	' OR ( mi.' . $_CB_database->NameQuote( 'user' ) . ' = ' . (int) $viewer->get( 'id' )
								.	' AND mi.' . $_CB_database->NameQuote( 'user' ) . ' > 0 ) )'
								.	"\n WHERE ( g." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' );

			if ( ! $isModerator ) {
				$query			.=		' OR ( g.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
								.		' AND u.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 1, 2, 3 ) ) )'
								.	"\n AND ( g." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $viewer->get( 'id' )
								.		' OR ( g.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
								.		' AND ( g.' . $_CB_database->NameQuote( 'type' ) . ' IN ( 1, 2 )'
								.		' OR mu.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 0, 1, 2, 3 )'
								.		' OR mi.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL ) ) )';
			} else {
				$query			.=		' OR u.' . $_CB_database->NameQuote( 'status' ) . ' IN ( 1, 2, 3 ) )';
			}
		}

		if ( ! $isModerator ) {
			$query				.=	"\n AND ( ( c." . $_CB_database->NameQuote( 'published' ) . " = 1"
								.		' AND c.' . $_CB_database->NameQuote( 'access' ) . ' IN ' . $_CB_database->safeArrayOfIntegers( CBGroupJive::getAccess( (int) $user->get( 'id' ) ) )
								.		' AND c.' . $_CB_database->NameQuote( 'access' ) . ' IN ' . $_CB_database->safeArrayOfIntegers( CBGroupJive::getAccess( (int) $viewer->get( 'id' ) ) ) . ' )'
								.		( $this->params->get( 'groups_uncategorized', 1 ) ? ' OR g.' . $_CB_database->NameQuote( 'category' ) . ' = 0 )' : ' )' );
		}

		$query					.=	$where
								.	"\n ORDER BY " . $orderBy;
		if ( $tab->params->get( 'tab_paging', 1 ) ) {
			$_CB_database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
		} else {
			$_CB_database->setQuery( $query );
		}
		$rows					=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJive\Table\GroupTable', array( $_CB_database ) );

		$input['search']		=	'<input type="text" name="gj_tab_search" value="' . htmlspecialchars( $search ) . '" onchange="document.gjTabForm.submit();" placeholder="' . htmlspecialchars( CBTxt::T( 'Search Groups...' ) ) . '" class="form-control" />';

		CBGroupJive::getGroup( $rows );
		CBGroupJive::preFetchUsers( $rows );

		$class					=	$this->params->get( 'general_class', null );

		$return					=	'<div class="cbGroupJive' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . '">'
								.		'<div class="cbGroupJiveInner">'
								.			HTML_groupjiveTab::showTab( $rows, $pageNav, $searching, $input, $viewer, $user, $tab, $this )
								.		'</div>'
								.	'</div>';

		return $return;
	}
}

class cbgjField extends cbFieldHandler
{

	/**
	 * Formatter:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output               'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $formatting           'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string      $reason               'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getFieldRow( &$field, &$user, $output, $formatting, $reason, $list_compare_types )
	{
		if ( ! in_array( $reason, array( 'register', 'edit' ) ) ) {
			return null;
		}

		return parent::getFieldRow( $field, $user, $output, $formatting, $reason, $list_compare_types );
	}

	/**
	 * Accessor:
	 * Returns a field in specified format
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $output               'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $reason               'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  int         $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function getField( &$field, &$user, $output, $reason, $list_compare_types )
	{
		if ( ( $output != 'htmledit' ) && ( $reason != 'search' ) ) {
			return null;
		}

		$options				=	$this->getGroups( $field, $user );

		if ( ! $options ) {
			return null;
		}

		switch ( $field->get( 'type' ) ) {
			case 'groupmultiautojoin':
				$fieldType		=	'multiselect';
				break;
			case 'groupautojoin':
			default:
				$fieldType		=	'select';
				break;
		}

		return $this->_fieldEditToHtml( $field, $user, $reason, 'input', $fieldType, null, null, $options );
	}

	/**
	 * Mutator:
	 * Prepares field data for saving to database (safe transfer from $postdata to $user)
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function prepareFieldDataSave( &$field, &$user, &$postdata, $reason )
	{
		if ( ! in_array( $reason, array( 'register', 'edit' ) ) ) {
			return;
		}

		$this->_prepareFieldMetaSave( $field, $user, $postdata, $reason );

		$value		=	$this->getValue( $field, $user, $postdata );

		if ( $this->validate( $field, $user, $field->get( 'name' ), $value, $postdata, $reason ) ) {
			$this->_logFieldUpdate( $field, $user, $reason, null, $value );
		}
	}

	/**
	 * Mutator:
	 * Prepares field data commit
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function commitFieldDataSave( &$field, &$user, &$postdata, $reason )
	{
		if ( ! in_array( $reason, array( 'register', 'edit' ) ) ) {
			return;
		}

		$value				=	$this->getValue( $field, $user, $postdata );

		if ( $value ) {
			$groups			=	explode( '|*|', $value );

			cbArrayToInts( $groups );

			foreach ( $groups as $groupId ) {
				$row		=	new \CB\Plugin\GroupJive\Table\UserTable();

				$row->load( array( 'user_id' => (int) $user->get( 'id' ), 'group' => (int) $groupId ) );

				if ( $row->get( 'id' ) ) {
					continue;
				}

				$row->set( 'user_id', (int) $user->get( 'id' ) );
				$row->set( 'group', (int) $groupId );
				$row->set( 'status', 1 );

				if ( $row->getError() || ( ! $row->check() ) ) {
					$this->_setValidationError( $field, $user, $reason, $row->getError() );
					break;
				}

				if ( $row->getError() || ( ! $row->store() ) ) {
					$this->_setValidationError( $field, $user, $reason, $row->getError() );
					break;
				}
			}
		}
	}

	/**
	 * Mutator:
	 * Prepares field data rollback
	 * Override
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array       $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string      $reason    'edit' for save user edit, 'register' for save registration
	 */
	public function rollbackFieldDataSave( &$field, &$user, &$postdata, $reason )
	{
		if ( ! in_array( $reason, array( 'register', 'edit' ) ) ) {
			return;
		}

		$value			=	$this->getValue( $field, $user, $postdata, true );

		if ( $value ) {
			$groups		=	explode( '|*|', $value );

			cbArrayToInts( $groups );

			foreach ( $groups as $groupId ) {
				$row	=	new \CB\Plugin\GroupJive\Table\UserTable();

				$row->load( array( 'user_id' => (int) $user->get( 'id' ), 'group' => (int) $groupId ) );

				if ( ! $row->get( 'id' ) ) {
					continue;
				}

				if ( ! $row->canDelete() ) {
					$this->_setValidationError( $field, $user, $reason, $row->getError() );
					break;
				}

				if ( ! $row->delete() ) {
					$this->_setValidationError( $field, $user, $reason, $row->getError() );
					break;
				}
			}
		}
	}

	/**
	 * @param FieldTable $field
	 * @param UserTable  $user
	 * @param bool       $raw
	 * @param bool       $joined
	 * @return stdClass[]
	 */
	private function getGroups( $field, $user, $raw = false, $joined = false )
	{
		global $_CB_database;

		$excludeCategories		=	explode( '|*|', $field->params->get( 'autojoin_exclude_categories' ) );
		$excludeGroups			=	explode( '|*|', $field->params->get( 'autojoin_exclude_groups' ) );

		if ( $user->get( 'id' ) && ( ! $joined ) ) {
			static $cache		=	array();

			$userId				=	(int) $user->get( 'id' );

			if ( ! isset( $cache[$userId] ) ) {
				$query			=	'SELECT g.' . $_CB_database->NameQuote( 'id' )
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_groups' ) . " AS g"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_users' ) . " AS u"
								.	' ON u.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $userId
								.	' AND u.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE ( g." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $userId
								.		' OR u.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL )';

				$_CB_database->setQuery( $query );
				$cache[$userId]	=	$_CB_database->loadResultArray();
			}

			if ( $cache[$userId] ) {
				$excludeGroups	=	array_unique( array_merge( $excludeGroups, $cache[$userId] ) );
			}
		}

		$options				=	CBGroupJive::getGroupOptions( $raw, $excludeCategories, $excludeGroups );

		return $options;
	}

	/**
	 * @param FieldTable $field
	 * @param UserTable  $user
	 * @param array      $postdata
	 * @param bool       $joined
	 * @return null|string
	 */
	private function getValue( $field, $user, $postdata, $joined = false )
	{
		$value						=	cbGetParam( $postdata, $field->get( 'name' ), null, _CB_ALLOWRAW );

		if ( ( $value === null ) || ( $value === '' ) || ( is_array( $value ) && ( count( $value ) <= 0 ) ) ) {
			$value					=	'';
		} else {
			$options				=	$this->getGroups( $field, $user, true, $joined );
			$groups					=	array();

			foreach ( $options as $option ) {
				$groups[]			=	$option->value;
			}

			if ( is_array( $value ) ) {
				$values				=	array();

				foreach ( $value as $k => $v ) {
					$v				=	stripslashes( $v );

					if ( in_array( $value, $groups ) ) {
						$values[]	=	$v;
					}
				}

				$value				=	$this->_implodeCBvalues( $values );
			} else {
				$value				=	stripslashes( $value );

				if ( ! in_array( $value, $groups ) ) {
					$value			=	null;
				}
			}
		}

		return $value;
	}
}
