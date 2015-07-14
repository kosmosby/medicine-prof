<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;
use CBLib\Registry\Registry;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_cbmedizdTab
{

	/**
	 * @param cbinvitesInviteTable[] $rows
	 * @param cbPageNav              $pageNav
	 * @param bool                   $searching
	 * @param array                  $input
	 * @param UserTable              $viewer
	 * @param UserTable              $user
	 * @param TabTable               $tab
	 * @param cbTabHandler           $plugin
	 * @return string
	 */
	static function showTab( $rows, $pageNav, $searching, $input, $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework, $_CB_database;

		$params						=	new Registry( $tab->params );
		$profileOwner				=	( $viewer->get( 'id' ) == $user->get( 'id' ) );
		$cbModerator				=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();

		$tabPaging					=	$params->get( 'tab_paging', 1 );
		$canSearch					=	( $params->get( 'tab_search', 1 ) && ( $searching || $pageNav->total ) );

		$inviteLimit				=	(int) $plugin->params->get( 'invite_limit', null );
		$canCreate					=	false;
                
                $user_groups_can_create = array(12,16); //proizvoditeli, komercheskie organizacii
                $gids = $user->get('gids');

		if ( $profileOwner ) {
			if ( $cbModerator ) {
				$canCreate			=	true;
			} elseif ( $user->get( 'id' )   ) {
				if ( $inviteLimit ) {
					$query			=	'SELECT COUNT(*)'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_invites' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
									.	"\n AND ( " . $_CB_database->NameQuote( 'user' ) . " IS NULL OR " . $_CB_database->NameQuote( 'user' ) . " = " . $_CB_database->Quote( '' ) . " )";
					$_CB_database->setQuery( $query );
					$inviteCount	=	(int) $_CB_database->loadResult();

					if ( $inviteCount < $inviteLimit ) {
						$canCreate	=	true;
					}
				} else {
					$canCreate		=	true;
				}
			}
		}
                $groupcancreate = false;
                if($gids){
                    foreach($gids as $gid){
                        if(in_array($gid, $user_groups_can_create)){
                            $groupcancreate = true;
                        }
                    }
                    
                }
                
                if(!$groupcancreate){
                    $canCreate = false;
                }

		$return						=	'<div class="medizdTab">'
									.		'<form action="' . $_CB_framework->userProfileUrl( $user->get( 'id' ), true, $tab->tabid ) . '" method="post" name="medizdForm" id="inviteForm" class="medizdForm">';

		if ( $canCreate || $canSearch ) {
			$return					.=			'<div class="medizdHeader row" style="margin-bottom: 10px;">';

			if ( $canCreate ) {
				$return				.=				'<div class="' . ( ! $canSearch ? 'col-sm-12' : 'col-sm-8' ) . ' text-left">'
									.					'<button type="button" onclick="location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'medizd', 'func' => 'new' ) ) . '\';" class="invitesButton invitesButtonNew btn btn-success"><span class="fa fa-plus-circle"></span> ' . CBTxt::T( 'MEDPR_NEW_PRODUCT' ) . '</button>'
									.				'</div>';
			}

			if ( $canSearch ) {
				$return				.=				'<div class="' . ( ! $canCreate ? 'col-sm-offset-8 ' : null ) . 'col-sm-4 text-right">'
									.					'<div class="input-group">'
									.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
									.						$input['search']
									.					'</div>'
									.				'</div>';
			}

			$return					.=			'</div>';
		}

		$menuAccess					=	( $cbModerator || $profileOwner );

		

		$return						.=			'<table class="invitesContainer table table-hover table-responsive">'
									.				'<thead>'
									.					'<tr>'
									.						'<th class="text-left">' . CBTxt::T( 'MEDPR_CODE' ) . '</th>'
									.						'<th style="width: 25%;" class="text-left hidden-xs">' . CBTxt::T( 'Name' ) . '</th>'
									.						'<th style="width: 5%;" class="text-center hidden-xs">' . CBTxt::T( 'Date' ) . '</th>'
                                                                        .                                               '<th style="width: 1%;" class="text-left hidden-xs"></th>'
									.					'</tr>'
									.				'</thead>'
									.				'<tbody>';

		if ( $rows ) foreach ( $rows as $row ) {
			

			$return					.=					'<tr>'
									.						'<td class="text-left"><a href="'.JRoute::_(JUri::base().'index.php?option=com_medicineproducts&view=item&id='.$row->id).'">' . $row->code . '</td>'
									.						'<td style="width: 50%;" class="text-left hidden-xs">'
									.							$row->name
									.						'</td>'
									.						'<td style="width: 25%;" class="text-center hidden-xs">'
                                                                        .                                               $row->created;

			

			$return					.=						'</td>';

			if ( ( $cbModerator || $profileOwner )    ) {
				$menuItems			=	'<ul class="invitesMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">';

                                    $link = $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'medizd', 'func' => 'edit', 'id' => (int) $row->get( 'id' )));
				
					$menuItems		.=		'<li class="invitesMenuItem"><a href="' . $link . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'Edit' ) . '</a></li>'
									.		'<li class="invitesMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'MEDPR_CONFIRM_DEL' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'medizd', 'func' => 'delete', 'id' => (int) $row->get( 'id' ) ) ) . '\'; }"><span class="fa fa-trash-o"></span> ' . CBTxt::T( 'Delete' ) . '</a></li>';
				

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

			$return					.=					'</tr>';
		} else {
			$return					.=					'<tr>'
									.						'<td colspan="3" class="text-left">';

			if ( $searching ) {
				$return				.=							CBTxt::T( 'MEDPR_PRODUCT_SEARCH_NULL' );
			} else {
				if ( $viewer->id == $user->id ) {
					$return			.=							CBTxt::T( 'MEDPR_NO_PRODUCT' );
				} else {
					$return			.=							CBTxt::T( 'MEDPR_USER_NO_PRODUCT' );
				}
			}

			$return					.=						'</td>'
									.					'</tr>';
		}

		$return						.=				'</tbody>';

		if ( $tabPaging && ( $pageNav->total > $pageNav->limit ) ) {
			$return					.=				'<tfoot>'
									.					'<tr>'
									.						'<td colspan="3" class="text-center">'
									.							$pageNav->getListLinks()
									.						'</td>'
									.					'</tr>'
									.				'</tfoot>';
		}

		$return						.=			'</table>'
									.			$pageNav->getLimitBox( false )
									.		'</form>'
									.	'</div>';

		return $return;
	}
}
?>