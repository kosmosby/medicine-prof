<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Input\Get;
use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Database\Table\Table;
use CB\Database\Table\FieldTable;
use CB\Database\Table\UserTable;
use CB\Database\Table\PluginTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;

$_PLUGINS->loadPluginGroup( 'user' );


//$_PLUGINS->registerFunction( 'onAfterDeleteUser', 'deleteMedizd', 'cbinvitesPlugin' );

///$_PLUGINS->registerUserFieldTypes();
//$_PLUGINS->registerUserFieldParams();

class cbmedizdClass
{


	static public function getTemplate( $files = null, $loadGlobal = true, $loadHeader = true )
	{
		global $_CB_framework, $_PLUGINS;

		static $tmpl							=	array();

		if ( ! $files ) {
			$files								=	array();
		} elseif ( ! is_array( $files ) ) {
			$files								=	array( $files );
		}

		$id										=	md5( serialize( array( $files, $loadGlobal, $loadHeader ) ) );

		if ( ! isset( $tmpl[$id] ) ) {
			$plugin								=	$_PLUGINS->getLoadedPlugin( 'user', 'cbinvites' );

			if ( ! $plugin ) {
				return;
			}

			$livePath							=	$_PLUGINS->getPluginLivePath( $plugin );
			$absPath							=	$_PLUGINS->getPluginPath( $plugin );
			$params								=	$_PLUGINS->getPluginParams( $plugin );

			$template							=	$params->get( 'general_template', 'default' );
			$paths								=	array( 'global_css' => null, 'php' => null, 'css' => null, 'js' => null, 'override_css' => null );

			foreach ( $files as $file ) {
				$file							=	preg_replace( '/[^-a-zA-Z0-9_]/', '', $file );
				$globalCss						=	'/templates/' . $template . '/template.css';
				$overrideCss					=	'/templates/' . $template . '/override.css';

				if ( $file ) {
					$php						=	$absPath . '/templates/' . $template . '/' . $file . '.php';
					$css						=	'/templates/' . $template . '/' . $file . '.css';
					$js							=	'/templates/' . $template . '/' . $file . '.js';
				} else {
					$php						=	null;
					$css						=	null;
					$js							=	null;
				}

				if ( $loadGlobal && $loadHeader ) {
					if ( ! file_exists( $absPath . $globalCss ) ) {
						$globalCss				=	'/templates/default/template.css';
					}

					if ( file_exists( $absPath . $globalCss ) ) {
						$_CB_framework->document->addHeadStyleSheet( $livePath . $globalCss );

						$paths['global_css']	=	$livePath . $globalCss;
					}
				}

				if ( $file ) {
					if ( ! file_exists( $php ) ) {
						$php					=	$absPath . '/templates/default/' . $file . '.php';
					}

					if ( file_exists( $php ) ) {
						require_once( $php );

						$paths['php']			=	$php;
					}

					if ( $loadHeader ) {
						if ( ! file_exists( $absPath . $css ) ) {
							$css				=	'/templates/default/' . $file . '.css';
						}

						if ( file_exists( $absPath . $css ) ) {
							$_CB_framework->document->addHeadStyleSheet( $livePath . $css );

							$paths['css']		=	$livePath . $css;
						}

						if ( ! file_exists( $absPath . $js ) ) {
							$js					=	'/templates/default/' . $file . '.js';
						}

						if ( file_exists( $absPath . $js ) ) {
							$_CB_framework->document->addHeadScriptUrl( $livePath . $js );

							$paths['js']		=	$livePath . $js;
						}
					}
				}

				if ( $loadGlobal && $loadHeader ) {
					if ( file_exists( $absPath . $overrideCss ) ) {
						$_CB_framework->document->addHeadStyleSheet( $livePath . $overrideCss );

						$paths['override_css']	=	$livePath . $overrideCss;
					}
				}
			}

			$tmpl[$id]							=	$paths;
		}
	}
}

class cbmedizdProductTable extends Table
{
	var $id					=	null;
	var $user_id			=	null;
	var $code					=	null;
	var $category			=	null;
	var $name				=	null;
	var $description				=	null;
	var $proizvoditel				=	null;
	var $country			=	null;
	var $price				=	null;
        var $created				=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl			=	'#__comprofiler_plugin_cbmedizd';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key		=	'id';

	/**
	 * @param bool $updateNulls
	 * @return bool
	 */
	public function store( $updateNulls = false )
	{
		global $_PLUGINS;

		$new	=	( $this->get( 'id' ) ? false : true );


		if ( ! parent::store( $updateNulls ) ) {
			return false;
		}



		return true;
	}

	/**
	 * @param null|int $id
	 * @return bool
	 */
	public function delete( $id = null )
	{
		global $_PLUGINS;

		//$_PLUGINS->trigger( 'invites_onBeforeDeleteInvite', array( &$this ) );

		if ( ! parent::delete( $id ) ) {
			return false;
		}

		//$_PLUGINS->trigger( 'invites_onAfterDeleteInvite', array( $this ) );

		return true;
	}

	
}

class cbmedizdPlugin extends cbPluginHandler
{

