<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Registry\Registry;
use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;
use CB\Plugin\GroupJive\CBGroupJive;
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJive\Table\CategoryTable;
use CB\Plugin\GroupJive\Table\NotificationTable;
use CB\Plugin\GroupJiveEvents\CBGroupJiveEvents;
use CB\Plugin\GroupJiveEvents\Table\EventTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );

$_PLUGINS->registerFunction( 'activity_onQueryActivity', 'activityQuery', 'cbgjEventsPlugin' );
$_PLUGINS->registerFunction( 'activity_onBeforeDisplayActivity', 'activityPrefetch', 'cbgjEventsPlugin' );
$_PLUGINS->registerFunction( 'activity_onDisplayActivity', 'activityDisplay', 'cbgjEventsPlugin' );
$_PLUGINS->registerFunction( 'gj_onAdminMenu', 'adminMenu', 'cbgjEventsPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterDeleteGroup', 'deleteGroup', 'cbgjEventsPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterCreateUser', 'storeNotifications', 'cbgjEventsPlugin' );
$_PLUGINS->registerFunction( 'gj_onCanCreateGroupContent', 'canCreate', 'cbgjEventsPlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayGroupEdit', 'editGroup', 'cbgjEventsPlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayNotifications', 'editNotifications', 'cbgjEventsPlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayGroup', 'showEvents', 'cbgjEventsPlugin' );

