<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
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
 * Class HTML_cbarticlesTab
 * Template for CB Articles
 */
class HTML_cbpreparatyTab
{
	/**
	 * Renders the Articles tab
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
	static public function showPreparatyTab( $rows, $pageNav, $searching, $input, $viewer, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $tab, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		global $_CB_framework, $_LANG;
                
                $app =& JFactory::getApplication();
                $menu       = $app->getMenu();
                $active = $menu->getActive();
                $Itemid = $active->id;
                
		$tabPaging				=	$tab->params->get( 'tab_paging', 1 );
		$canSearch				=	( $tab->params->get( 'tab_search', 1 ) && ( $searching || $pageNav->total ) );
                $canCreate					=	false;
		$profileOwner				=	( $viewer->get( 'id' ) == $user->get( 'id' ) );
		$cbModerator				=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		//$canPublish					=	( $cbModerator || ( $profileOwner && ( ! $plugin->params->get( 'hangout_approval', 0 ) ) ) );

		if ( $profileOwner ) {
			if ( $cbModerator ) {
				$canCreate			=	true;
			} elseif ( $user->get( 'id' ) && Application::User( (int) $viewer->get( 'id' ) )->canViewAccessLevel( (int) $plugin->params->get( 'hangout_create_access', 2 ) ) ) {
				if ( ( ! $blogLimit ) || ( $blogLimit && ( $pageNav->total < $blogLimit ) ) ) {
					$canCreate		=	true;
				}
			}
		}
                
		$return					=	'<div class="articlesTab">'
								.		'<form action="' . $_CB_framework->userProfileUrl( $user->id, true, $tab->tabid ) . '" method="post" name="articleForm" id="articleForm" class="articleForm">';
                
                if ( $canCreate ) {
				$return				.=				'<div class="' . ( ! $canSearch ? 'col-sm-12' : 'col-sm-8' ) . ' text-left">'
									.					'<button type="button" onclick="location.href=\'' . ($_CB_framework->getCfg( 'live_site' ).'/index.php?option=com_flexicontent&view=item&typeid=2&task=add&Itemid='.$Itemid) . '\';" class="blogsButton blogsButtonNew btn btn-success"><span class="fa fa-plus-circle"></span> ' . $_LANG['New Preparat'] . '</button>'
									.				'</div>';
			}
                        
		if ( $canSearch ) {
			$return				.=			'<div class="articlesHeader row" style="margin-bottom: 10px;">'
								.				'<div class="col-sm-offset-8 col-sm-4 text-right">'
								.					'<div class="input-group">'
								.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
								.						$input['search']
								.					'</div>'
								.				'</div>'
								.			'</div>';
		}

		$return					.=			'<table class="articlesContainer table table-hover table-responsive">'
								.				'<thead>'
								.					'<tr>'
								.						'<th style="width: 50%;" class="text-left">' . $_LANG['Preparat'] . '</th>'
								.						'<th style="width: 25%;" class="text-left hidden-xs">' . CBTxt::T( 'Category' ) . '</th>'
								.						'<th style="width: 25%;" class="text-left hidden-xs">' . CBTxt::T( 'Created' ) . '</th>'
                                                                .                                               '<th style="width: 1%;" class="text-left hidden-xs"></th>'
								.					'</tr>'
								.				'</thead>'
								.				'<tbody>';
                
		
               $attribs = '';
			$image = FLEXI_J16GE ?
				JHTML::image(FLEXI_ICONPATH.'edit.png', JText::_( 'FLEXI_EDIT' ), $attribs) :
				JHTML::_('image.site', 'edit.png', FLEXI_ICONPATH, NULL, NULL, JText::_( 'FLEXI_EDIT' ), $attribs) ;
                
		if ( $rows ) foreach ( $rows as $row ) {
                    $item_url = cbpreparatyModel::getUrl( $row, true, 'article' , $Itemid);
                    //$item_url_edit = cbpreparatyModel::getUrl( $row, true, 'article' , 445);
                    $link = $_CB_framework->getCfg( 'live_site' ). '/' .$item_url  .(strstr($item_url, '?') ? '&' : '?').  'task=edit';
                    $edit_row	= $profileOwner ? '<a href="'.$link.'">'.$image.'</a>&nbsp;' : '';
                
			$return				.=					'<tr>'
								.						'<td style="width: 50%;" class="text-left">'.$edit_row.'<a href="' . cbpreparatyModel::getUrl( $row, true, 'article' ) . '">' . $row->get( 'title' ) . '</a></td>'
								.						'<td style="width: 25%;" class="text-left hidden-xs">' . ( $row->get( 'category' ) ? $row->get( 'category_title' ) : CBTxt::T( 'None' ) ) . '</td>'
								.						'<td style="width: 25%;" class="text-left hidden-xs">' . cbFormatDate( $row->get( 'created' ) ) . '</td>';
                        if ( ( $cbModerator || $profileOwner )    ) {
				$menuItems			=	'<ul class="invitesMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">';


				
					$menuItems		.=		'<li class="invitesMenuItem"><a href="' . $link . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'Edit' ) . '</a></li>'
									.		'<li class="invitesMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to delete this Drug?' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'preparaty', 'func' => 'delete', 'id' => (int) $row->get( 'id' ) ) ) . '\'; }"><span class="fa fa-trash-o"></span> ' . CBTxt::T( 'Delete' ) . '</a></li>';
				

				$menuItems			.=	'</ul>';

				$menuAttr			=	cbTooltip( 1, $menuItems, null, 'auto', null, null, null, 'class="btn btn-default btn-xs" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle"' );

				$return				.=						'<td style="width: 1%;" class="text-right">'
									.							'<div class="invitesMenu btn-group">'
									.								'<button type="button"' . $menuAttr . '><span class="fa fa-cog"></span> <span class="fa fa-caret-down"></span></button>'
									.							'</div>'
									.						'</td>';
			} else{
				$return				.=						'<td style="width: 1%;" class="text-right"></td>';
			}
                        
			$return				.= 					'</tr>';
		} else {
			$return				.=					'<tr>'
								.						'<td colspan="3" class="text-left">';

			if ( $searching ) {
				$return			.=							$_LANG['No preparaty search results found.'];
			} else {
				if ( $viewer->id == $user->id ) {
					$return		.=							$_LANG['You have no preparaty.'];
				} else {
					$return		.=							$_LANG['This user has no preparaty.'];
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
