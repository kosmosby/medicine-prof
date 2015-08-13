<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Plugin\Activity;

use CB\Database\Table\UserTable;

defined('CBLIB') or die();

abstract class StreamDirection extends Stream implements StreamDirectionInterface
{
	/** @var int $direction */
	protected $direction	=	0;

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

		$_PLUGINS->loadPluginGroup( 'user' );

		if ( $source === null ) {
			$source			=	'stream';
		}

		if ( $user === null ) {
			$user			=	\CBuser::getMyUserDataInstance();
		}

		if ( $direction === null ) {
			$direction		=	0;
		}

		$this->source		=	$source;
		$this->user			=	$user;
		$this->direction	=	$direction;

		if ( ! $this->id ) {
			$this->id		=	uniqid();
		}
	}

	/**
	 * Gets or sets the stream display direction
	 *
	 * @param int|null $direction
	 * @return int|null
	 */
	public function direction( $direction = null )
	{
		if ( $direction ) {
			$this->direction	=	$direction;
		}

		return $this->direction;
	}

	/**
	 * Returns an array of all current params
	 *
	 * @return array
	 */
	public function asArray()
	{
		$params					=	parent::asArray();

		$params['direction']	=	(int) $this->direction;

		return $params;
	}
}