class cbgjEventsPlugin extends cbPluginHandler
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

		$join[]				=	'LEFT JOIN ' . $_CB_database->NameQuote( '#__groupjive_plugin_events' ) . ' AS gj_e'
							.	' ON a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' = ' . $_CB_database->Quote( 'group.event' )
							.	' AND a.' . $_CB_database->NameQuote( 'item' ) . ' = gj_e.' . $_CB_database->NameQuote( 'id' );

		if ( ! CBGroupJive::isModerator() ) {
			$user			=	CBuser::getMyUserDataInstance();

			$where[]		=	'( ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' = ' . $_CB_database->Quote( 'group.event' )
							.	' AND gj_e.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL'
							.	' AND ( gj_e.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
							.		' OR ( gj_e.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
							.		' AND ( gj_g.' . $_CB_database->NameQuote( 'type' ) . ' IN ( 1, 2 )'
							.		' OR gj_u.' . $_CB_database->NameQuote( 'status' ) . ' > 0 ) ) ) )'
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' != ' . $_CB_database->Quote( 'groupjive' )
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' != ' . $_CB_database->Quote( 'group.event' ) . ' ) ) )';
		} else {
			$where[]		=	'( ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' = ' . $_CB_database->Quote( 'group.event' )
							.	' AND gj_e.' . $_CB_database->NameQuote( 'id' ) . ' IS NOT NULL )'
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' != ' . $_CB_database->Quote( 'groupjive' )
							.	' OR ( a.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'groupjive' )
							.	' AND a.' . $_CB_database->NameQuote( 'subtype' ) . ' != ' . $_CB_database->Quote( 'group.event' ) . ' ) ) )';
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

		$eventIds				=	array();

		foreach ( $rows as $row ) {
			if ( ! ( ( $row->get( 'type' ) == 'groupjive' ) && ( $row->get( 'subtype' ) == 'group.event' ) ) ) {
				continue;
			}

			$eventId			=	(int) $row->get( 'item' );

			if ( $eventId && ( ! in_array( $eventId, $eventIds ) ) ) {
				$eventIds[]		=	$eventId;
			}
		}

		if ( ! $eventIds ) {
			return;
		}

		$user					=	CBuser::getMyUserDataInstance();

		$guests					=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_events_attendance' ) . " AS ea"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS eacb"
								.	' ON eacb.' . $_CB_database->NameQuote( 'id' ) . ' = ea.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS eaj"
								.	' ON eaj.' . $_CB_database->NameQuote( 'id' ) . ' = eacb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE ea." . $_CB_database->NameQuote( 'event' ) . " = e." . $_CB_database->NameQuote( 'id' )
								.	"\n AND eacb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND eacb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND eaj." . $_CB_database->NameQuote( 'block' ) . " = 0";

		$query					=	'SELECT e.*'
								.	', a.' . $_CB_database->NameQuote( 'id' ) . ' AS _attending'
								.	', ( ' . $guests . ' ) AS _guests'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_events' ) . " AS e"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_plugin_events_attendance' ) . " AS a"
								.	' ON a.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
								.	' AND a.' . $_CB_database->NameQuote( 'event' ) . ' = e.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE e." . $_CB_database->NameQuote( 'id' ) . " IN " . $_CB_database->safeArrayOfIntegers( $eventIds );
		$_CB_database->setQuery( $query );
		$events					=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJiveEvents\Table\EventTable', array( $_CB_database ) );

		if ( ! $events ) {
			return;
		}

		CBGroupJiveEvents::getEvent( $events );
		CBGroupJive::preFetchUsers( $events );
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
		if ( ! ( ( $row->get( 'type' ) == 'groupjive' ) && ( $row->get( 'subtype' ) == 'group.event' ) ) ) {
			return;
		}

		$event			=	CBGroupJiveEvents::getEvent( (int) $row->get( 'item' ) );

		if ( ! $event->get( 'id' ) ) {
			return;
		}

		CBGroupJive::getTemplate( 'activity', true, true, $this->element );

		$insert			=	HTML_groupjiveEventActivity::showEventActivity( $row, $title, $message, $stream, $event, $this );
	}

	/**
	 * Displays backend menu items
	 *
	 * @param array $menu
	 */
	public function adminMenu( &$menu )
	{
		global $_CB_framework;

		$menu[]		=	array(	'title' => CBTxt::T( 'Events' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'action' => 'showgjevents', 'cid' => $this->getPluginId() ) ), 'icon' => 'cbgj-events',
								'submenu' => array(	array( 'title' => CBTxt::Th( 'Add New Event to Group' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'cid' => $this->getPluginId(), 'table' => 'gjeventsbrowser', 'action' => 'editrow' ) ), 'icon' => 'cb-new' ),
													array( 'title' => CBTxt::T( 'Event Attendance' ), 'link' => $_CB_framework->backendViewUrl( 'editPlugin', true, array( 'action' => 'showgjattendance', 'cid' => $this->getPluginId() ) ), 'icon' => 'cbgj-eventsattendance' )
								)
							);
	}

	/**
	 * delete all the events for the group that was deleted
	 *
	 * @param GroupTable $group
	 */
	public function deleteGroup( $group )
	{
		global $_CB_database;

		$query			=	'SELECT *'
						.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_events' )
						.	"\n WHERE " . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' );
		$_CB_database->setQuery( $query );
		$events			=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJiveEvents\Table\EventTable', array( $_CB_database ) );

		/** @var EventTable[] $events */
		foreach ( $events as $event ) {
			$event->delete();
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
		$notifications->set( 'event_new', $this->params->get( 'notifications_default_event_new', 0 ) );
		$notifications->set( 'event_edit', $this->params->get( 'notifications_default_event_edit', 0 ) );
		$notifications->set( 'event_approve', $this->params->get( 'notifications_default_event_approve', 0 ) );
		$notifications->set( 'event_attend', $this->params->get( 'notifications_default_event_attend', 0 ) );
		$notifications->set( 'event_unattend', $this->params->get( 'notifications_default_event_unattend', 0 ) );
	}

	/**
	 * check events create limit
	 *
	 * @param bool       $access
	 * @param string     $param
	 * @param GroupTable $group
	 * @param UserTable  $user
	 */
	public function canCreate( &$access, $param, $group, $user )
	{
		global $_CB_database;

		if ( $param == 'events' ) {
			$createLimit				=	(int) $this->params->get( 'groups_events_create_limit', 0 );

			if ( $createLimit ) {
				static $count			=	array();

				$countId				=	md5( $user->get( 'id' ) . $group->get( 'id' ) );

				if ( ! isset( $count[$countId] ) ) {
					$query				=	'SELECT COUNT(*)'
										.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_events' )
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
	 * render frontend events group edit params
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

		$enableTooltip		=	cbTooltip( null, CBTxt::T( 'Optionally enable or disable usage of events. Group owner and group administrators are exempt from this configuration and can always schedule events. Note existing events will still be accessible.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['events']	=	moscomprofilerHTML::selectList( $listEnable, 'params[events]', 'class="form-control"' . $enableTooltip, 'value', 'text', (int) $this->input( 'post/params.events', $row->params()->get( 'events', 1 ), GetterInterface::INT ), 1, false, false );

		return HTML_groupjiveEventParams::showEventParams( $row, $input, $category, $user, $this );
	}

	/**
	 * render frontend events group notifications edit params
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

		$input['event_new']			=	moscomprofilerHTML::yesnoSelectList( 'params[event_new]', 'class="form-control"', (int) $this->input( 'post/params.event_new', $row->params()->get( 'event_new', $this->params->get( 'notifications_default_event_new', 0 ) ), GetterInterface::INT ), CBTxt::T( 'Notify' ), CBTxt::T( "Don't Notify" ), false );
		$input['event_edit']		=	moscomprofilerHTML::yesnoSelectList( 'params[event_edit]', 'class="form-control"', (int) $this->input( 'post/params.event_edit', $row->params()->get( 'event_edit', $this->params->get( 'notifications_default_event_edit', 0 ) ), GetterInterface::INT ), CBTxt::T( 'Notify' ), CBTxt::T( "Don't Notify" ), false );
		$input['event_approve']		=	moscomprofilerHTML::yesnoSelectList( 'params[event_approve]', 'class="form-control"', (int) $this->input( 'post/params.event_approve', $row->params()->get( 'event_approve', $this->params->get( 'notifications_default_event_approve', 0 ) ), GetterInterface::INT ), CBTxt::T( 'Notify' ), CBTxt::T( "Don't Notify" ), false );
		$input['event_attend']		=	moscomprofilerHTML::yesnoSelectList( 'params[event_attend]', 'class="form-control"', (int) $this->input( 'post/params.event_attend', $row->params()->get( 'event_attend', $this->params->get( 'notifications_default_event_attend', 0 ) ), GetterInterface::INT ), CBTxt::T( 'Notify' ), CBTxt::T( "Don't Notify" ), false );
		$input['event_unattend']	=	moscomprofilerHTML::yesnoSelectList( 'params[event_unattend]', 'class="form-control"', (int) $this->input( 'post/params.event_unattend', $row->params()->get( 'event_unattend', $this->params->get( 'notifications_default_event_unattend', 0 ) ), GetterInterface::INT ), CBTxt::T( 'Notify' ), CBTxt::T( "Don't Notify" ), false );

		return HTML_groupjiveEventNotifications::showEventNotifications( $row, $input, $group, $user, $this );
	}

	/**
	 * prepare frontend events render
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
	public function showEvents( &$return, &$group, &$users, &$invites, &$counters, &$buttons, &$menu, &$tabs, $user )
	{
		global $_CB_framework, $_CB_database;

		CBGroupJive::getTemplate( 'events', true, true, $this->element );

		$canModerate			=	( CBGroupJive::isModerator( $user->get( 'id' ) ) || ( CBGroupJive::getGroupStatus( $user, $group ) >= 2 ) );
		$limit					=	(int) $this->params->get( 'groups_events_limit', 15 );
		$limitstart				=	$_CB_framework->getUserStateFromRequest( 'gj_group_events_limitstart{com_comprofiler}', 'gj_group_events_limitstart' );
		$search					=	$_CB_framework->getUserStateFromRequest( 'gj_group_events_search{com_comprofiler}', 'gj_group_events_search' );
		$where					=	null;

		if ( $search && $this->params->get( 'groups_events_search', 1 ) ) {
			$where				.=	"\n AND ( e." . $_CB_database->NameQuote( 'title' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
								.	" OR e." . $_CB_database->NameQuote( 'event' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
								.	" OR e." . $_CB_database->NameQuote( 'address' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
								.	" OR e." . $_CB_database->NameQuote( 'location' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false ) . " )";
		}

		$searching				=	( $where ? true : false );

		$query					=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_events' ) . " AS e"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS cb"
								.	' ON cb.' . $_CB_database->NameQuote( 'id' ) . ' = e.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS j"
								.	' ON j.' . $_CB_database->NameQuote( 'id' ) . ' = cb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE e." . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' )
								.	"\n AND cb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND cb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND j." . $_CB_database->NameQuote( 'block' ) . " = 0";

		if ( ! $canModerate ) {
			$query				.=	"\n AND ( e." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
								.		' OR e.' . $_CB_database->NameQuote( 'published' ) . ' = 1 )';
		}

		$query					.=	$where;
		$_CB_database->setQuery( $query );
		$total					=	(int) $_CB_database->loadResult();

		if ( ( ! $total ) && ( ! $searching ) && ( ! CBGroupJive::canCreateGroupContent( $user, $group, 'events' ) ) ) {
			return null;
		}

		$pageNav				=	new cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( 'gj_group_events_' );

		$guests					=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_events_attendance' ) . " AS ea"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS eacb"
								.	' ON eacb.' . $_CB_database->NameQuote( 'id' ) . ' = ea.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS eaj"
								.	' ON eaj.' . $_CB_database->NameQuote( 'id' ) . ' = eacb.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE ea." . $_CB_database->NameQuote( 'event' ) . " = e." . $_CB_database->NameQuote( 'id' )
								.	"\n AND eacb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND eacb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND eaj." . $_CB_database->NameQuote( 'block' ) . " = 0";

		$now					=	Application::Database()->getUtcDateTime();

		$query					=	'SELECT e.*'
								.	', TIMESTAMPDIFF( SECOND, ' . $_CB_database->Quote( $now ) . ',e.' . $_CB_database->NameQuote( 'start' ) . ' ) AS _start_ordering'
								.	', TIMESTAMPDIFF( SECOND, ' . $_CB_database->Quote( $now ) . ', e.' . $_CB_database->NameQuote( 'end' ) . ' ) AS _end_ordering'
								.	', a.' . $_CB_database->NameQuote( 'id' ) . ' AS _attending'
								.	', ( ' . $guests . ' ) AS _guests'
								.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_plugin_events' ) . " AS e"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS cb"
								.	' ON cb.' . $_CB_database->NameQuote( 'id' ) . ' = e.' . $_CB_database->NameQuote( 'user_id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS j"
								.	' ON j.' . $_CB_database->NameQuote( 'id' ) . ' = cb.' . $_CB_database->NameQuote( 'id' )
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_plugin_events_attendance' ) . " AS a"
								.	' ON a.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
								.	' AND a.' . $_CB_database->NameQuote( 'event' ) . ' = e.' . $_CB_database->NameQuote( 'id' )
								.	"\n WHERE e." . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' )
								.	"\n AND cb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND cb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
								.	"\n AND j." . $_CB_database->NameQuote( 'block' ) . " = 0";

		if ( ! $canModerate ) {
			$query				.=	"\n AND ( e." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
								.		' OR e.' . $_CB_database->NameQuote( 'published' ) . ' = 1 )';
		}

		$query					.=	$where
								.	"\n ORDER BY IF( _start_ordering < 0 AND _end_ordering > 0, 1, IF( _start_ordering > 0, 2, 3 ) ), ABS( _start_ordering )";
		if ( $this->params->get( 'groups_events_paging', 1 ) ) {
			$_CB_database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
		} else {
			$_CB_database->setQuery( $query );
		}
		$rows					=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJiveEvents\Table\EventTable', array( $_CB_database ) );

		$input					=	array();

		$input['search']		=	'<input type="text" name="gj_group_events_search" value="' . htmlspecialchars( $search ) . '" onchange="document.gjGroupEventsForm.submit();" placeholder="' . htmlspecialchars( CBTxt::T( 'Search Events...' ) ) . '" class="form-control" />';

		CBGroupJiveEvents::getEvent( $rows );
		CBGroupJive::preFetchUsers( $rows );

		$group->set( '_events', $pageNav->total );

		return array(	'id'		=>	'events',
						'title'		=>	CBTxt::T( 'Events' ),
						'content'	=>	HTML_groupjiveEvents::showEvents( $rows, $pageNav, $searching, $input, $counters, $group, $user, $this )
					);
	}
}