<?php
/**
* CBSubs (TM): Community Builder Paid Subscriptions Plugin: cbsubsgroupjive
* @version $Id: cbsubs.groupjive.php 1465 2012-07-10 17:37:13Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage cbsubs.groupjive.php
* @author Beat
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;
use CBLib\Registry\ParamsInterface;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onCPayUserStateChange', 'onCPayUserStateChange', 'getcbsubsgroupjiveTab' );

/**
 * CBSubs GroupJive integration plugin class
 */
class getcbsubsgroupjiveTab extends cbTabHandler {

	/**
	 * Called at each change of user subscription state due to a plan activation or deactivation
	 *
	 * @param  UserTable        $user
	 * @param  string           $status
	 * @param  int              $planId
	 * @param  int              $replacedPlanId
	 * @param  ParamsInterface  $integrationParams
	 * @param  string           $cause              'PaidSubscription' (first activation only), 'SubscriptionActivated' (renewals, cancellation reversals), 'SubscriptionDeactivated', 'Denied'
	 * @param  string           $reason             'N' new subscription, 'R' renewal, 'U'=update )
	 * @param  int              $now                Unix time
	 */
	public function onCPayUserStateChange( &$user, $status, /** @noinspection PhpUnusedParameterInspection */ $planId, /** @noinspection PhpUnusedParameterInspection */ $replacedPlanId, &$integrationParams, /** @noinspection PhpUnusedParameterInspection */ $cause, /** @noinspection PhpUnusedParameterInspection */ $reason, /** @noinspection PhpUnusedParameterInspection */ $now ) {
		global $_CB_framework;

		if ( ! is_object( $user ) ) {
			return;
		}

		$api											=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbgroupjive/cbgroupjive.class.php';

		if ( ! file_exists( $api ) ) {
			return;
		}

		/** @noinspection PhpIncludeInspection */
		require_once( $api );

		$gj_plugin										=	cbgjClass::getPlugin();
		$cbUser											=	CBuser::getInstance( $user->id );

		if ( ! $cbUser ) {
			$cbUser										=	$cbUser->getInstance( null );
		}

		for ( $i = 1; $i <= 5; $i++ ) {
			if ( $status == 'A' ) {
				if ( ( cbgjClass::getCleanParam( true, 'cbgj_auto_type' . $i, null, null, $integrationParams ) == 3 ) && cbgjClass::getCleanParam( true, 'cbgj_auto_name' . $i, null, null, $integrationParams ) ) {
					$parent								=	(int) cbgjClass::getCleanParam( true, 'cbgj_auto_cat_parent' . $i, '0', null, $integrationParams );
					$name								=	$cbUser->replaceUserVars( cbgjClass::getCleanParam( true, 'cbgj_auto_name' . $i, null, null, $integrationParams ) );

					if ( cbgjClass::getCleanParam( true, 'cbgj_auto_unique' . $i, 1, null, $integrationParams ) ) {
						$where							=	array( array( 'user_id', '=', $user->id ), array( 'name', '=', $name ), array( 'parent', '=', $parent ) );
					} else {
						$where							=	array( array( 'name', '=', $name ), array( 'parent', '=', $parent ) );
					}

					$row								=	cbgjData::getCategories( null, null, $where, null, null, false );

					if ( ! $row->id ) {
						$category_editor				=	$gj_plugin->params->get( 'category_editor', 1 );
						$types							=	cbgjClass::getCleanParam( true, 'cbgj_auto_cat_types' . $i, '1|*|2|*|3', null, $integrationParams );

						$row->published					=	1;
						$row->parent					=	$parent;
						$row->user_id					=	(int) $user->id;
						$row->name						=	$name;

						if ( ( $category_editor == 2 ) || ( $category_editor == 3 ) ) {
							$row->description			=	$cbUser->replaceUserVars( cbgjClass::getHTMLCleanParam( true, 'cbgj_auto_desc' . $i, null, null, $integrationParams ) );
						} else {
							$row->description			=	$cbUser->replaceUserVars( cbgjClass::getCleanParam( true, 'cbgj_auto_desc' . $i, null, null, $integrationParams ) );
						}

						$row->access					=	(int) $gj_plugin->params->get( 'category_access_default', -2 );
						$row->types						=	( $types ? $types : $gj_plugin->params->get( 'category_types_default', '1|*|2|*|3' ) );
						$row->create					=	(int) $gj_plugin->params->get( 'category_create_default', 1 );
						$row->create_access				=	(int) $gj_plugin->params->get( 'category_createaccess_default', -1 );
						$row->nested					=	(int) $gj_plugin->params->get( 'category_nested_default', 1 );
						$row->nested_access				=	(int) $gj_plugin->params->get( 'category_nestedaccess_default', -1 );
						$row->date						=	date( 'Y-m-d H:i:s' );
						$row->ordering					=	99999;

						$row->store();
					}
				} elseif ( ( cbgjClass::getCleanParam( true, 'cbgj_auto_type' . $i, null, null, $integrationParams ) == 2 ) && cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat' . $i, null, null, $integrationParams ) && cbgjClass::getCleanParam( true, 'cbgj_auto_name' . $i, null, null, $integrationParams ) ) {
					if ( ( cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat' . $i, null, null, $integrationParams ) == -1 ) && cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat_name' . $i, null, null, $integrationParams ) ) {
						$parent							=	(int) cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat_parent' . $i, '0', null, $integrationParams );
						$name							=	$cbUser->replaceUserVars( cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat_name' . $i, null, null, $integrationParams ) );

						if ( cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat_unique' . $i, 1, null, $integrationParams ) ) {
							$where						=	array( array( 'user_id', '=', $user->id ), array( 'name', '=', $name ), array( 'parent', '=', $parent ) );
						} else {
							$where						=	array( array( 'name', '=', $name ), array( 'parent', '=', $parent ) );
						}

						$category						=	cbgjData::getCategories( null, null, $where, null, null, false );

						if ( ! $category->id ) {
							$category_editor			=	$gj_plugin->params->get( 'category_editor', 1 );
							$types						=	cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat_types' . $i, '1|*|2|*|3', null, $integrationParams );

							$category->published		=	1;
							$category->parent			=	$parent;
							$category->user_id			=	(int) $user->id;
							$category->name				=	$name;

							if ( ( $category_editor == 2 ) || ( $category_editor == 3 ) ) {
								$category->description	=	$cbUser->replaceUserVars( cbgjClass::getHTMLCleanParam( true, 'cbgj_auto_grp_cat_desc' . $i, null, null, $integrationParams ) );
							} else {
								$category->description	=	$cbUser->replaceUserVars( cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat_desc' . $i, null, null, $integrationParams ) );
							}

							$category->access			=	(int) $gj_plugin->params->get( 'category_access_default', -2 );
							$category->types			=	( $types ? $types : $gj_plugin->params->get( 'category_types_default', '1|*|2|*|3' ) );
							$category->create			=	(int) $gj_plugin->params->get( 'category_create_default', 1 );
							$category->create_access	=	(int) $gj_plugin->params->get( 'category_createaccess_default', -1 );
							$category->nested			=	(int) $gj_plugin->params->get( 'category_nested_default', 1 );
							$category->nested_access	=	(int) $gj_plugin->params->get( 'category_nestedaccess_default', -1 );
							$category->date				=	date( 'Y-m-d H:i:s' );
							$category->ordering			=	99999;

							$category->store();
						}
					} else {
						$category						=	cbgjData::getCategories( null, null, array( 'id', '=', cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat' . $i, null, null, $integrationParams ) ), null, null, false );
					}

					if ( $category->id ) {
						$parent							=	(int) cbgjClass::getCleanParam( true, 'cbgj_auto_grp_parent' . $i, '0', null, $integrationParams );
						$name							=	$cbUser->replaceUserVars( cbgjClass::getCleanParam( true, 'cbgj_auto_name' . $i, null, null, $integrationParams ) );
						$join							=	false;

						if ( cbgjClass::getCleanParam( true, 'cbgj_auto_unique' . $i, 1, null, $integrationParams ) ) {
							$where						=	array( array( 'category', '=', $category->id ), array( 'user_id', '=', $user->id ), array( 'name', '=', $name ), array( 'parent', '=', $parent ) );
						} else {
							$where						=	array( array( 'category', '=', $category->id ), array( 'name', '=', $name ), array( 'parent', '=', $parent ) );

							if ( cbgjClass::getCleanParam( true, 'cbgj_auto_grp_autojoin' . $i, 1, null, $integrationParams ) ) {
								$join					=	true;
							}
						}

						$row							=	cbgjData::getGroups( null, null, $where, null, null, false );

						if ( ! $row->id ) {
							$group_editor				=	$gj_plugin->params->get( 'group_editor', 1 );
							$type						=	cbgjClass::getCleanParam( true, 'cbgj_auto_grp_type' . $i, 1, null, $integrationParams );

							$row->published				=	1;
							$row->category				=	(int) $category->id;
							$row->parent				=	$parent;
							$row->user_id				=	(int) $user->id;
							$row->name					=	$name;

							if ( ( $group_editor == 2 ) || ( $group_editor == 3 ) ) {
								$row->description		=	$cbUser->replaceUserVars( cbgjClass::getHTMLCleanParam( true, 'cbgj_auto_desc' . $i, null, null, $integrationParams ) );
							} else {
								$row->description		=	$cbUser->replaceUserVars( cbgjClass::getCleanParam( true, 'cbgj_auto_desc' . $i, null, null, $integrationParams ) );
							}

							$row->access				=	(int) $gj_plugin->params->get( 'group_access_default', -2 );
							$row->type					=	(int) ( $type ? $type : $gj_plugin->params->get( 'group_type_default', 1 ) );
							$row->nested				=	(int) $gj_plugin->params->get( 'group_nested_default', 1 );
							$row->nested_access			=	(int) $gj_plugin->params->get( 'group_nestedaccess_default', -1 );
							$row->date					=	date( 'Y-m-d H:i:s' );
							$row->ordering				=	1;

							if ( $row->store() ) {
								$row->storeOwner( $row->user_id );
							}
						} elseif ( $join ) {
							$usr						=	cbgjData::getUsers( null, null, array( array( 'group', '=', $row->id ), array( 'user_id', '=', $user->id ) ), null, null, false );

							if ( ! $usr->id ) {
								$usr->user_id			=	(int) $user->id;
								$usr->group				=	(int) $row->id;
								$usr->date				=	date( 'Y-m-d H:i:s' );
								$usr->status			=	(int) cbgjClass::getCleanParam( true, 'cbgj_auto_grp_usr_status' . $i, 1, null, $integrationParams );

								if ( $usr->store() ) {
									if ( $usr->status == 4 ) {
										$row->storeOwner( $usr->user_id );
									}
								}
							}
						}
					}
				} elseif ( ( cbgjClass::getCleanParam( true, 'cbgj_auto_type' . $i, null, null, $integrationParams ) == 1 ) && cbgjClass::getCleanParam( true, 'cbgj_auto_usr_groups' . $i, null, null, $integrationParams ) ) {
					$groups								=	cbgjClass::getCleanParam( true, 'cbgj_auto_usr_groups' . $i, null, null, $integrationParams );

					if ( $groups ) {
						$groups							=	explode( '|*|', $groups );

						cbArrayToInts( $groups );
					}

					if ( $groups ) foreach ( $groups as $group_id ) {
						$group							=	cbgjData::getGroups( null, null, array( 'id', '=', $group_id ), null, null, false );

						if ( $group->id ) {
							$row						=	cbgjData::getUsers( null, null, array( array( 'group', '=', $group->id ), array( 'user_id', '=', $user->id ) ), null, null, false );

							if ( ! $row->id ) {
								$row->user_id			=	(int) $user->id;
								$row->group				=	(int) $group->id;
								$row->date				=	date( 'Y-m-d H:i:s' );
								$row->status			=	(int) cbgjClass::getCleanParam( true, 'cbgj_auto_usr_status' . $i, 1, null, $integrationParams );

								if ( $row->store() ) {
									if ( $row->status == 4 ) {
										$group->storeOwner( $row->user_id);
									}
								}
							}
						}
					}
				}
			} elseif ( cbgjClass::getCleanParam( true, 'cbgj_auto_remove' . $i, null, null, $integrationParams ) ) {
				if ( ( cbgjClass::getCleanParam( true, 'cbgj_auto_type' . $i, null, null, $integrationParams ) == 3 ) && cbgjClass::getCleanParam( true, 'cbgj_auto_name' . $i, null, null, $integrationParams ) ) {
					$name								=	$cbUser->replaceUserVars( cbgjClass::getCleanParam( true, 'cbgj_auto_name' . $i, null, null, $integrationParams ) );

					if ( cbgjClass::getCleanParam( true, 'cbgj_auto_unique' . $i, 1, null, $integrationParams ) ) {
						$where							=	array( array( 'user_id', '=', $user->id ), array( 'name', '=', $name ) );
					} else {
						$where							=	array( 'name', '=', $name );
					}

					$row								=	cbgjData::getCategories( null, null, $where, null, null, false );

					if ( $row->id ) {
						$row->deleteAll();
					}
				} elseif ( ( cbgjClass::getCleanParam( true, 'cbgj_auto_type' . $i, null, null, $integrationParams ) == 2 ) && cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat' . $i, null, null, $integrationParams ) && cbgjClass::getCleanParam( true, 'cbgj_auto_name' . $i, null, null, $integrationParams ) ) {
					$name								=	$cbUser->replaceUserVars( cbgjClass::getCleanParam( true, 'cbgj_auto_name' . $i, null, null, $integrationParams ) );

					if ( cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat' . $i, null, null, $integrationParams ) == -1 ) {
						if ( cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat_unique' . $i, 1, null, $integrationParams ) ) {
							$where						=	array( array( 'user_id', '=', $user->id ), array( 'name', '=', $name ) );
						} else {
							$where						=	array( 'name', '=', $name );
						}
					} else {
						$category						=	cbgjData::getCategories( null, null, array( 'id', '=', cbgjClass::getCleanParam( true, 'cbgj_auto_grp_cat' . $i, null, null, $integrationParams ) ), null, null, false );

						if ( cbgjClass::getCleanParam( true, 'cbgj_auto_unique' . $i, 1, null, $integrationParams ) ) {
							$where						=	array( array( 'category', '=', $category->id ), array( 'user_id', '=', $user->id ), array( 'name', '=', $name ) );
						} else {
							$where						=	array( array( 'category', '=', $category->id ), array( 'name', '=', $name ) );
						}
					}

					$row								=	cbgjData::getGroups( null, null, $where, null, null, false );

					if ( $row->id ) {
						$row->deleteAll();
					}
				} elseif ( ( cbgjClass::getCleanParam( true, 'cbgj_auto_type' . $i, null, null, $integrationParams ) == 1 ) && cbgjClass::getCleanParam( true, 'cbgj_auto_usr_groups' . $i, null, null, $integrationParams ) ) {
					$groups								=	cbgjClass::getCleanParam( true, 'cbgj_auto_usr_groups' . $i, null, null, $integrationParams );

					if ( $groups ) {
						$groups							=	explode( '|*|', $groups );

						cbArrayToInts( $groups );
					}

					if ( $groups ) foreach ( $groups as $group_id ) {
						$group							=	cbgjData::getGroups( null, null, array( 'id', '=', $group_id ), null, null, false );

						if ( $group->id ) {
							$row						=	cbgjData::getUsers( null, null, array( array( 'group', '=', $group->id ), array( 'user_id', '=', $user->id ) ), null, null, false );

							if ( $row->id ) {
								$row->deleteAll();
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Function for the backend XML
	 *
	 * @param  string  $name          Name of the control
	 * @param  string  $value         Current value
	 * @param  string  $control_name  Name of the controlling array (if any)
	 * @return string                 HTML for the control data part or FALSE in case of error
	 */
	public function loadGJCategoryList( $name, $value, $control_name ) {
 		global $_CB_framework;

		$api				=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbgroupjive/cbgroupjive.class.php';

		if ( ! file_exists( $api ) ) {
			return CBPTXT::Th( 'GroupJive 2.x is not installed!' );
		}

		/** @noinspection PhpIncludeInspection */
		require_once( $api );

		$gj_categories		=	cbgjClass::getCategoryOptions( null );

		if ( $gj_categories ) {
			array_unshift( $gj_categories, moscomprofilerHTML::makeOption( '-1', CBPTXT::T( 'New Category' ) ) );
			array_unshift( $gj_categories, moscomprofilerHTML::makeOption( '', CBPTXT::T( '- Select Category -' ) ) );

			if ( isset( $value ) ) {
				$valAsObj	=	array_map( create_function( '$v', '$o=new stdClass(); $o->value=$v; return $o;' ), explode( '|*|', $value ) );
			} else {
				$valAsObj	=	null;
			}

			$categories		=	moscomprofilerHTML::selectList( $gj_categories, $control_name ? $control_name .'['. $name .'][]' : $name, null, 'value', 'text', $valAsObj, 0, false );
		} else {
			$categories		=	CBPTXT::T( 'No categories exist!' );;
		}

		return $categories;
	}

	/**
	 * Function for the backend XML
	 *
	 * @param  string  $name          Name of the control
	 * @param  string  $value         Current value
	 * @param  string  $control_name  Name of the controlling array (if any)
	 * @return string                 HTML for the control data part or FALSE in case of error
	 */
	public function loadGJParentCategoryList( $name, $value, $control_name ) {
 		global $_CB_framework;

		$api				=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbgroupjive/cbgroupjive.class.php';

		if ( ! file_exists( $api ) ) {
			return CBPTXT::Th( 'GroupJive 2.x is not installed!' );
		}

		/** @noinspection PhpIncludeInspection */
		require_once( $api );

		$gj_categories		=	cbgjClass::getCategoryOptions( null );

		if ( $gj_categories ) {
			array_unshift( $gj_categories, moscomprofilerHTML::makeOption( '0', CBPTXT::T( 'No Parent' ) ) );

			if ( isset( $value ) ) {
				$valAsObj	=	array_map( create_function( '$v', '$o=new stdClass(); $o->value=$v; return $o;' ), explode( '|*|', $value ) );
			} else {
				$valAsObj	=	null;
			}

			$categories		=	moscomprofilerHTML::selectList( $gj_categories, $control_name ? $control_name .'['. $name .'][]' : $name, null, 'value', 'text', $valAsObj, 0, false, false );
		} else {
			$categories		=	CBPTXT::T( 'No categories exist!' );;
		}

		return $categories;
	}

	/**
	 * Function for the backend XML
	 *
	 * @param  string  $name          Name of the control
	 * @param  string  $value         Current value
	 * @param  string  $control_name  Name of the controlling array (if any)
	 * @return string                 HTML for the control data part or FALSE in case of error
	 */
	public function loadGJGroupsList( $name, $value, $control_name ) {
 		global $_CB_framework;

		$api							=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbgroupjive/cbgroupjive.class.php';

		if ( ! file_exists( $api ) ) {
			return CBPTXT::Th( 'GroupJive 2.x is not installed!' );
		}

		/** @noinspection PhpIncludeInspection */
		require_once( $api );

		$list_gj_groups					=	cbgjClass::getGroupOptions( null );

		if ( $list_gj_groups ) {
			array_unshift( $list_gj_groups, moscomprofilerHTML::makeOption( '', CBTxt::T( '- Select Groups -' ) ) );

			if ( isset( $value ) ) {
				$valAsObj				=	array_map( create_function( '$v', '$o=new stdClass(); $o->value=$v; return $o;' ), explode( '|*|', $value ) );
			} else {
				$valAsObj				=	null;
			}

			$groups						=	moscomprofilerHTML::selectList( $list_gj_groups, $control_name ? $control_name .'['. $name .'][]' : $name, 'size="6" multiple="multiple"', 'value', 'text', $valAsObj, 0, false );
		} else {
			$groups						=	CBPTXT::T( 'No groups exist!' );;
		}

		return $groups;
	}

	/**
	 * Function for the backend XML
	 *
	 * @param  string  $name          Name of the control
	 * @param  string  $value         Current value
	 * @param  string  $control_name  Name of the controlling array (if any)
	 * @return string                 HTML for the control data part or FALSE in case of error
	 */
	public function loadGJParentGroupsList( $name, $value, $control_name ) {
 		global $_CB_framework;

		$api							=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbgroupjive/cbgroupjive.class.php';

		if ( ! file_exists( $api ) ) {
			return CBPTXT::Th( 'GroupJive 2.x is not installed!' );
		}

		/** @noinspection PhpIncludeInspection */
		require_once( $api );

		$list_gj_groups					=	cbgjClass::getGroupOptions( null );

		if ( $list_gj_groups ) {
			array_unshift( $list_gj_groups, moscomprofilerHTML::makeOption( '0', CBTxt::T( 'No Parent' ) ) );

			if ( isset( $value ) ) {
				$valAsObj				=	array_map( create_function( '$v', '$o=new stdClass(); $o->value=$v; return $o;' ), explode( '|*|', $value ) );
			} else {
				$valAsObj				=	null;
			}

			$groups						=	moscomprofilerHTML::selectList( $list_gj_groups, $control_name ? $control_name .'['. $name .'][]' : $name, null, 'value', 'text', $valAsObj, 0, false, false );
		} else {
			$groups						=	CBPTXT::T( 'No groups exist!' );;
		}

		return $groups;
	}
}
