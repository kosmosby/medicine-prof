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
use CB\Plugin\GroupJiveForums\CBGroupJiveForums;
use CB\Plugin\GroupJiveForums\Model\ModelInterface;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );

$_PLUGINS->registerFunction( 'gj_onAfterUpdateCategory', 'storeForum', 'cbgjForumsPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterCreateCategory', 'storeForum', 'cbgjForumsPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterUpdateGroup', 'storeForum', 'cbgjForumsPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterCreateGroup', 'storeForum', 'cbgjForumsPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterDeleteCategory', 'deleteForum', 'cbgjForumsPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterDeleteGroup', 'deleteForum', 'cbgjForumsPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterUpdateUser', 'storeModerator', 'cbgjForumsPlugin' );
$_PLUGINS->registerFunction( 'gj_onAfterCreateUser', 'storeModerator', 'cbgjForumsPlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayGroupEdit', 'editGroup', 'cbgjForumsPlugin' );
$_PLUGINS->registerFunction( 'gj_onBeforeDisplayGroup', 'showForums', 'cbgjForumsPlugin' );
$_PLUGINS->registerFunction( 'kunenaIntegration', 'kunena', 'cbgjForumsPlugin' );

class cbgjForumsPlugin extends cbPluginHandler
{
	/** @var PluginTable  */
	public $_gjPlugin		=	null;
	/** @var Registry  */
	public $_gjParams		=	null;
	/** @var ModelInterface  */
	public $_forumModel		=	null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $_PLUGINS;

