<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Plugin\GroupJiveForums\Model\Kunena;

use CBLib\Registry\Registry;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;
use CBLib\Language\CBTxt;
use CB\Plugin\GroupJive\CBGroupJive;
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJiveForums\Model\ModelInterface;
use CB\Plugin\GroupJiveForums\Table\Kunena\CategoryTable;
use CB\Plugin\GroupJiveForums\Table\Kunena\PostTable;

defined('CBLIB') or die();

class Model implements ModelInterface
{
	/** @var string  */
	public $type		=	'kunena';
	/** @var PluginTable  */
	public $gjPlugin	=	null;
	/** @var Registry  */
	public $gjParams	=	null;
	/** @var PluginTable  */
	public $plugin		=	null;
	/** @var Registry  */
	public $params		=	null;

	public function __construct()
	{
		global $_PLUGINS;

		$this->gjPlugin		=	$_PLUGINS->getLoadedPlugin( 'user', 'cbgroupjive' );
		$this->gjParams		=	$_PLUGINS->getPluginParams( $this->gjPlugin );

		$this->plugin		=	$_PLUGINS->getLoadedPlugin( 'user/plug_cbgroupjive/plugins', 'cbgroupjiveforums' );
		$this->params		=	$_PLUGINS->getPluginParams( $this->plugin );
	}

	/**
	 * @return array
	 */
	public function getCategories()
	{
		$rows			=	\KunenaForumCategoryHelper::getChildren( 0, 10, array( 'action' => 'admin', 'unpublished' => true ) );
		$options		=	array();

		foreach ( $rows as $row ) {
			$options[]	=	\moscomprofilerHTML::makeOption( (string) $row->id, str_repeat( '- ', $row->level + 1  ) . ' ' . $row->name );
		}

		return $options;
	}

	/**
	 * @param int $id
	 * @return CategoryTable
	 */
	public function getCategory( $id )
	{
		if ( ! $id ) {
			return new CategoryTable();
		}

		static $cache		=	array();

		if ( ! isset( $cache[$id] ) ) {
			$row			=	new CategoryTable();

			$row->load( (int) $id );

			$cache[$id]		=	$row;
		}

		return $cache[$id];
	}

	/**
	 * @param UserTable  $user
	 * @param GroupTable $group
	 * @param array      $counters
	 * @return array|null
	 */
	public function getTopics( $user, &$group, &$counters )
	{
		global $_CB_framework, $_CB_database;

		$categoryId					=	(int) $group->params()->get( 'forum_id' );

		if ( ( ! $categoryId ) || ( ! $group->params()->get( 'forums', 1 ) ) || ( $group->category()->get( 'id' ) && ( ! $group->category()->params()->get( 'forums', 1 ) ) ) ) {
			return null;
		}

		CBGroupJive::getTemplate( 'forums', true, true, $this->plugin->element );

		$limit						=	(int) $this->params->get( 'groups_forums_limit', 15 );
		$limitstart					=	$_CB_framework->getUserStateFromRequest( 'gj_group_forums_limitstart{com_comprofiler}', 'gj_group_forums_limitstart' );
		$search						=	$_CB_framework->getUserStateFromRequest( 'gj_group_forums_search{com_comprofiler}', 'gj_group_forums_search' );
		$where						=	null;

		if ( $search && $this->params->get( 'groups_forums_search', 1 ) ) {
			$where					.=	'( m.' . $_CB_database->NameQuote( 'subject' ) . ' LIKE ' . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false )
									.	' OR t.' . $_CB_database->NameQuote( 'message' ) . ' LIKE ' . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $search, true ) . '%', false ) . ' )';
		}

		$searching					=	( $where ? true : false );

		$params						=	array(	'starttime' => -1,
												'where' => $where
											);

		$posts						=	\KunenaForumMessageHelper::getLatestMessages( $categoryId, 0, 0, $params );
		$total						=	array_shift( $posts );

		if ( ( ! $total ) && ( ! $searching ) && ( ! CBGroupJive::canCreateGroupContent( $user, $group, 'forums' ) ) ) {
			return null;
		}

		$pageNav					=	new \cbPageNav( $total, $limitstart, $limit );

		$pageNav->setInputNamePrefix( 'gj_group_forums_' );

		switch( (int) $this->params->get( 'groups_forums_orderby', 2 ) ) {
			case 1:
				$params['orderby']	=	'm.' . $_CB_database->NameQuote( 'time' ) . ' ASC';
				break;
		}

		if ( $this->params->get( 'groups_forums_paging', 1 ) ) {
			$posts					=	\KunenaForumMessageHelper::getLatestMessages( $categoryId, (int) $pageNav->limitstart, (int) $pageNav->limit, $params );
			$posts					=	array_pop( $posts );
		} else {
			$posts					=	array_pop( $posts );
		}

		$rows						=	array();

		/** @var \KunenaForumMessage[] $posts */
		foreach ( $posts as $post ) {
			$row					=	new PostTable();

			$row->post( $post );

			$rows[]					=	$row;
		}

		$input						=	array();

		$input['search']			=	'<input type="text" name="gj_group_forums_search" value="' . htmlspecialchars( $search ) . '" onchange="document.gjGroupForumsForm.submit();" placeholder="' . htmlspecialchars( CBTxt::T( 'Search Posts...' ) ) . '" class="form-control" />';

		CBGroupJive::preFetchUsers( $rows );

		$group->set( '_forums', $pageNav->total );

		return array(	'id'		=>	'forums',
						'title'		=>	CBTxt::T( 'Forums' ),
						'content'	=>	\HTML_groupjiveForums::showForums( $rows, $pageNav, $searching, $input, $counters, $group, $user, $this )
					);
	}
}