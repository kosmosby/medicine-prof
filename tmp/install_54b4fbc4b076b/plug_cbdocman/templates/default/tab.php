<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
use CBLib\Application\Application;
use CBLib\Database\Table\Table;
use CBLib\Language\CBTxt;
use CB\Database\Table\PluginTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class HTML_cbdocmanTab
 * Template for CB Docman
 */
class HTML_cbdocmanTab
{
	/**
	 * Renders the Docman tab
	 *
	 * @param  Table[]      $rows       Articles to render
	 * @param  cbPageNav    $pageNav    Pagination
	 * @param  boolean      $searching  Currently searching
	 * @param  string[]     $input      HTML of input elements
	 * @param  UserTable    $viewer     Viewing user
	 * @param  UserTable    $user       Viewed user
	 * @param  stdClass     $model      The model reference
	 * @param  TabTable     $tab        Current Tab
	 * @param  PluginTable  $plugin     Current Plugin
	 * @return string                   HTML
	 */
	static public function showDocmanTab( $rows, $pageNav, $searching, $input, $viewer, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $tab, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		global $_CB_framework, $_LANG;

		$tabPaging				=	$tab->params->get( 'tab_paging', 1 );
		//$canSearch				=	( $tab->params->get( 'tab_search', 1 ) && ( $searching || $pageNav->total ) );
                $canCreate					=	false;
		$profileOwner				=	( $viewer->get( 'id' ) == $user->get( 'id' ) );
		$cbModerator				=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		
                if ( $profileOwner ) {
			if ( $cbModerator ) {
				$canCreate			=	true;
			} elseif ( $user->get( 'id' ) && Application::User( (int) $viewer->get( 'id' ) )->canViewAccessLevel( (int) $plugin->params->get( 'blog_create_access', 2 ) ) ) {
				if ( ( ! $blogLimit ) || ( $blogLimit && ( $pageNav->total < $blogLimit ) ) ) {
					$canCreate		=	true;
				}
			}
		}
                
		$return					=	'<div class="articlesTab">'
								.		'<form action="' . $_CB_framework->userProfileUrl( $user->id, true, $tab->tabid ) . '" method="post" name="articleForm" id="articleForm" class="articleForm">';

		/*if ( $canSearch ) {
			$return				.=			'<div class="articlesHeader row" style="margin-bottom: 10px;">'
								.				'<div class="col-sm-offset-8 col-sm-4 text-right">'
								.					'<div class="input-group">'
								.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
								.						$input['search']
								.					'</div>'
								.				'</div>'
								.			'</div>';
		}*/
                if ( $canCreate ) {
                $return                                 .=      '<div class="col-sm-8 text-left">'
									.					'<button type="button" onclick="location.href=\'' . JRoute::_('index.php?option=com_docman&view=document&layout=form&slug=&category_slug=&Itemid='.cbdocmanModel::getDocmanItemID()) . '\';" class="blogsButton blogsButtonNew btn btn-success"><span class="fa fa-plus-circle"></span> ' . $_LANG['New Document'] . '</button>'
									.				'</div>';

                }
                $return					.=			'<table class="articlesContainer table table-hover table-responsive">'
								.				'<thead>'
								.					'<tr>'
								.						'<th style="width: 50%;" class="text-left">' . $_LANG['Document Title'] . '</th>'
								.						'<th style="width: 25%;" class="text-left hidden-xs">' . CBTxt::T( 'Category' ) . '</th>'
								.						'<th style="width: 25%;" class="text-left hidden-xs">' . CBTxt::T( 'Created' ) . '</th>'
								.					'</tr>'
								.				'</thead>'
								.				'<tbody>';

		if ( $rows ) foreach ( $rows as $row ) {
			$return				.=					'<tr>'
								.						'<td style="width: 50%;" class="text-left"><a href="' . cbdocmanModel::getUrl( $row, true, 'article' ) . '">' . $row->get( 'title' ) . '</a></td>'
								.						'<td style="width: 25%;" class="text-left hidden-xs">' . ( $row->get( 'category' ) ? $row->get( 'category_title' ) : '' ) . '</td>'
								.						'<td style="width: 25%;" class="text-left hidden-xs">' . cbFormatDate( $row->get( 'created_on' ) ) . '</td>'
								.					'</tr>';
		} else {
			$return				.=					'<tr>'
								.						'<td colspan="3" class="text-left">';

			if ( $searching ) {
				$return			.=							CBTxt::T( 'No article search results found.' );
			} else {
				if ( $viewer->id == $user->id ) {
					$return		.=							$_LANG['You have no article'];
				} else {
					$return		.=							$_LANG['This user has no articles.'];
				}
			}

			$return				.=						'</td>'
								.					'</tr>';
		}

		$return					.=				'</tbody>';

		if ( $tabPaging && ( $pageNav->total > $pageNav->limit ) ) {
			$return				.=				'<tfoot>'
								.					'<tr>'
								.						'<td colspan="3" class="text-center">'
								.							$pageNav->getListLinks()
								.						'</td>'
								.					'</tr>'
								.				'</tfoot>';
		}

		$return					.=			'</table>'
								.			$pageNav->getLimitBox( false )
								.		'</form>'
								.	'</div>';

		return $return;
	}
}
