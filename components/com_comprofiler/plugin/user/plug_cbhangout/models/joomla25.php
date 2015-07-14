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
use CBLib\Database\Table\OrderedTable;
use CBLib\Language\CBTxt;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class cbhangoutBlogTable extends OrderedTable
{

	public function __construct( $db = null )
	{
		parent::__construct( $db, '#__content', 'id' );
	}

	private function map()
	{
		$map	=	array( 'user' => 'created_by', 'published' => 'state', 'category' => 'catid', 'hangout_intro' => 'introtext', 'hangout_full' => 'fulltext' );

		foreach ( $map as $to => $from ) {
			$this->set( $to, $this->get( $from ) );
		}
	}

	public function load( $id = null )
	{
		global $_CB_framework;

		$plugin			=	cbhangoutClass::getPlugin();
		$myId			=	$_CB_framework->myId();

		$key			=	$this->get( '_tbl_key' );

		if ( $id !== null ) {
			$this->set( $key, $id );
		}

		$id				=	$this->get( $key );

		if ( $id === null ) {
			return false;
		}

		$article		=	JTable::getInstance( 'content' );

		$article->load( (int) $id );

		foreach ( $article as $k => $v ) {
			$this->set( $k, $v );
		}

		if ( ! $article->get( 'id' ) ) {
			if ( ! $this->get( 'created_by' ) ) {
				$this->set( 'created_by', (int) $myId );
			}

			if ( ! $this->get( 'catid' ) ) {
				$this->set( 'catid', (int) $plugin->params->get( 'hangout_j_category_default', null ) );
			}

			if ( ! $this->get( 'state' ) ) {
				$this->set( 'state', (int) $plugin->params->get( 'hangout_approval', 0 ) );
			}

			if ( ! $this->get( 'access' ) ) {
				$this->set( 'access', (int) $plugin->params->get( 'hangout_access_default', 1 ) );
			}
		}

		$this->map();

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

			$this->set( 'created_by', (int) Get::get( $array, 'user', $this->get( 'created_by', $myId ) ), GetterInterface::INT );
			$this->set( 'title', Get::get( $array, 'title', $this->get( 'title' ), GetterInterface::STRING ) );
			$this->set( 'introtext', Get::get( $array, 'hangout_intro', $this->get( 'introtext' ), GetterInterface::HTML ) );
			$this->set( 'fulltext', Get::get( $array, 'hangout_full', $this->get( 'fulltext' ), GetterInterface::HTML ) );
			$this->set( 'catid', (int) ( ( $plugin->params->get( 'hangout_category_config', 1 ) || $isModerator ) ? Get::get( $array, 'category', $this->get( 'catid' ), GetterInterface::INT ) : $this->get( 'catid', $plugin->params->get( 'hangout_j_category_default', null ) ) ) );
			$this->set( 'state', (int) ( ( ( ! $plugin->params->get( 'hangout_approval', 0 ) ) || $isModerator ) ? Get::get( $array, 'published', $this->get( 'state' ), GetterInterface::INT ) : $this->get( 'state', ( $isModerator || ( ! $plugin->params->get( 'hangout_approval', 0 ) ) ? 1 : 0 ) ) ) );
			$this->set( 'access', (int) ( ( $plugin->params->get( 'hangout_access_config', 1 ) || $isModerator ) ? Get::get( $array, 'access', $this->get( 'access' ), GetterInterface::INT ) : $this->get( 'access', $plugin->params->get( 'hangout_access_default', 1 ) ) ) );
			$this->set( 'ordering', (int) $this->get( 'ordering', 1 ) );

			$this->map();
		}

		return $bind;
	}

	public function check( )
	{

		if ( $this->get( 'title' ) == '' ) {
			$this->setError( CBTxt::T( 'Title not specified!' ) );

			return false;
		} elseif ( ! $this->get( 'created_by' ) ) {
			$this->setError( CBTxt::T( 'User not specified!' ) );

			return false;
		} elseif ( $this->get( 'created_by' ) && ( ! CBuser::getUserDataInstance( (int) $this->get( 'created_by' ) )->id ) ) {
			$this->setError( CBTxt::T( 'User specified does not exist!' ) );

			return false;
		} elseif ( $this->get( 'access' ) === '' ) {
			$this->setError( CBTxt::T( 'Access not specified!' ) );

			return false;
		} elseif ( ! $this->get( 'catid' ) ) {
			$this->setError( CBTxt::T( 'Category not specified!' ) );

			return false;
		} elseif ( ! in_array( $this->get( 'catid' ), cbhangoutModel::getCategoriesList( true ) ) ) {
			$this->setError( CBTxt::T( 'Category not allowed!' ) );

			return false;
		}

		return true;
	}

	public function store( $updateNulls = false )
	{
		global $_CB_framework, $_PLUGINS;

		$plugin				=	cbhangoutClass::getPlugin();
		$user				=	CBuser::getMyUserDataInstance();

		$id					=	$this->get( $this->get( '_tbl_key' ) );
		$article			=	JTable::getInstance( 'content' );

		if ( ! $article->load( (int) $id ) ) {
			return false;
		}

		if ( ! $article->bind( (array) $this ) ) {
			return false;
		}

		$new				=	( (int) $id ? false : true );
		$table				=	JTable::getInstance( 'content' );

		$article->set( 'alias', $this->getTitleAlias( $article->get( 'title' ) ) );

		$alias				=	$article->get( 'alias' );

		while ( $table->load( array( 'alias' => $alias, 'catid' => $article->get( 'catid' ) ) ) ) {
			$matches		=	null;

			if ( preg_match( '#-(\d+)$#', $alias, $matches ) ) {
				$alias		=	preg_replace( '#-(\d+)$#', '-' . ( $matches[1] + 1 ) . '', $alias );
			} else {
				$alias		.=	'-2';
			}
		}

		$article->set( 'alias', $alias );

		if ( $article->get( 'images' ) == '' ) {
			$article->set( 'images', '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}' );
		}

		if ( $article->get( 'urls' ) == '' ) {
			$article->set( 'urls', '{"urla":null,"urlatext":"","targeta":"","urlb":null,"urlbtext":"","targetb":"","urlc":null,"urlctext":"","targetc":""}' );
		}

		if ( $article->get( 'attribs' ) == '' ) {
			$article->set( 'attribs', '{"show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}' );
		}

		if ( $article->get( 'metadata' ) == '' ) {
			$article->set( 'metadata', '{"robots":"","author":"","rights":"","xreference":"","tags":""}' );
		}

		if ( $article->get( 'language' ) == '' ) {
			$article->set( 'language', '*' );
		}

		if ( ! $new ) {
			$article->set( 'modified', $_CB_framework->getUTCDate() );
			$article->set( 'modified_by', (int) $user->get( 'id' ) );

			$_PLUGINS->trigger( 'cbhangout_onBeforeUpdateBlog', array( &$this, &$article, $user, $plugin ) );
		} else {
			$article->set( 'created', $_CB_framework->getUTCDate() );

			$_PLUGINS->trigger( 'cbhangout_onBeforeCreateBlog', array( &$this, &$article, $user, $plugin ) );
		}

		if ( ! $article->store( $updateNulls ) ) {
			return false;
		}

		$article->reorder( $this->_db->NameQuote( 'catid' ) . ' = ' . (int) $article->get( 'catid' ) );

		if ( ! $new ) {
			$_PLUGINS->trigger( 'cbhangout_onAfterUpdateBlog', array( $this, $article, $user, $plugin ) );
		} else {
			$_PLUGINS->trigger( 'cbhangout_onAfterCreateBlog', array( $this, $article, $user, $plugin ) );
		}

		return true;
	}

	/**
	 * Autogenerates an URL-compatible title-alias for a title
	 *
	 * @param  string  $title  Title
	 * @return string          URL-compatible Alias corresponding to title
	 */
	private static function getTitleAlias( $title )
	{
		$alias	=	str_replace( '-', ' ', $title );
		$alias	=	trim( cbIsoUtf_strtolower( $alias ) );
		$alias	=	preg_replace( '/(\s|[^A-Za-z0-9\-])+/', '-', $alias );
		$alias	=	trim( $alias, '-' );

		return $alias;
	}

	public function delete( $id = null )
	{
		global $_PLUGINS;

		$plugin		=	cbhangoutClass::getPlugin();
		$user		=	CBuser::getMyUserDataInstance();

		$key		=	$this->get( '_tbl_key' );

		if ( $id !== null ) {
			$this->set( $key, $id );
		}

		$id			=	$this->get( $key );
		$article	=	JTable::getInstance( 'content' );

		if ( ! $article->load( (int) $id ) ) {
			return false;
		}

		$_PLUGINS->trigger( 'cbhangout_onBeforeDeleteBlog', array( &$this, &$article, $user, $plugin ) );

		if ( ! $article->delete( (int) $id ) ) {
			return false;
		}

		$_PLUGINS->trigger( 'cbhangout_onAfterDeleteBlog', array( $this, $article, $user, $plugin ) );

		$article->reorder( $this->_db->NameQuote( 'catid' ) . ' = ' . (int) $article->get( 'catid' ) );

		return true;
	}

	public function getCategory( )
	{
		$category	=	new cbhangoutCategoryTable( $this->_db );

		if ( ! $category->load( (int) $this->get( 'catid' ) ) ) {
			return false;
		}

		return $category;
	}
}

