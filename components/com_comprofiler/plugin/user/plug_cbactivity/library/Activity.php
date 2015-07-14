<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Plugin\Activity;

use CBLib\Application\Application;
use CBLib\Registry\GetterInterface;
use CB\Database\Table\UserTable;
use CB\Plugin\Activity\Table\ActivityTable;

defined('CBLIB') or die();

class Activity extends StreamDirection implements ActivityInterface
{
	/** @var string $endpoint */
	protected $endpoint		=	'activity';

	/**
	 * Constructor for stream object
	 *
	 * @param null|string    $source
	 * @param null|UserTable $user
	 * @param null|int       $direction 0: down, 1: up
	 */
	public function __construct( $source = null, $user = null, $direction = null )
	{
		global $_PLUGINS;

		parent::__construct( $source, $user, $direction );

		static $params		=	null;

		if ( ! $params ) {
			$plugin			=	$_PLUGINS->getLoadedPlugin( 'user', 'cbactivity' );
			$params			=	$_PLUGINS->getPluginParams( $plugin );
		}

		// Activity
		$this->set( 'paging', (int) $params->get( 'activity_paging', 1 ) );
		$this->set( 'limit', (int) $params->get( 'activity_limit', 30 ) );
		$this->set( 'create_access', (int) $params->get( 'activity_create_access', 2 ) );
		$this->set( 'message_limit', (int) $params->get( 'activity_message_limit', 400 ) );

		// Actions
		$this->set( 'actions', (int) $params->get( 'activity_actions', 1 ) );
		$this->set( 'actions_message_limit', (int) $params->get( 'activity_actions_message_limit', 100 ) );

		// Locations
		$this->set( 'locations', (int) $params->get( 'activity_locations', 1 ) );
		$this->set( 'locations_address_limit', (int) $params->get( 'activity_locations_address_limit', 200 ) );

		// Links
		$this->set( 'links', (int) $params->get( 'activity_links', 1 ) );
		$this->set( 'links_link_limit', (int) $params->get( 'activity_links_link_limit', 5 ) );

		// Tags
		$this->set( 'tags', (int) $params->get( 'activity_tags', 1 ) );

		// Comments
		$this->set( 'comments', (int) $params->get( 'activity_comments', 1 ) );
		$this->set( 'comments_paging', (int) $params->get( 'activity_comments_paging', 1 ) );
		$this->set( 'comments_limit', (int) $params->get( 'activity_comments_limit', 4 ) );
		$this->set( 'comments_create_access', (int) $params->get( 'activity_comments_create_access', 2 ) );
		$this->set( 'comments_message_limit', (int) $params->get( 'activity_comments_message_limit', 400 ) );

		// Comment Replies
		$this->set( 'comments_replies', (int) $params->get( 'activity_comments_replies', 0 ) );
		$this->set( 'comments_replies_paging', (int) $params->get( 'activity_comments_replies_paging', 1 ) );
		$this->set( 'comments_replies_limit', (int) $params->get( 'activity_comments_replies_limit', 4 ) );
		$this->set( 'comments_replies_create_access', (int) $params->get( 'activity_comments_replies_create_access', 2 ) );
		$this->set( 'comments_replies_message_limit', (int) $params->get( 'activity_comments_replies_message_limit', 400 ) );
	}

