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
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;
use CB\Plugin\GroupJive\CBGroupJive;
use CB\Plugin\GroupJive\Table\GroupTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );

$_PLUGINS->registerFunction( 'gj_onBeforeDisplayGroup', 'showAbout', 'cbgjAboutPlugin' );

class cbgjAboutPlugin extends cbPluginHandler
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
	 * prepare frontend about render
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
	public function showAbout( &$return, &$group, &$users, &$invites, &$counters, &$buttons, &$menu, &$tabs, $user )
	{
		global $_CB_framework;

		if ( CBGroupJive::isModerator( $user->get( 'id' ) ) || ( ( $group->get( 'published' ) == 1 ) && ( CBGroupJive::getGroupStatus( $user, $group ) >= 3 ) ) ) {
			$menu[]		=	'<a href="' . $_CB_framework->pluginClassUrl( $this->element, true, array( 'action' => 'about', 'func' => 'edit', 'id' => (int) $group->get( 'id' ) ) ) . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'About' ) . '</a>';
		}

		$about			=	trim( $group->params()->get( 'about_content' ) );

		if ( ( ! $about ) || ( $about == '<p></p>' ) ) {
			return null;
		}

		CBGroupJive::getTemplate( 'about', true, true, $this->element );

		if ( $this->params->get( 'groups_about_substitutions', 0 ) ) {
			$about		=	CBuser::getInstance( (int) $user->get( 'id' ), false )->replaceUserVars( $about, false, false, null, false );
		}

		if ( $this->params->get( 'groups_about_content_plugins', 0 ) ) {
			$about		=	Application::Cms()->prepareHtmlContentPlugins( $about );
		}

		return array(	'id'		=>	'about',
						'title'		=>	CBTxt::T( 'About' ),
						'content'	=>	HTML_groupjiveAbout::showAbout( $about, $group, $user, $this )
					);
	}
}