class cbhangoutCategoryTable extends OrderedTable
{

	public function __construct( $db = null )
	{
		parent::__construct( $db, '#__categories', 'id' );
	}

	public function load( $id = null )
	{
		$key			=	$this->get( '_tbl_key' );

		if ( $id !== null ) {
			$this->set( $key, $id );
		}

		$id				=	$this->get( $key );

		if ( $id === null ) {
			return false;
		}

		$category		=	JTable::getInstance( 'category' );

		if ( $category->load( (int) $id ) ) {
			foreach ( $category as $k => $v ) {
				$this->set( $k, $v );
			}

			return true;
		}

		return false;
	}
}

class cbhangoutModel
{
	/**
	 * @param  string       $where
	 * @param  UserTable    $viewer
	 * @param  UserTable    $user
	 * @param  PluginTable  $plugin
	 * @return int
	 */
	static public function getHangoutTotal( $where, $viewer, $user, $plugin )
	{
		global $_CB_database;

		$section	=	$plugin->params->get( 'hangout_j_section', null );

		$query		=	'SELECT COUNT(*)'
					.	"\n FROM " . $_CB_database->NameQuote( '#__content' ) . " AS a"
					.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__categories' ) . " AS b"
					.	' ON b.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'catid' )
					.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS c"
					.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'created_by' );