	/**
	 * Creates or modifies stream data
	 *
	 * @param array     $data
	 * @param int|array $keys
	 * @return bool
	 */
	public function push( $data = array(), $keys = null )
	{
		$row					=	new ActivityTable();

		if ( isset( $data['id'] ) ) {
			if ( $keys === null ) {
				$keys			=	(int) $data['id'];
			}

			unset( $data['id'] );
		}

		if ( ( $keys === null ) && $this->get( 'id' ) ) {
			$keys				=	(int) $this->get( 'id', null, GetterInterface::INT );
		}

		if ( $keys ) {
			$row->load( $keys );
		}

		if ( ( ! $row->get( 'user_id' ) ) && ( ! isset( $data['user_id'] ) ) ) {
			$data['user_id']	=	(int) $this->user->get( 'id' );
		}

		if ( ( ! $row->get( 'type' ) ) && ( ! isset( $data['type'] ) ) && $this->get( 'type' ) ) {
			$data['type']		=	$this->get( 'type', null, GetterInterface::STRING );
		}

		if ( ( ! $row->get( 'subtype' ) ) && ( ! isset( $data['subtype'] ) ) && $this->get( 'subtype' ) ) {
			$data['subtype']	=	$this->get( 'subtype', null, GetterInterface::STRING );
		}

		if ( ( ! $row->get( 'item' ) ) && ( ! isset( $data['item'] ) ) && $this->get( 'item' ) ) {
			$data['item']		=	$this->get( 'item', null, GetterInterface::STRING );
		}

		if ( ( ! $row->get( 'parent' ) ) && ( ! isset( $data['parent'] ) ) && $this->get( 'parent' ) ) {
			$data['parent']		=	$this->get( 'parent', null, GetterInterface::STRING );
		}

		if ( isset( $data['params'] ) ) {
			$params				=	$row->params();

			$params->load( $data['params'] );

			$data['params']		=	$params->asJson();
		}

		if ( ! $row->bind( $data ) ) {
			return false;
		}

		if ( $row->getError() || ( ! $row->check() ) ) {
			return false;
		}

		if ( $row->getError() || ( ! $row->store() ) ) {
			return false;
		}

		$this->resetCount		=	true;
		$this->resetSelect		=	true;

		return true;
	}

	/**
	 * Removes data from stream
	 *
	 * @param int|array $keys
	 * @return bool
	 */
	public function remove( $keys = null )
	{
		if ( ( $keys === null ) && $this->get( 'id' ) ) {
			$keys			=	(int) $this->get( 'id', null, GetterInterface::INT );
		}

		$row				=	new ActivityTable();

		$row->load( $keys );

		if ( ! $row->get( 'id' ) ) {
			return false;
		}

		if ( ! $row->delete() ) {
			return false;
		}

		$this->resetCount	=	true;
		$this->resetSelect	=	true;

		return true;
	}

