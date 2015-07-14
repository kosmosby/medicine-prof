<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Plugin\Activity;

use CBLib\Registry\GetterInterface;
use CB\Plugin\Activity\Table\TagTable;

defined('CBLIB') or die();

class Tags extends Stream implements TagsInterface
{
	/** @var string $endpoint */
	protected $endpoint		=	'tags';

	/**
	 * Creates or modifies stream data
	 *
	 * @param array     $data
	 * @param int|array $keys
	 * @return bool
	 */
	public function push( $data = array(), $keys = null )
	{
		$row					=	new TagTable();

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

		$row				=	new TagTable();

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
	 * Retrieves tag stream data rows or row count
	 *
	 * @param bool  $count
	 * @param array $where
	 * @param array $join
	 * @return TagTable[]|int
	 */
	public function data( $count = false, $where = array(), $join = array() )
	{
		global $_CB_database, $_PLUGINS;

		static $cache					=	array();

		$_PLUGINS->trigger( 'activity_onQueryTags', array( $count, &$where, &$join, &$this ) );

		$useWhere						=	true;

		$query							=	'SELECT ' . ( $count ? 'COUNT( a.' . $_CB_database->NameQuote( 'id' ) . ' )' : 'a.*' )
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_activity_tags' ) . " AS a"
										.	( $join ? "\n " . implode( "\n ", $join ) : null );

		if ( $this->get( 'id' ) ) {
			$query						.=	( $useWhere ? "\n WHERE " : "\n AND " ) . "a." . $_CB_database->NameQuote( 'id' ) . " = " . (int) $this->get( 'id', null, GetterInterface::INT );

			$useWhere					=	false;
		}

		if ( $this->get( 'type' ) ) {
			$query						.=	( $useWhere ? "\n WHERE " : "\n AND " ) . "a." . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( $this->get( 'type', null, GetterInterface::STRING ) );

			$useWhere					=	false;
		}

		if ( $this->get( 'subtype' ) ) {
			$query						.=	( $useWhere ? "\n WHERE " : "\n AND " ) . "a." . $_CB_database->NameQuote( 'subtype' ) . " = " . $_CB_database->Quote( $this->get( 'subtype', null, GetterInterface::STRING ) );

			$useWhere					=	false;
		}

		if ( $this->get( 'item' ) ) {
			$query						.=	( $useWhere ? "\n WHERE " : "\n AND " ) . "a." . $_CB_database->NameQuote( 'item' ) . " = " . $_CB_database->Quote( $this->get( 'item', null, GetterInterface::STRING ) );

			$useWhere					=	false;
		}

		if ( $this->get( 'parent' ) ) {
			$query						.=	( $useWhere ? "\n WHERE " : "\n AND " ) . "a." . $_CB_database->NameQuote( 'parent' ) . " = " . $_CB_database->Quote( $this->get( 'parent', null, GetterInterface::STRING ) );

			$useWhere					=	false;
		}

		$query							.=	( $where ? ( $useWhere ? "\n WHERE " : "\n AND " ) . explode( "\n AND ", $where ) : null )
										.	( ! $count ? "\n ORDER BY a." . $_CB_database->NameQuote( 'date' ) . " ASC" : null );

		$cacheId						=	md5( $query . ( $count ? 'count' : null ) );

		if ( ( ! isset( $cache[$cacheId] ) ) || ( ( $count && $this->resetCount ) || $this->resetSelect ) ) {
			if ( $count ) {
				$this->resetCount		=	false;

				$_CB_database->setQuery( $query );

				$cache[$cacheId]		=	(int) $_CB_database->loadResult();
			} else {
				$this->resetSelect		=	false;

				$_CB_database->setQuery( $query );

				$rows					=	$_CB_database->loadObjectList( null, '\CB\Plugin\Activity\Table\TagTable', array( $_CB_database ) );

				$_PLUGINS->trigger( 'activity_onLoadTags', array( &$rows, $this ) );

				$cache[$cacheId]		=	$rows;
			}
		}

		return $cache[$cacheId];
	}
}