		if ( $section ) {
			$query	.=	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__categories' ) . " AS d"
					.	' ON d.' . $_CB_database->NameQuote( 'id' ) . ' = ' . (int) $section;
		}

		$query		.=	"\n WHERE b." . $_CB_database->NameQuote( 'extension' ) . " = " . $_CB_database->Quote( 'com_content' );

		if ( $section ) {
			$query	.=	"\n AND b." . $_CB_database->NameQuote( 'lft' ) . " BETWEEN ( d." . $_CB_database->NameQuote( 'lft' ) . " + 1 ) AND ( d." . $_CB_database->NameQuote( 'rgt' ) . " - 1 )"
					.	"\n AND d." . $_CB_database->NameQuote( 'extension' ) . " = " . $_CB_database->Quote( 'com_content' );
		}

		$query		.=	"\n AND a." . $_CB_database->NameQuote( 'created_by' ) . " = " . (int) $user->get( 'id' )
					.	( ( $viewer->get( 'id' ) != $user->get( 'id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ? "\n AND a." . $_CB_database->NameQuote( 'state' ) . " = 1" : null )
					.	"\n AND a." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
					.	"\n AND b." . $_CB_database->NameQuote( 'published' ) . " = 1"
					.	"\n AND b." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() );

		$query		.=	$where;

		$_CB_database->setQuery( $query );

		return $_CB_database->loadResult();
	}

	/**
	 * @param  int[]             $paging
	 * @param  string            $where
	 * @param  UserTable         $viewer
	 * @param  UserTable         $user
	 * @param  PluginTable       $plugin
	 * @return cbhangoutBlogTable[]
	 */
	static public function getHangout( $paging, $where, $viewer, $user, $plugin )
	{
		global $_CB_database;

		$section	=	$plugin->params->get( 'hangout_j_section', null );

		$query		=	'SELECT a.*'
					.	', a.' . $_CB_database->NameQuote( 'created_by' ) . ' AS user'
					.	', a.' . $_CB_database->NameQuote( 'introtext' ) . ' AS hangout_intro'
					.	', a.' . $_CB_database->NameQuote( 'fulltext' ) . ' AS hangout_full'
					.	', a.' . $_CB_database->NameQuote( 'state' ) . ' AS published'
					.	', b.' . $_CB_database->NameQuote( 'title' ) . ' AS category'
					.	', b.' . $_CB_database->NameQuote( 'published' ) . ' AS category_published'
					.	', b.' . $_CB_database->NameQuote( 'alias' ) . ' AS category_alias'
					.	"\n FROM " . $_CB_database->NameQuote( '#__content' ) . " AS a"
					.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__categories' ) . " AS b"
					.	' ON b.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'catid' )
					.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS c"
					.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'created_by' );

		if ( $section ) {
			$query	.=	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__categories' ) . " AS d"
					.	' ON d.' . $_CB_database->NameQuote( 'id' ) . ' = ' . (int) $section;
		}

		$query		.=	"\n WHERE b." . $_CB_database->NameQuote( 'extension' ) . " = " . $_CB_database->Quote( 'com_content' );

		if ( $section ) {
			$query	.=	"\n AND b." . $_CB_database->NameQuote( 'lft' ) . " BETWEEN ( d." . $_CB_database->NameQuote( 'lft' ) . " + 1 ) AND ( d." . $_CB_database->NameQuote( 'rgt' ) . " - 1 )"
					.	"\n AND d." . $_CB_database->NameQuote( 'extension' ) . " = " . $_CB_database->Quote( 'com_content' );
		}

		$query		.=	"\n AND a." . $_CB_database->NameQuote( 'created_by' ) . " = " . (int) $user->get( 'id' )
					.	( ( $viewer->get( 'id' ) != $user->get( 'id' ) ) && ( ! Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator() ) ? "\n AND a." . $_CB_database->NameQuote( 'state' ) . " = 1" : null )
					.	"\n AND a." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
					.	"\n AND b." . $_CB_database->NameQuote( 'published' ) . " = 1"
					.	"\n AND b." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
					.	$where
					.	"\n ORDER BY a." . $_CB_database->NameQuote( 'created' ) . " DESC";

		if ( $paging ) {
			$_CB_database->setQuery( $query, $paging[0], $paging[1] );
		} else {
			$_CB_database->setQuery( $query );
		}

		return $_CB_database->loadObjectList( null, 'cbhangoutBlogTable', array( $_CB_database ) );
	}