	/**
	 * @param UserTable $user
	 */
	public function acceptInvites( $user )
	{
		global $_CB_database;

		$code							=	$user->get( 'invite_code' );

		$query							=	'SELECT *'
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_invites' );
		if ( $code ) {
			$query						.=	"\n WHERE ( " . $_CB_database->NameQuote( 'to' ) . " = " . $_CB_database->Quote( $user->email )
										.	' OR ' . $_CB_database->NameQuote( 'code' ) . ' = ' . $_CB_database->Quote( $code ) . ' )';
		} else {
			$query						.=	"\n WHERE " . $_CB_database->NameQuote( 'to' ) . " = " . $_CB_database->Quote( $user->email );
		}
		$_CB_database->setQuery( $query );
		$invites						=	$_CB_database->loadObjectList( null, 'cbinvitesInviteTable', array( $_CB_database ) );

		/** @var cbinvitesInviteTable[] $invites */
		if ( $invites ) foreach ( $invites as $invite ) {
			$invite->accept( $user );
		}
	}

	/**
	 * @param UserTable $user
	 */
	public function deleteInvites( $user )
	{
		global $_CB_database;

		$query			=	'SELECT *'
						.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_invites' )
						.	"\n WHERE ( " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->id
						.	' OR ' . $_CB_database->NameQuote( 'user' ) . ' = ' . (int) $user->id . ' )';
		$_CB_database->setQuery( $query );
		$invites		=	$_CB_database->loadObjectList( null, 'cbinvitesInviteTable', array( $_CB_database ) );

		/** @var cbinvitesInviteTable[] $invites */
		if ( $invites ) foreach ( $invites as $invite ) {
			$invite->delete();
		}
	}
}

class cbmedizdTab extends cbTabHandler
{

	/**
	 * @param moscomprofilerTabs $tab
	 * @param UserTable          $user
	 * @param int                $ui
	 * @return null|string
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		global $_CB_framework, $_CB_database,$_PLUGINS;

		$viewer					=	CBuser::getMyUserDataInstance();
                $absPath							=	$_PLUGINS->getPluginPath( $plugin );
                require $absPath . '/templates/default/tab.php';
                //cbmedizdClass::getTemplate();
		//if ( $viewer->id == $user->id ) {
			outputCbJs( 1 );
			outputCbTemplate( 1 );
			cbimport( 'cb.pagination' );

			//cbinvitesClass::getTemplate( 'tab' );

			$limit				=	(int) $this->params->get( 'tab_limit', 15 );
			$limitstart			=	$_CB_framework->getUserStateFromRequest( 'tab_medizd_limitstart{com_comprofiler}', 'tab_medizd_limitstart' );
			$filterSearch		=	$_CB_framework->getUserStateFromRequest( 'tab_medizd_search{com_comprofiler}', 'tab_medizd_search' );
			$where				=	null;
			$join				=	null;

			if ( isset( $filterSearch ) && ( $filterSearch != '' ) ) {
				$where			.=	"\n AND ( a." . $_CB_database->NameQuote( 'name' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false )
								.	" OR b." . $_CB_database->NameQuote( 'id' ) . " = " . $_CB_database->Quote( $filterSearch )
								.	" OR a." . $_CB_database->NameQuote( 'description' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false )
								.	" OR b." . $_CB_database->NameQuote( 'name' ) . " LIKE " . $_CB_database->Quote( '%' . $_CB_database->getEscaped( $filterSearch, true ) . '%', false ) . " )";

				$join			.=	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS b"
								.	' ON b.' . $_CB_database->NameQuote( 'id' ) . ' = a.' . $_CB_database->NameQuote( 'user_id' );
			}

			$searching			=	( $where ? true : false );

			$query				=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_cbmedizd' ) . " AS a"
								.	$join
								.	"\n WHERE a." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->id
								.	$where
								.	"\n ORDER BY " . $_CB_database->NameQuote( 'created' ) . " DESC";
			$_CB_database->setQuery( $query );
			$total				=	$_CB_database->loadResult();

			if ( $total <= $limitstart ) {
				$limitstart		=	0;
			}

			$pageNav			=	new cbPageNav( $total, $limitstart, $limit );

			$pageNav->setInputNamePrefix( 'tab_medizd_' );

			$query				=	'SELECT a.*'
								.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_cbmedizd' ) . " AS a"
								.	$join
								.	"\n WHERE a." . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->id
								.	$where
								.	"\n ORDER BY " . $_CB_database->NameQuote( 'created' ) . " DESC";
			if ( $this->params->get( 'tab_paging', 1 ) ) {
				$_CB_database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
			} else {
				$_CB_database->setQuery( $query );
			}
			$rows				=	$_CB_database->loadObjectList( null, 'cbmedizdProductTable', array( $_CB_database ) );

			$input				=	array();
			$input['search']	=	'<input type="text" name="tab_medizd_search" value="' . htmlspecialchars( $filterSearch ) . '" onchange="document.medizdForm.submit();" placeholder="' . htmlspecialchars( CBTxt::T( 'MEDPR_SEARCH_PRODUCT' ) ) . '" class="form-control" />';

			$class				=	$this->params->get( 'general_class', null );

			$return				=	'<div id="cbmedizd" class="cbmedizd' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . '">'
								.		'<div id="cbmedizdInner" class="cbmedizdInner">'
								.			HTML_cbmedizdTab::showTab( $rows, $pageNav, $searching, $input, $viewer, $user, $tab, $this )
								.		'</div>'
								.	'</div>';

			return $return;
		//}

		return null;
	}
}

?>