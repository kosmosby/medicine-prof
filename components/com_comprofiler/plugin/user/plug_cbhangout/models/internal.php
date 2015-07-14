<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Input\Get;
use CBLib\Registry\GetterInterface;
use CBLib\Application\Application;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Database\Table\OrderedTable;
use CBLib\Language\CBTxt;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class cbhangoutBlogTable
 * Database Table class for internal articles
 */
class cbhangoutBlogTable extends OrderedTable
{
	public $id			=	null;
	public $user		=	null;
	public $title		=	null;
	public $hangout_intro	=	null;
	public $hangout_full	=	null;
	public $category	=	null;
	public $created		=	null;
	public $modified	=	null;
	public $access		=	null;
	public $published	=	null;
	public $ordering	=	null;
        public $price           =       null;

	/**
	 * Table constructor
	 *
	 * @param  DatabaseDriverInterface|null  $db
	 */
	public function __construct( $db = null )
	{
		parent::__construct( $db, '#__comprofiler_plugin_hangout', 'id' );
	}

	public function load( $id = null )
	{
		global $_CB_framework;

		$plugin		=	cbhangoutClass::getPlugin();
		$myId		=	$_CB_framework->myId();

		parent::load( (int) $id );

		if ( ! $this->get( 'id' ) ) {
			if ( ! $this->get( 'user' ) ) {
				$this->set( 'user', (int) $myId );
			}

			if ( $this->get( 'category' ) == '' ) {
				$this->set( 'category', $plugin->params->get( 'hangout_int_category_default', 'General' ) );
			}

			if ( ! $this->get( 'published' ) ) {
				$this->set( 'published', (int) $plugin->params->get( 'hangout_approval', 0 ) );
			}

			if ( ! $this->get( 'access' ) ) {
				$this->set( 'access', (int) $plugin->params->get( 'hangout_access_default', 1 ) );
			}
		}

		return true;
	}

	public function bind( $array, $ignore = '', $prefix = null )
	{
		global $_CB_framework;

		$bind				=	parent::bind( $array, $ignore, $prefix );

		if ( $bind ) {
			$plugin			=	cbhangoutClass::getPlugin();
			$myId			=	$_CB_framework->myId();
			$isModerator	=	Application::MyUser()->isGlobalModerator();

			$this->set( 'user', (int) Get::get( $array, 'user', $this->get( 'user', $myId ), GetterInterface::INT ) );
			$this->set( 'title', Get::get( $array, 'title', $this->get( 'title' ), GetterInterface::STRING ) );
			$this->set( 'hangout_intro', Get::get( $array, 'hangout_intro', $this->get( 'hangout_intro' ), GetterInterface::HTML ) );
			$this->set( 'hangout_full', Get::get( $array, 'hangout_full', $this->get( 'hangout_full' ), GetterInterface::HTML ) );
			$this->set( 'category', ( ( $plugin->params->get( 'hangout_category_config', 1 ) || $isModerator ) ? Get::get( $array, 'category', $this->get( 'category' ), GetterInterface::STRING ) : $this->get( 'category', $plugin->params->get( 'hangout_int_category_default', 'General' ) ) ) );
			$this->set( 'published', (int) ( ( ( ! $plugin->params->get( 'hangout_approval', 0 ) ) || $isModerator ) ? Get::get( $array, 'published', $this->get( 'published' ), GetterInterface::INT ) : $this->get( 'published', ( $isModerator || ( ! $plugin->params->get( 'hangout_approval', 0 ) ) ? 1 : 0 ) ) ) );
			$this->set( 'access', (int) ( ( $plugin->params->get( 'hangout_access_config', 1 ) || $isModerator ) ? Get::get( $array, 'access', $this->get( 'access' ), GetterInterface::INT ) : $this->get( 'access', $plugin->params->get( 'hangout_access_default', 1 ) ) ) );
			$this->set( 'ordering', (int) $this->get( 'ordering', 1 ) );
		}

		return $bind;
	}

	public function check()
	{
		if ( $this->get( 'title' ) == '' ) {
			$this->setError( CBTxt::T( 'Title not specified!' ) );

			return false;
		} elseif ( ! $this->get( 'user' ) ) {
			$this->setError( CBTxt::T( 'User not specified!' ) );

			return false;
		} elseif ( $this->get( 'user' ) && ( ! CBuser::getUserDataInstance( (int) $this->get( 'user' ) )->id ) ) {
			$this->setError( CBTxt::T( 'User specified does not exist!' ) );

			return false;
		} elseif ( $this->get( 'access' ) === '' ) {
			$this->setError( CBTxt::T( 'Access not specified!' ) );

			return false;
		} elseif ( $this->get( 'category' ) === '' ) {
			$this->setError( CBTxt::T( 'Category not specified!' ) );

			return false;
		} elseif ( ! in_array( $this->get( 'category' ), cbhangoutModel::getCategoriesList( true ) ) ) {
			$this->setError( CBTxt::T( 'Category not allowed!' ) );

			return false;
		}

		return true;
	}