	/**
	 * Retrieves activity stream data rows or row count
	 *
	 * @param bool  $count
	 * @param array $where
	 * @param array $join
	 * @return ActivityTable[]|int
	 */
	public function data( $count = false, $where = array(), $join = array() )
	{
		global $_CB_database, $_PLUGINS;

		static $cache					=	array();

		$whereCache						=	$where;
		$joinCache						=	$join;
		$isSelf							=	( Application::MyUser()->getUserId() == $this->user->get( 'id' ) );

		if ( $count ) {
			$select						=	'COUNT( a.' . $_CB_database->NameQuote( 'id' ) . ' )';
		} else {
			$select						=	'a.*';
		}

		$_PLUGINS->trigger( 'activity_onQueryActivity', array( $count, &$select, &$where, &$join, &$this ) );

		$query							=	'SELECT ' . $select
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_activity' ) . " AS a"
										.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler_plugin_activity_hidden' ) . " AS b"
										.	' ON b.' . $_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( 'activity' )
										.	' AND b.' . $_CB_database->NameQuote( 'item' ) . ' = a.' . $_CB_database->NameQuote( 'id' );

		if ( $this->source != 'hidden' ) {
			$query						.=	' AND b.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) Application::MyUser()->getUserId();
		}

		$query							.=	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
										.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'user_id' )
										.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS d"
										.	' ON d.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' );

		if ( ( $this->source == 'profile' ) && $isSelf ) {
			$query						.=	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler_members' ) . " AS e"
										.	' ON e.' . $_CB_database->NameQuote( 'pending' ) . ' = 0'
										.	' AND e.' . $_CB_database->NameQuote( 'accepted' ) . ' = 1'
										.	' AND e.' . $_CB_database->NameQuote( 'memberid' ) . ' = a.' . $_CB_database->NameQuote( 'user_id' )
										.	' AND e.' . $_CB_database->NameQuote( 'referenceid' ) . ' = ' . (int) $this->user->get( 'id' );
		}

		$query							.=	( $join ? "\n " . implode( "\n ", $join ) : null );

		if ( $this->source == 'hidden' ) {
			$query						.=	"\n WHERE b." . $_CB_database->NameQuote( 'id' ) . " IS NOT NULL"
										.	"\n AND b." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) Application::MyUser()->getUserId();
		} else {
			$query						.=	"\n WHERE b." . $_CB_database->NameQuote( 'id' ) . " IS NULL";
		}

		$query							.=	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
										.	"\n AND c." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
										.	"\n AND d." . $_CB_database->NameQuote( 'block' ) . " = 0";

		if ( $this->source == 'profile' ) {
			if ( $isSelf ) {
				$query					.=	"\n AND ( a." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $this->user->get( 'id' ) . " OR e." . $_CB_database->NameQuote( 'memberid' ) . " IS NOT NULL )";
			} else {
				$query					.=	"\n AND ( a." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $this->user->get( 'id' ) . " OR a." . $_CB_database->NameQuote( 'parent' ) . " = " . (int) $this->user->get( 'id' ) . " )";
			}
		}

		if ( $this->get( 'id' ) ) {
			$query						.=	"\n AND a." . $_CB_database->NameQuote( 'id' ) . " = " . (int) $this->get( 'id', null, GetterInterface::INT );
		}

		if ( $this->get( 'type' ) ) {
			$query						.=	"\n AND a." . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $this->get( 'type', null, GetterInterface::STRING ) );
		}

		if ( $this->get( 'subtype' ) ) {
			$query						.=	"\n AND a." . $_CB_database->NameQuote( 'subtype' ) . " = " . $_CB_database->Quote( $this->get( 'subtype', null, GetterInterface::STRING ) );
		}

		if ( $this->get( 'item' ) ) {
			$query						.=	"\n AND a." . $_CB_database->NameQuote( 'item' ) . " = " . $_CB_database->Quote( $this->get( 'item', null, GetterInterface::STRING ) );
		}

		if ( $this->get( 'parent' ) ) {
			$query						.=	"\n AND a." . $_CB_database->NameQuote( 'parent' ) . " = " . $_CB_database->Quote( $this->get( 'parent', null, GetterInterface::STRING ) );
		}

		if ( $this->get( 'filter' ) ) {
			$query						.=	"\n AND ( a." . $_CB_database->NameQuote( 'title' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $this->get( 'filter', null, GetterInterface::STRING ), true ) . '%', false )
										.	" OR " . "a." . $_CB_database->NameQuote( 'message' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $this->get( 'filter', null, GetterInterface::STRING ), true ) . '%', false ) . " )";
		}

		$query							.=	( $where ? "\n AND " . explode( "\n AND ", $where ) : null )
										.	( ! $count ? "\n ORDER BY a." . $_CB_database->NameQuote( 'date' ) . " DESC" : null );

		$cacheId						=	md5( $query . ( $count ? 'count' : (int) $this->get( 'limitstart', null, GetterInterface::INT ) . (int) $this->get( 'limit', null, GetterInterface::INT ) ) );

		if ( ( ! isset( $cache[$cacheId] ) ) || ( ( $count && $this->resetCount ) || $this->resetSelect ) ) {
			if ( $count ) {
				$this->resetCount		=	false;

				$_CB_database->setQuery( $query );

				$cache[$cacheId]		=	(int) $_CB_database->loadResult();
			} else {
				$this->resetSelect		=	false;

				if ( $this->get( 'limit' ) ) {
					$_CB_database->setQuery( $query, (int) $this->get( 'limitstart', null, GetterInterface::INT ), (int) $this->get( 'limit', null, GetterInterface::INT ) );
				} else {
					$_CB_database->setQuery( $query );
				}

				$rows					=	$_CB_database->loadObjectList( null, '\CB\Plugin\Activity\Table\ActivityTable', array( $_CB_database ) );
				$rowsCount				=	count( $rows );

				$_PLUGINS->trigger( 'activity_onLoadActivity', array( &$rows, $this ) );

				if ( $this->get( 'limit' ) && $rowsCount && ( ! count( $rows ) ) ) {
					$directionCache		=	$this->direction;

					$this->direction	=	0;

					$this->set( 'limitstart', ( (int) $this->get( 'limitstart', null, GetterInterface::INT ) + (int) $this->get( 'limit', null, GetterInterface::INT ) ) );

					$cache[$cacheId]	=	$this->data( $whereCache, $joinCache );

					$this->direction	=	$directionCache;
				} else {
					$cache[$cacheId]	=	$rows;
				}
			}
		}

		$rows							=	$cache[$cacheId];

		if ( $this->direction && ( ! $count ) ) {
			$rows						=	array_reverse( $rows );
		}

		return $rows;
	}
}