	/**
	 * @param  boolean  $raw
	 * @return array
	 */
	static public function getCategoriesList( $raw = false )
	{
		global $_CB_database;

		static $cache				=	null;

		if ( ! isset( $cache ) ) {
			$plugin					=	cbhangoutClass::getPlugin();
			$section				=	$plugin->params->get( 'hangout_j_section', null );

			if ( $section ) {
				$query				=	'SELECT cat.' . $_CB_database->NameQuote( 'id' ) . ' AS value'
									.	", IF( cat." . $_CB_database->NameQuote( 'level' ) . " = ( sec." . $_CB_database->NameQuote( 'level' ) . " + 1 ), cat." . $_CB_database->NameQuote( 'title' ) . ", CONCAT( REPEAT( '- ', cat." . $_CB_database->NameQuote( 'level' ) . " - ( sec." . $_CB_database->NameQuote( 'level' ) . " + 1 ) ), cat." . $_CB_database->NameQuote( 'title' ) . " ) ) AS text"
									.	"\n FROM " . $_CB_database->NameQuote( '#__categories' ) . " AS cat"
									.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__categories' ) . " AS sec"
									.	' ON sec.' . $_CB_database->NameQuote( 'id' ) . ' = ' . (int) $section
									.	"\n WHERE cat." . $_CB_database->NameQuote( 'lft' ) . " BETWEEN ( sec." . $_CB_database->NameQuote( 'lft' ) . " + 1 ) AND ( sec." . $_CB_database->NameQuote( 'rgt' ) . " - 1 )"
									.	"\n AND sec." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
									.	"\n AND sec." . $_CB_database->NameQuote( 'extension' ) . " = " . $_CB_database->Quote( 'com_content' )
									.	"\n AND cat." . $_CB_database->NameQuote( 'published' ) . " = 1"
									.	"\n AND cat." . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
									.	"\n AND cat." . $_CB_database->NameQuote( 'extension' ) . " = " . $_CB_database->Quote( 'com_content' );
			} else {
				$query				=	'SELECT ' . $_CB_database->NameQuote( 'id' ) . ' AS value'
									.	", CONCAT( REPEAT( '- ', " . $_CB_database->NameQuote( 'level' ) . " ), " . $_CB_database->NameQuote( 'title' ) . " ) AS text"
									.	"\n FROM " . $_CB_database->NameQuote( '#__categories' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'published' ) . " = 1"
									.	"\n AND " . $_CB_database->NameQuote( 'access' ) . " IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
									.	"\n AND " . $_CB_database->NameQuote( 'extension' ) . " = " . $_CB_database->Quote( 'com_content' );
			}

			$_CB_database->setQuery( $query );

			$cache					=	$_CB_database->loadObjectList();
		}

		$rows						=	$cache;

		if ( $rows ) {
			if ( $raw === true ) {
				$categories			=	array();

				foreach ( $rows as $row ) {
					$categories[]	=	(int) $row->value;
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
	 * @param  string            $type
	 * @return string
	 */
	static public function getUrl( $row, $htmlspecialchars = true, $type = 'article' )
	{
		global $_CB_framework;

		if ( is_integer( $row ) ) {
			$row			=	new cbhangoutBlogTable();

			$row->load( (int) $row );
		}

		$category			=	$row->getCategory();

		/** @noinspection PhpIncludeInspection */
		require_once ( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_content/helpers/route.php' );

		$categorySlug		=	$row->get( 'catid' ) . ( $category->get( 'alias' ) ? ':' . $category->get( 'alias' ) : null );
		$articleSlug		=	$row->get( 'id' ) . ( $row->get( 'alias' ) ? ':' . $row->get( 'alias' ) : null );

		switch ( $type ) {
			case 'section':
				$url		=	ContentHelperRoute::getCategoryRoute( $row->get( 'sectionid' ) );
				break;
			case 'category':
				$url		=	ContentHelperRoute::getCategoryRoute( $categorySlug );
				break;
			case 'article':
			default:
				$url		=	ContentHelperRoute::getArticleRoute( $articleSlug, $categorySlug );
				break;
		}

		if ( ! stristr( $url, 'Itemid' ) ) {
			$url			=	$_CB_framework->getCfg( 'live_site' ) . '/' . $url;
		} else {
			$url			=	JRoute::_( $url, false );
		}

		if ( $url ) {
			if ( $htmlspecialchars ) {
				$url		=	htmlspecialchars( $url );
			}
		}

		return $url;
	}
}