	public function store( $updateNulls = false )
	{
		global $_CB_framework, $_PLUGINS;

		$new	=	( $this->get( 'id' ) ? false : true );

		if ( ! $new ) {
			$this->set( 'modified', $_CB_framework->getUTCDate() );

			$_PLUGINS->trigger( 'cbhangout_onBeforeUpdateBlog', array( &$this, &$this ) );
		} else {
			$this->set( 'created', $_CB_framework->getUTCDate() );

			$_PLUGINS->trigger( 'cbhangout_onBeforeCreateBlog', array( &$this, &$this ) );
		}

		if ( ! parent::store( $updateNulls ) ) {
			return false;
		}

		$this->updateOrder();

		if ( ! $new ) {
			$_PLUGINS->trigger( 'cbhangout_onAfterUpdateBlog', array( $this, $this ) );
		} else {
			$_PLUGINS->trigger( 'cbhangout_onAfterCreateBlog', array( $this, $this ) );
		}

		return true;
	}

	public function delete( $oid = null )
	{
		global $_PLUGINS;

		$_PLUGINS->trigger( 'cbhangout_onBeforeDeleteBlog', array( &$this, &$this ) );

		if ( ! parent::delete( $oid ) ) {
			return false;
		}

		$_PLUGINS->trigger( 'cbhangout_onAfterDeleteBlog', array( $this, $this ) );

		$this->updateOrder();

		return true;
	}

	public function getCategory( )
	{
		return new cbhangoutCategoryTable( $this->_db );
	}
}

/**
 * Class cbhangoutCategoryTable
 * Categories Table model for CB Blogs
 */
class cbhangoutCategoryTable extends OrderedTable
{
	public function __construct( $db = null )
	{
		parent::__construct( $db, '', 'id' );
	}

	public function load( $id = null )
	{
		return true;
	}
}

/**
 * Class cbhangoutModel
 * Model for CB Blogs
 */
class cbhangoutModel
{
	/**
	 * @param  string       $where
	 * @param  UserTable    $viewer
	 * @param  UserTable    $user
	 * @param  PluginTable  $plugin
	 * @return int
	 */
	static public function getHangoutTotal( $where, $viewer, $user, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		global $_CB_database;

		$query		=	'SELECT COUNT(*)'
					.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_hangout' ) . " AS a"
					.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS c"
					.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'user' )
					.	"\n WHERE a." . $_CB_database->NameQuote( 'user' ) . " = " . (int) $user->get( 'id' )
					.	( ( $viewer->get( 'id' ) != $user->get( 'id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ? "\n AND a." . $_CB_database->NameQuote( 'published' ) . " = 1" : null )
					.	"\n AND a." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
					.	$where;

		$_CB_database->setQuery( $query );

		$total		=	$_CB_database->loadResult();

		return $total;
	}

	/**
	 * @param  int[]             $paging
	 * @param  string            $where
	 * @param  UserTable         $viewer
	 * @param  UserTable         $user
	 * @param  PluginTable       $plugin
	 * @return cbhangoutBlogTable[]
	 */
	static public function getHangout( $paging, $where, $viewer, $user, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		global $_CB_database;

		$query		=	'SELECT a.*'
					.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_hangout' ) . " AS a"
					.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS c"
					.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'user' )
					.	"\n WHERE a." . $_CB_database->NameQuote( 'user' ) . " = " . (int) $user->get( 'id' )
					.	( ( $viewer->get( 'id' ) != $user->get( 'id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ? "\n AND a." . $_CB_database->NameQuote( 'published' ) . " = 1" : null )
					.	"\n AND a." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
					.	$where
					.	"\n ORDER BY a." . $_CB_database->NameQuote( 'created' ) . " DESC";

		if ( $paging ) {
			$_CB_database->setQuery( $query, $paging[0], $paging[1] );
		} else {
			$_CB_database->setQuery( $query );
		}

		$hangout		=	$_CB_database->loadObjectList( null, 'cbhangoutBlogTable', array( $_CB_database ) );

		return $hangout;
	}

	/**
	 * @param  boolean  $raw
	 * @return array
	 */
	static public function getCategoriesList( $raw = false )
	{
		static $cache				=	null;

		if ( ! isset( $cache ) ) {
			$plugin					=	cbhangoutClass::getPlugin();
			$categories				=	explode( ',', $plugin->params->get( 'hangout_categories', 'General,Movies,Music,Games,Sports' ) );
			$cache					=	array();

			if ( $categories ) foreach ( $categories as $category ) {
				$cache[]			=	moscomprofilerHTML::makeOption( $category, CBTxt::T( $category ) );
			}
		}

		$rows						=	$cache;

		if ( $rows ) {
			if ( $raw === true ) {
				$categories			=	array();

				foreach ( $rows as $row ) {
					$categories[]	=	$row->value;
				}

				$rows				=	$categories;
			}
		} else {
			$rows					=	array();
		}

		return $rows;
	}

	/**
	 * @param  int|OrderedTable  $row
	 * @param  boolean           $htmlspecialchars
	 * @return string
	 */
	static public function getUrl( $row, $htmlspecialchars = true )
	{
		if ( is_object( $row ) ) {
			$id		=	$row->get( 'id' );
		} else {
			$id		=	$row;
		}

		return cbSef( 'index.php?option=com_comprofiler&view=pluginclass&plugin=cbhangout&action=hangout&func=show&id=' . (int) $id, $htmlspecialchars );
	}
}