		if ( ! $this->_gjPlugin ) {
			$this->_gjPlugin		=	$_PLUGINS->getLoadedPlugin( 'user', 'cbgroupjive' );
			$this->_gjParams		=	$_PLUGINS->getPluginParams( $this->_gjPlugin );
			$this->_forumModel		=	CBGroupJiveForums::getModel();
		}
	}

	/**
	 * store the forum category for the group or category that was deleted
	 *
	 * @param GroupTable|CategoryTable $row
	 */
	public function storeForum( $row )
	{
		if ( ( ! $this->_forumModel ) || $row->get( '_skipForums' ) ) {
			return;
		}

		$parent					=	(int) $this->params->get( 'groups_forums_category' );

		if ( ! $parent ) {
			return;
		}

		if ( ( $row instanceof GroupTable ) && $row->category()->get( 'id' ) ) {
			$parentCategory		=	$this->_forumModel->getCategory( (int) $row->category()->params()->get( 'forum_id' ) );

			if ( ! $parentCategory->get( 'id' ) ) {
				$parentCategory->set( 'parent', $parent );
				$parentCategory->set( 'name', $row->category()->get( 'name' ) );
				$parentCategory->set( 'alias', $row->category()->get( 'id' ) . '-' . $row->category()->get( 'name' ) );
				$parentCategory->set( 'description', $row->category()->get( 'description' ) );
				$parentCategory->set( 'published', ( ! $row->category()->params()->get( 'forums', 1 ) ? 0 : $row->category()->get( 'published' ) ) );

				$parentCategory->access( $row->category() );

				if ( ! $parentCategory->check() ) {
					return;
				}

				if ( ! $parentCategory->store() ) {
					return;
				}

				$row->category()->set( '_skipForums', true );

				$row->category()->params()->set( 'forum_id', (int) $parentCategory->get( 'id' ) );

				$row->category()->set( 'params', $row->category()->params()->asJson() );

				$row->category()->store();

				$row->category()->set( '_skipForums', false );
			}

			$parent				=	(int) $parentCategory->get( 'id' );
		}

		$category				=	$this->_forumModel->getCategory( (int) $row->params()->get( 'forum_id' ) );

		$new					=	( $category->get( 'id' ) ? false : true );

		$category->set( 'parent', $parent );
		$category->set( 'name', $row->get( 'name' ) );
		$category->set( 'alias', $row->get( 'id' ) . '-' . $row->get( 'name' ) );
		$category->set( 'description', $row->get( 'description' ) );
		$category->set( 'published', ( ! $row->params()->get( 'forums', 1 ) ? 0 : $row->get( 'published' ) ) );

		$category->access( $row );

		if ( ! $category->check() ) {
			return;
		}

		if ( ! $category->store() ) {
			return;
		}

		if ( ( $row instanceof GroupTable ) && ( ! CBGroupJive::isModerator( $row->get( 'user_id' ) ) ) ) {
			$moderators			=	$category->moderators();

			if ( ! in_array( $row->get( 'user_id' ), $moderators ) ) {
				$category->moderators( $row->get( 'user_id' ) );
			}
		}

		if ( $new ) {
			$row->set( '_skipForums', true );

			$row->params()->set( 'forum_id', (int) $category->get( 'id' ) );

			$row->set( 'params', $row->params()->asJson() );

			$row->store();

			$row->set( '_skipForums', false );
		}
	}

	/**
	 * delete the forum category for the group or category that was deleted
	 *
	 * @param GroupTable|CategoryTable $row
	 */
	public function deleteForum( $row )
	{
		if ( ! $this->_forumModel ) {
			return;
		}

		$category		=	$this->_forumModel->getCategory( (int) $row->params()->get( 'forum_id' ) );

		if ( ! $category->get( 'id' ) ) {
			return;
		}

		$category->delete();
	}

	/**
	 * add or remove forum category moderators
	 *
	 * @param \CB\Plugin\GroupJive\Table\UserTable $row
	 */
	public function storeModerator( $row )
	{
		if ( ( ! $this->_forumModel ) || CBGroupJive::isModerator( $row->get( 'user_id' ) ) ) {
			return;
		}

		$category		=	$this->_forumModel->getCategory( (int) $row->group()->params()->get( 'forum_id' ) );

		if ( ! $category->get( 'id' ) ) {
			return;
		}

		$moderators		=	$category->moderators();

		if ( $row->get( 'status' ) >= 2 ) {
			if ( ! in_array( $row->get( 'user_id' ), $moderators ) ) {
				$category->moderators( $row->get( 'user_id' ) );
			}
		} else {
			if ( in_array( $row->get( 'user_id' ), $moderators ) ) {
				$category->moderators( null, $row->get( 'user_id' ) );
			}
		}
	}

	/**
	 * render frontend forum group edit params
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
		if ( ! $this->_forumModel ) {
			return null;
		}

		CBGroupJive::getTemplate( 'group_edit', true, true, $this->element );

		$listEnable			=	array();
		$listEnable[]		=	moscomprofilerHTML::makeOption( 0, CBTxt::T( 'Disable' ) );
		$listEnable[]		=	moscomprofilerHTML::makeOption( 1, CBTxt::T( 'Enable' ) );

		$enableTooltip		=	cbTooltip( null, CBTxt::T( 'Optionally enable or disable usage of forums.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

		$input['forums']	=	moscomprofilerHTML::selectList( $listEnable, 'params[forums]', 'class="form-control"' . $enableTooltip, 'value', 'text', (int) $this->input( 'post/params.forums', $row->params()->get( 'forums', 1 ), GetterInterface::INT ), 1, false, false );

		return HTML_groupjiveForumsParams::showForumsParams( $row, $input, $category, $user, $this );
	}

	/**
	 * prepare frontend forum render
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
	public function showForums( &$return, &$group, &$users, &$invites, &$counters, &$buttons, &$menu, &$tabs, $user )
	{
		if ( ! $this->_forumModel ) {
			return null;
		}

		return $this->_forumModel->getTopics( $user, $group, $counters );
	}

	/**
	 * integrates with kunena model
	 *
	 * @param string $event
	 * @param $config
	 * @param $params
	 */
	public function kunena( $event, &$config, &$params )
	{
		global $_CB_database;

		if ( ( ! $this->_forumModel ) || ( $this->_forumModel->type != 'kunena' ) ) {
			return;
		}

		if ( $event == 'loadGroups' ) {
			$groups									=	CBGroupJive::getGroupOptions();
			$options								=	array();

			foreach ( $groups as $group ) {
				$option								=	new stdClass();
				$option->id							=	( is_array( $group->value ) ? uniqid() : (int) $group->value );
				$option->parent_id					=	0;
				$option->level						=	( is_array( $group->value ) ? 0 : 1 );
				$option->name						=	$group->text;

				$options[$option->id]				=	$option;
			}

			$params['groups']						=	$options;
		} elseif ( $event == 'getAllowedForumsRead' ) {
			static $cache							=	array();

			$mydId									=	Application::MyUser()->getUserId();

			if ( ! $mydId ) {
				return;
			}

			if ( ! isset( $cache[$mydId] ) ) {
				$user								=	CBuser::getMyUserDataInstance();
				$isModerator						=	CBGroupJive::isModerator( $user->get( 'id' ) );

				$query								=	'SELECT g.*'
													.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_groups' ) . " AS g"
													.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS cb"
													.	' ON cb.' . $_CB_database->NameQuote( 'id' ) . ' = g.' . $_CB_database->NameQuote( 'user_id' )
													.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS j"
													.	' ON j.' . $_CB_database->NameQuote( 'id' ) . ' = cb.' . $_CB_database->NameQuote( 'id' )
													.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__groupjive_users' ) . " AS u"
													.	' ON u.' . $_CB_database->NameQuote( 'user_id' ) . ' = ' . (int) $user->get( 'id' )
													.	' AND u.' . $_CB_database->NameQuote( 'group' ) . ' = g.' . $_CB_database->NameQuote( 'id' )
													.	"\n WHERE cb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
													.	"\n AND cb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
													.	"\n AND j." . $_CB_database->NameQuote( 'block' ) . " = 0";

				if ( ! $isModerator ) {
					$query							.=	"\n AND ( g." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
													.		' OR ( g.' . $_CB_database->NameQuote( 'published' ) . ' = 1'
													.		' AND u.' . $_CB_database->NameQuote( 'status' ) . ' > 0 ) )';
				}

				$_CB_database->setQuery( $query );
				$groups								=	$_CB_database->loadObjectList( null, '\CB\Plugin\GroupJive\Table\GroupTable', array( $_CB_database ) );

				$allowed							=	array();

				/** @var GroupTable[] $groups */
				foreach ( $groups as $group ) {
					if ( $group->params()->get( 'forums', 1 ) ) {
						$froumId					=	(int) $group->params()->get( 'forum_id' );

						if ( $froumId && CBGroupJive::canCreateGroupContent( $user, $group, 'forums' ) ) {
							$allowed[]				=	$froumId;
						}
					}
				}

				$cache[$mydId]						=	$allowed;
			}

			if ( ! $cache[$mydId] ) {
				return;
			}

			$existingAccess							=	explode( ',', $params[1] );
			$cleanAccess							=	array_diff( $cache[$mydId], $existingAccess );
			$newAccess								=	array_merge( $existingAccess, $cleanAccess );

			cbArrayToInts( $newAccess );

			$params[1]								=	implode( ',', $newAccess );
		} elseif ( $event == 'authoriseUsers' ) {
			/** @var KunenaForumCategory $category */
			$category								=	$params['category'];
			$groupId								=	$category->get( 'access' );

			if ( ( $category->get( 'accesstype' ) != 'communitybuilder' ) || ( ! $groupId ) ) {
				return;
			}

			$users									=	$params['userids'];

			if ( ! $users ) {
				return;
			}

			static $allowed							=	array();

			if ( ! isset( $allowed[$groupId] ) ) {
				$allowed[$groupId]					=	array();

				$group								=	CBGroupJive::getGroup( $groupId );

				if ( $group->get( 'id' ) ) {
					$query							=	'SELECT u.' . $_CB_database->NameQuote( 'user_id' )
													.	"\n FROM " . $_CB_database->NameQuote( '#__groupjive_users' ) . " AS u"
													.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS cb"
													.	' ON cb.' . $_CB_database->NameQuote( 'id' ) . ' = u.' . $_CB_database->NameQuote( 'user_id' )
													.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS j"
													.	' ON j.' . $_CB_database->NameQuote( 'id' ) . ' = cb.' . $_CB_database->NameQuote( 'id' )
													.	"\n WHERE u." . $_CB_database->NameQuote( 'group' ) . " = " . (int) $group->get( 'id' )
													.	"\n AND cb." . $_CB_database->NameQuote( 'approved' ) . " = 1"
													.	"\n AND cb." . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
													.	"\n AND j." . $_CB_database->NameQuote( 'block' ) . " = 0"
													.	"\n AND u." . $_CB_database->NameQuote( 'status' ) . " >= 1";
					$_CB_database->setQuery( $query );
					$allowed[$groupId]				=	$_CB_database->loadResultArray();
				}

				foreach ( $users as $userId ) {
					if ( ( ! in_array( $userId, $allowed[$groupId] ) ) && CBGroupJive::isModerator( $userId ) ) {
						$allowed[$groupId][]		=	$userId;
					}
				}

				cbArrayToInts( $allowed[$groupId] );
			}

			if ( ! $allowed[$groupId] ) {
				return;
			}

			$params['allow']						=	$allowed[$groupId];
		} elseif ( $this->params->get( 'groups_forums_back', 1 ) && ( $event == 'onStart' ) && ( $this->input( 'view', null, GetterInterface::STRING ) == 'category' ) ) {
			$categoryId								=	(int) $this->input( 'catid', 0, GetterInterface::INT );

			if ( ! $categoryId ) {
				return;
			}

			$model									=	CBGroupJiveForums::getModel();

			if ( ! $model ) {
				return;
			}

			$category								=	$model->getCategory( $categoryId );

			if ( ! $category->get( 'id' ) ) {
				return;
			}

			$category								=	$category->category();

			if ( ( $category->get( 'accesstype' ) != 'communitybuilder' ) || ( ! $category->get( 'access' ) ) ) {
				return;
			}

			$group									=	CBGroupJive::getGroup( (int) $category->get( 'access' ) );

			if ( ! $group->get( 'id' ) ) {
				return;
			}

			CBGroupJive::getTemplate( 'backlink', true, true, $this->element );

			echo HTML_groupjiveForumsBacklink::showBacklink( $group, $category, $this );
		}
	}
}
