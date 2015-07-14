<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\TabTable;
use CB\Database\Table\FieldTable;
use CB\Database\Table\UserTable;
use CB\Database\Table\ListTable;
use CBLib\Registry\Registry;
use CBLib\Registry\ParamsInterface;
use CBLib\Application\Application;
use CBLib\Registry\GetterInterface;
use CBLib\Language\CBTxt;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onBeforeDisplayUsersList', 'getList', 'cbconditionalPlugin' );
$_PLUGINS->registerFunction( 'onBeforegetFieldRow', 'fieldDisplay', 'cbconditionalPlugin' );
$_PLUGINS->registerFunction( 'onAfterEditATab', 'tabEdit', 'cbconditionalPlugin' );
$_PLUGINS->registerFunction( 'onAfterTabsFetch', 'tabsFetch', 'cbconditionalPlugin' );
$_PLUGINS->registerFunction( 'onAfterFieldsFetch', 'fieldsFetch', 'cbconditionalPlugin' );

class cbconditionalPlugin extends cbPluginHandler
{

	/**
	 * @param string     $value
	 * @param string|int $operator
	 * @param string     $input
	 * @return bool
	 */
	private function getMatch( $value, $operator, $input )
	{
		if ( is_array( $value ) ) {
			$value		=	implode( '|*|', $value );
		}

		$value			=	trim( $value );
		$input			=	trim( $input );

		switch ( $operator ) {
			case 1:
				$match	=	( $value != $input );
				break;
			case 2:
				$match	=	( $value > $input );
				break;
			case 3:
				$match	=	( $value < $input );
				break;
			case 4:
				$match	=	( $value >= $input );
				break;
			case 5:
				$match	=	( $value <= $input );
				break;
			case 6:
				$match	=	( ! $value );
				break;
			case 7:
				$match	=	( $value );
				break;
			case 8:
				$match	=	( stristr( $value, $input ) );
				break;
			case 9:
				$match	=	( ! stristr( $value, $input ) );
				break;
			case 10:
				$match	=	( preg_match( $input, $value ) );
				break;
			case 11:
				$match	=	( ! preg_match( $input, $value ) );
				break;
			case 0:
			default:
				$match	=	( $value == $input );
				break;
		}

		return (bool) $match;
	}

	/**
	 * @param string $param
	 * @return array
	 */
	private function getFieldsArray( $param )
	{
		if ( $param ) {
			$param	=	explode( '|*|', $param );

			cbArrayToInts( $param );

			$param	=	array_values( array_unique( $param ) );
		}

		if ( ! is_array( $param ) ) {
			$param	=	array();
		}

		return $param;
	}

	/**
	 * @param null|string|int|TabTable[] $tabs
	 * @param string                     $reason
	 * @param int                        $userId
	 * @param bool                       $jquery
	 * @param string                     $formatting
	 * @param bool                       $tabbed
	 * @return array
	 */
	private function getTabConditional( $tabs, $reason, $userId, $jquery = false, $formatting = 'table', $tabbed = true )
	{
		global $_CB_database, $_CB_framework;

		$disabled													=	array();

		static $userCache											=	array();

		if ( ! isset( $userCache[$userId] ) ) {
			$cbUser													=	CBuser::getInstance( (int) $userId, false );
			$cmsUser												=	Application::User( (int) $userId );

			$userCache[$userId]										=	array( $cbUser, $cbUser->getUserData(), $cmsUser->getAuthorisedViewLevels(), $cmsUser->getAuthorisedGroups() );
		}

		/** @var CBuser $cbUser */
		$cbUser														=	$userCache[$userId][0];
		/** @var UserTable $user */
		$user														=	$userCache[$userId][1];
		/** @var array $userAccessLevels */
		$userAccessLevels											=	$userCache[$userId][2];
		/** @var array $userUsergroups */
		$userUsergroups												=	$userCache[$userId][3];

		static $tabCache											=	array();

		if ( ! $tabs ) {
			/** @var TabTable[] $tabsCache */
			static $tabsCache										=	array();

			if ( ! isset( $tabsCache[$user->id] ) ) {
				$cbTabs												=	$cbUser->_getCbTabs();
				$tabsCache[$user->id]								=	$cbTabs->_getTabsDb( $user, 'adminfulllist' );
			}

			$tabs													=	$tabsCache[$user->id];
		} elseif ( ! is_array( $tabs ) ) {
			if ( is_string( $tabs ) || is_integer( $tabs ) ) {
				$tabId												=	(int) $tabs;

				if ( $tabId ) {
					if ( ! isset( $tabCache[$tabId] ) ) {
						$tab										=	new TabTable();

						$tab->load( $tabId );

						$tabCache[$tabId]							=	$tab;
					}

					$tabs											=	$tabCache[$tabId];
				}
			}

			$tabs													=	array( $tabs );
		} elseif ( is_array( $tabs ) ) {
			$tabArray												=	array();

			foreach ( $tabs as $tabId ) {
				if ( is_string( $tabId ) || is_integer( $tabId ) ) {
					$tabId											=	(int) $tabId;

					if ( $tabId ) {
						if ( ! isset( $tabCache[$tabId] ) ) {
							$tab									=	new TabTable();

							$tab->load( $tabId );

							$tabCache[$tabId]						=	$tab;
						}

						$tabArray[]									=	$tabCache[$tabId];
					}
				} elseif ( $tabId instanceof TabTable ) {
					$tabArray[]										=	$tabId;
				}
			}

			$tabs													=	$tabArray;
		}

		/** @var Registry[] $tabParams */
		static $tabParams											=	array();
		/** @var FieldTable[] $fields */
		static $fields												=	array();
		/** @var FieldTable[] $tabFields */
		static $tabFields											=	array();
		/** @var array[] $conditioned */
		static $conditioned											=	array();

		$uId														=	(int) $user->get( 'id' );

		if ( $tabs ) foreach ( $tabs as $tab ) {
			if ( $tab instanceof TabTable ) {
				$tId												=	(int) $tab->get( 'tabid' );

				if ( ! isset( $conditioned[$tId][$uId][$reason][$jquery] ) ) {
					$tabConditions									=	array();

					$conditioned[$tId][$uId][$reason][$jquery]		=	$tabConditions;

					if ( ! isset( $tabParams[$tId] ) ) {
						if ( ! ( $tab->params instanceof ParamsInterface ) ) {
							$tab->params							=	new Registry( $tab->params );
						}

						$tabParams[$tId]							=	$tab->params;
					}

					$params											=	$tabParams[$tId];

					for ( $i = 1; $i <= 5; $i++ ) {
						$conditional								=	( $i > 1 ? $i : null );
						$display									=	(int) $params->get( 'cbconditional_display' . $conditional, 0 );

						if ( $reason == 'profile' ) {
							if ( ! $params->get( 'cbconditional_target_view' . $conditional, 1 ) ) {
								$display							=	0;
							}
						} elseif ( $reason == 'edit' ) {
							if ( ! $params->get( 'cbconditional_target_edit' . $conditional, 0 ) ) {
								$display							=	0;
							}
						} elseif ( $reason == 'register' ) {
							if ( ! $params->get( 'cbconditional_target_reg' . $conditional, 0 ) ) {
								$display							=	0;
							}
						}

						if ( $display ) {
							$fieldName								=	$params->get( 'cbconditional_field' . $conditional, null );

							if ( $fieldName ) {
								$operator							=	(int) $params->get( 'cbconditional_operator' . $conditional, 0 );
								$value								=	$cbUser->replaceUserVars( $params->get( 'cbconditional_value' . $conditional, null ), false, true, $this->getExtras(), ( (int) $params->get( 'cbconditional_value_translate' . $conditional, 0 ) ? true : false ) );

								if ( in_array( $operator, array( '6', '7' ) ) ) {
									$value							=	null;
								}

								$mode								=	(int) $params->get( 'cbconditional_mode' . $conditional, 0 );

								switch ( $fieldName ) {
									case 'customvalue':
										$fieldValue					=	$cbUser->replaceUserVars( $params->get( 'cbconditional_customvalue' . $conditional, null ), false, true, $this->getExtras(), ( (int) $params->get( 'cbconditional_customvalue_translate' . $conditional, 0 ) ? true : false ) );
										break;
									case 'customviewaccesslevels':
										$accessLevels				=	cbToArrayOfInt( explode( '|*|', $params->get( 'cbconditional_customviewaccesslevels' . $conditional, null ) ) );
										$fieldValue					=	0;

										foreach ( $accessLevels as $accessLevel ) {
											if ( in_array( $accessLevel, $userAccessLevels ) ) {
												$fieldValue			=	1;
												break;
											}
										}

										$operator					=	0;
										$value						=	1;
										break;
									case 'customusergroups':
										$userGroups					=	cbToArrayOfInt( explode( '|*|', $params->get( 'cbconditional_customusergroups' . $conditional, null ) ) );
										$fieldValue					=	0;

										foreach ( $userGroups as $userGroup ) {
											if ( in_array( $userGroup, $userUsergroups ) ) {
												$fieldValue			=	1;
												break;
											}
										}

										$operator					=	0;
										$value						=	1;
										break;
									default:
										if ( ! isset( $fields[$fieldName] ) ) {
											$field					=	new FieldTable();

											$field->load( array( 'name' => $fieldName ) );

											$fields[$fieldName]		=	$field;
										}

										$fieldValue					=	$this->getFieldValue( $user, $cbUser, $fields[$fieldName], $reason );
										break;
								}

								if ( $jquery ) {
									$_CB_framework->addJQueryPlugin( 'cbcondition', '/components/com_comprofiler/plugin/user/plug_cbconditional/js/cbcondition.js' );

									$js								=	"var tabCondition = ['#cbtp_$tId'];";

									if ( $tabbed ) {
										$js							.=	"tabCondition.push( '#cbtabpane$tId' );";
									} else {
										if ( in_array( $formatting, array( 'tables', 'divs' ) ) ) {
											$js						.=	"tabCondition.push( '#cbtf_$tId' );";
										} else {
											if ( ! isset( $tabFields[$tId] ) ) {
												$query				=	'SELECT *'
																	.	"\n FROM " .  $_CB_database->NameQuote( '#__comprofiler_fields' )
																	.	"\n WHERE " . $_CB_database->NameQuote( 'tabid' ) . " = " . (int) $tId;
												$_CB_database->setQuery( $query );
												$tabFields[$tId]	=	$_CB_database->loadObjectList( null, '\CB\Database\Table\FieldTable', array( $_CB_database ) );
											}

											foreach ( $tabFields[$tId] as $tabField ) {
												/** @var  FieldTable $tabField */
												$fId				=	(int) $tabField->get( 'fieldid' );

												$js					.=	"tabCondition.push( '#cbfr_$fId,#cbfr_' . $fId . '__verify,#cbfrd_$fId,#cbfrd_' . $fId . '__verify' );";
											}
										}
									}

									switch ( $fieldName ) {
										case 'customvalue':
										case 'customviewaccesslevels':
										case 'customusergroups':
											$js						.=	"$.cbcondition({"
																	.		"conditions: [{"
																	.			"operator: " . (int) $operator . ","
																	.			"input: '" . addslashes( str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), ( is_array( $fieldValue ) ? implode( '|*|', $fieldValue ) : $fieldValue ) ) ) . "',"
																	.			"value: '" . addslashes( str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), ( is_array( $value ) ? implode( '|*|', $value ) : $value ) ) ) . "',"
																	.			( $mode ? "show: tabCondition," : "hide: tabCondition," )
																	.			"reset: " . (int) $this->params->get( 'cond_reset', 0 ) . ""
																	.		"}]"
																	.	"});";
											break;
										default:
											$fieldId				=	$fields[$fieldName]->get( 'fieldid' );

											if ( $fieldId ) {
												$js					.=	"$( '#cbfr_" . (int) $fieldId . ",#cbfrd_" . (int) $fieldId . "' ).cbcondition({"
																	.		"conditions: [{"
																	.			"operator: " . (int) $operator . ","
																	.			"input: '" . addslashes( str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), ( is_array( $fieldValue ) ? implode( '|*|', $fieldValue ) : $fieldValue ) ) ) . "',"
																	.			"value: '" . addslashes( str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), ( is_array( $value ) ? implode( '|*|', $value ) : $value ) ) ) . "',"
																	.			( $mode ? "show: tabCondition," : "hide: tabCondition," )
																	.			"reset: " . (int) $this->params->get( 'cond_reset', 0 ) . ""
																	.		"}]"
																	.	"});";
											}
											break;
									}

									$_CB_framework->outputCbJQuery( $js, 'cbcondition' );
								}

								$tabConditions[]					=	array(	'match' => $this->getMatch( $fieldValue, $operator, $value ),
																				'mode' => $mode,
																				'tab' => $tId
																			);
							}
						}
					}

					$conditioned[$tId][$uId][$reason][$jquery]		=	$tabConditions;
				}

				$conditions											=	$conditioned[$tId][$uId][$reason][$jquery];

				foreach ( $conditions as $cond ) {
					if ( $cond['match'] ) {
						if ( ( ! $cond['mode'] ) && ( ! in_array( $cond['tab'], $disabled ) ) ) {
							array_push( $disabled, $cond['tab'] );
						}
					} else {
						if ( $cond['mode'] && ( ! in_array( $cond['tab'], $disabled ) ) ) {
							array_push( $disabled, $cond['tab'] );
						}
					}
				}
			}
		}

		return $disabled;
	}

	/**
	 * @param null|string|int|FieldTable[] $fields
	 * @param string                       $reason
	 * @param int                          $userId
	 * @param bool                         $jquery
	 * @return stdClass
	 */
	private function getFieldConditional( $fields, $reason, $userId, $jquery = false )
	{
		global $_CB_framework;

		$condition													=	new stdClass();
		$condition->show											=	array();
		$condition->hide											=	array();

		static $userCache											=	array();

		if ( ! isset( $userCache[$userId] ) ) {
			$cbUser													=	CBuser::getInstance( (int) $userId, false );
			$cmsUser												=	Application::User( (int) $userId );

			$userCache[$userId]										=	array( $cbUser, $cbUser->getUserData(), $cmsUser->getAuthorisedViewLevels(), $cmsUser->getAuthorisedGroups() );
		}

		/** @var CBuser $cbUser */
		$cbUser														=	$userCache[$userId][0];
		/** @var UserTable $user */
		$user														=	$userCache[$userId][1];
		/** @var array $userAccessLevels */
		$userAccessLevels											=	$userCache[$userId][2];
		/** @var array $userUsergroups */
		$userUsergroups												=	$userCache[$userId][3];

		static $fieldCache											=	array();

		if ( ! $fields ) {
			/** @var FieldTable[] $tabsCache */
			static $tabsCache										=	array();

			if ( ! isset( $tabsCache[$user->id] ) ) {
				$cbTabs												=	$cbUser->_getCbTabs();
				$tabsCache[$user->id]								=	$cbTabs->_getTabFieldsDb( null, $user, 'adminfulllist', null, true, true );
			}

			$fields													=	$tabsCache[$user->id];
		} elseif ( ! is_array( $fields ) ) {
			if ( is_string( $fields ) || is_integer( $fields ) ) {
				$fieldId											=	(int) $fields;

				if ( $fieldId ) {
					if ( ! isset( $fieldCache[$fieldId] ) ) {
						$field										=	new FieldTable();

						$field->load( $fieldId );

						$fieldCache[$fieldId]						=	$field;
					}

					$fields											=	$fieldCache[$fieldId];
				}
			}

			$fields													=	array( $fields );
		} elseif ( is_array( $fields ) ) {
			$fieldArray												=	array();

			foreach ( $fields as $fieldId ) {
				if ( is_string( $fieldId ) || is_integer( $fieldId ) ) {
					$fieldId										=	(int) $fieldId;

					if ( $fieldId ) {
						if ( ! isset( $fieldCache[$fieldId] ) ) {
							$field									=	new FieldTable();

							$field->load( $fieldId );

							$fieldCache[$fieldId]					=	$field;
						}

						$fieldArray[]								=	$fieldCache[$fieldId];
					}
				} elseif ( $fieldId instanceof FieldTable ) {
					$fieldArray[]									=	$fieldId;
				}
			}

			$fields													=	$fieldArray;
		}

		/** @var Registry[] $fieldParams */
		static $fieldParams											=	array();
		/** @var array[] $conditioned */
		static $conditioned											=	array();

		$uId														=	(int) $user->get( 'id' );

		if ( $fields ) foreach ( $fields as $field ) {
			if ( $field instanceof FieldTable ) {
				$fId												=	(int) $field->get( 'fieldid' );

				if ( ! isset( $conditioned[$fId][$uId][$reason][$jquery] ) ) {
					$fieldConditions								=	array();

					$conditioned[$fId][$uId][$reason][$jquery]		=	$fieldConditions;

					if ( ! isset( $fieldParams[$fId] ) ) {
						if ( ! ( $field->params instanceof ParamsInterface ) ) {
							$field->params							=	new Registry( $field->params );
						}

						$fieldParams[$fId]							=	$field->params;
					}

					$params											=	$fieldParams[$fId];

					for ( $i = 1; $i <= 5; $i++ ) {
						$conditional								=	( $i > 1 ? $i : null );
						$display									=	(int) $params->get( 'cbconditional_display' . $conditional, 0 );

						if ( $reason == 'register' ) {
							if ( ! $params->get( 'cbconditional_target_reg' . $conditional, 1 ) ) {
								$display							=	0;
							}
						} elseif ( $reason == 'edit' ) {
							if ( ! $params->get( 'cbconditional_target_edit' . $conditional, 1 ) ) {
								$display							=	0;
							}
						} elseif ( $reason == 'profile' ) {
							if ( ! $params->get( 'cbconditional_target_view' . $conditional, 1 ) ) {
								$display							=	0;
							}
						} elseif ( $reason == 'search' ) {
							if ( ! $params->get( 'cbconditional_target_search' . $conditional, 0 ) ) {
								$display							=	0;
							}
						} elseif ( $reason == 'list' ) {
							if ( ! $params->get( 'cbconditional_target_list' . $conditional, 1 ) ) {
								$display							=	0;
							}
						}

						if ( $display ) {
							if ( $display == 2 ) {
								$mode								=	(int) $params->get( 'cbconditional_mode' . $conditional, 0 );
								$show								=	$this->getFieldsArray( ( $mode == 1 ? $fId : null ) );
								$hide								=	$this->getFieldsArray( ( $mode == 0 ? $fId : null ) );
								$optshow							=	array();
								$opthide							=	array();

								$fieldPair							=	explode( ',', $params->get( 'cbconditional_field' . $conditional, null ) );

								if ( count( $fieldPair ) < 2 ) {
									array_unshift( $fieldPair, 0 );
								}

								$fieldId							=	(int) array_shift( $fieldPair );
								$fieldName							=	array_pop( $fieldPair );

								if ( ! isset( $fields[$fieldId] ) ) {
									$field							=	new FieldTable();

									$field->load( $fieldId );

									$fields[$fieldId]				=	$field;
								}

								$fieldObj							=	$fields[$fieldId];
							} else {
								$show								=	$this->getFieldsArray( $params->get( 'cbconditional_show' . $conditional, null ) );
								$hide								=	$this->getFieldsArray( $params->get( 'cbconditional_hide' . $conditional, null ) );
								$optshow							=	$this->getFieldsArray( $params->get( 'cbconditional_options_show' . $conditional, null ) );
								$opthide							=	$this->getFieldsArray( $params->get( 'cbconditional_options_hide' . $conditional, null ) );

								$fieldId							=	(int) $field->get( 'fieldid' );
								$fieldName							=	$field->get( 'name' );
								$fieldObj							=	$field;
							}

							if ( $show || $hide || $optshow || $opthide ) {
								$operator							=	(int) $params->get( 'cbconditional_operator' . $conditional, 0 );
								$value								=	$cbUser->replaceUserVars( $params->get( 'cbconditional_value' . $conditional, null ), false, true, $this->getExtras(), ( (int) $params->get( 'cbconditional_value_translate' . $conditional, 0 ) ? true : false ) );

								if ( in_array( $operator, array( 6, 7 ) ) ) {
									$value							=	null;
								}

								switch ( $fieldName ) {
									case 'customvalue':
										$fieldValue					=	$cbUser->replaceUserVars( $params->get( 'cbconditional_customvalue' . $conditional, null ), false, true, $this->getExtras(), ( (int) $params->get( 'cbconditional_customvalue_translate' . $conditional, 0 ) ? true : false ) );
										break;
									case 'customviewaccesslevels':
										$accessLevels				=	cbToArrayOfInt( explode( '|*|', $params->get( 'cbconditional_customviewaccesslevels' . $conditional, null ) ) );
										$fieldValue					=	0;

										foreach ( $accessLevels as $accessLevel ) {
											if ( in_array( $accessLevel, $userAccessLevels ) ) {
												$fieldValue			=	1;
												break;
											}
										}

										$operator					=	0;
										$value						=	1;
										break;
									case 'customusergroups':
										$userGroups					=	cbToArrayOfInt( explode( '|*|', $params->get( 'cbconditional_customusergroups' . $conditional, null ) ) );
										$fieldValue					=	0;

										foreach ( $userGroups as $userGroup ) {
											if ( in_array( $userGroup, $userUsergroups ) ) {
												$fieldValue			=	1;
												break;
											}
										}

										$operator					=	0;
										$value						=	1;
										break;
									default:
										$fieldValue					=	$this->getFieldValue( $user, $cbUser, $fieldObj, $reason );
										break;
								}

								if ( $jquery ) {
									$_CB_framework->addJQueryPlugin( 'cbcondition', '/components/com_comprofiler/plugin/user/plug_cbconditional/js/cbcondition.js' );

									$js								=	"var conditionShow = [];"
																	.	"var conditionHide = [];";

									foreach ( $show as $v ) {
										$js							.=	"conditionShow.push( '#cbfr_$v,#cbfr_" . $v . "__verify,#cbfrd_$v,#cbfrd_" . $v . "__verify' );";
									}

									foreach ( $hide as $k => $v ) {
										$js							.=	"conditionHide.push( '#cbfr_$v,#cbfr_" . $v . "__verify,#cbfrd_$v,#cbfrd_" . $v . "__verify' );";
									}

									foreach ( $optshow as $k => $v ) {
										$js							.=	"conditionShow.push( '#cbf$v' );";
									}

									foreach ( $opthide as $k => $v ) {
										$js							.=	"conditionHide.push( '#cbf$v' );";
									}

									switch ( $fieldName ) {
										case 'customvalue':
										case 'customviewaccesslevels':
										case 'customusergroups':
											$js						.=	"$.cbcondition({"
																	.		"conditions: [{"
																	.			"operator: " . (int) $operator . ","
																	.			"input: '" . addslashes( str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), ( is_array( $fieldValue ) ? implode( '|*|', $fieldValue ) : $fieldValue ) ) ) . "',"
																	.			"value: '" . addslashes( str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), ( is_array( $value ) ? implode( '|*|', $value ) : $value ) ) ) . "',"
																	.			"show: conditionShow,"
																	.			"hide: conditionHide,"
																	.			"reset: " . (int) $this->params->get( 'cond_reset', 0 ) . ""
																	.		"}]"
																	.	"});";
											break;
										default:
											$js						.=	"$( '#cbfr_" . (int) $fieldId . ",#cbfrd_" . (int) $fieldId . "' ).cbcondition({"
																	.		"conditions: [{"
																	.			"operator: " . (int) $operator . ","
																	.			"input: '" . addslashes( str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), ( is_array( $fieldValue ) ? implode( '|*|', $fieldValue ) : $fieldValue ) ) ) . "',"
																	.			"value: '" . addslashes( str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), ( is_array( $value ) ? implode( '|*|', $value ) : $value ) ) ) . "',"
																	.			"show: conditionShow,"
																	.			"hide: conditionHide,"
																	.			"reset: " . (int) $this->params->get( 'cond_reset', 0 ) . ""
																	.		"}]"
																	.	"});";
											break;
									}

									$_CB_framework->outputCbJQuery( $js, 'cbcondition' );
								}

								$fieldConditions[]					=	array(	'match' => $this->getMatch( $fieldValue, $operator, $value ),
																				'show' => $show,
																				'hide' => $hide
																			);
							}
						}
					}

					$conditioned[$fId][$uId][$reason][$jquery]		=	$fieldConditions;
				}

				$conditions											=	$conditioned[$fId][$uId][$reason][$jquery];

				foreach ( $conditions as $cond ) {
					if ( $cond['match'] ) {
						foreach ( $cond['show'] as $v ) {
							$v										=	(int) $v;

							if ( in_array( $v, $condition->hide ) ) {
								unset( $condition->hide[$v] );
							}

							if ( ! in_array( $v, $condition->show ) ) {
								array_push( $condition->show, $v );
							}
						}

						foreach ( $cond['hide'] as $v ) {
							$v										=	(int) $v;

							if ( in_array( $v, $condition->show ) ) {
								unset( $condition->show[$v] );
							}

							if ( ! in_array( $v, $condition->hide ) ) {
								array_push( $condition->hide, $v );
							}
						}
					} else {
						foreach ( $cond['show'] as $v ) {
							$v										=	(int) $v;

							if ( in_array( $v, $condition->show ) ) {
								unset( $condition->show[$v] );
							}

							if ( ! in_array( $v, $condition->hide ) ) {
								array_push( $condition->hide, $v );
							}
						}

						foreach ( $cond['hide'] as $v ) {
							$v										=	(int) $v;

							if ( in_array( $v, $condition->hide ) ) {
								unset( $condition->hide[$v] );
							}

							if ( ! in_array( $v, $condition->show ) ) {
								array_push( $condition->show, $v );
							}
						}
					}
				}
			}
		}

		return $condition;
	}

	/**
	 * @param UserTable  $user
	 * @param CBuser     $cbUser
	 * @param FieldTable $field
	 * @param string     $reason
	 * @param bool       $forceNoPost
	 * @return array|mixed|string
	 */
	private function getFieldValue( $user, $cbUser, $field, $reason, $forceNoPost = false )
	{
		global $_PLUGINS;

		static $values											=	array();

		$fieldId												=	(int) $field->get( 'fieldid' );
		$userId													=	(int) $user->get( 'id' );

		if ( ! isset( $values[$fieldId][$userId][$reason][$forceNoPost] ) ) {
			if ( ! ( $field->params instanceof ParamsInterface ) ) {
				$field->params									=	new Registry( $field->params );
			}

			$fieldValue											=	null;

			$values[$fieldId][$userId][$reason][$forceNoPost]	=	$fieldValue;

			$post												=	$this->getInput()->getNamespaceRegistry( 'post' );

			if ( ( ! $forceNoPost ) && in_array( $reason, array( 'register', 'edit' ) ) && ( $post->count() && in_array( $this->input( 'view', null, GetterInterface::STRING ), array( 'saveregisters', 'saveuseredit' ) ) ) ) {
				$postUser										=	new UserTable();

				foreach ( array_keys( get_object_vars( $user ) ) as $k ) {
					if ( substr( $k, 0, 1 ) != '_' ) {
						$postUser->set( $k, $user->get( $k ) );
					}
				}

				if ( ! $post->get( $field->get( 'name' ) ) ) {
					$post->set( $field->get( 'name' ), null );
				}

				$postUser->bindThisUserFromDbArray( $post->asArray() );

				$fieldValue										=	$postUser->get( $field->get( 'name' ) );

				if ( is_array( $fieldValue ) ) {
					$fieldValue									=	implode( '|*|', $fieldValue );
				}

				if ( $fieldValue === null ) {
					$field->set( '_noCondition', true );

					$fieldValue									=	$_PLUGINS->callField( $field->get( 'type' ), 'getFieldRow', array( &$field, &$postUser, 'php', 'none', 'profile', 0 ), $field );

					$field->set( '_noCondition', false );

					if ( is_array( $fieldValue ) ) {
						$fieldValue								=	array_shift( $fieldValue );

						if ( is_array( $fieldValue ) ) {
							$fieldValue							=	implode( '|*|', $fieldValue );
						}
					}

					if ( $fieldValue === null ) {
						$fieldValue								=	$this->getFieldValue( $user, $cbUser, $field, $reason, true );
					}
				}
			} else {
				$fieldValue										=	$user->get( $field->get( 'name' ) );

				if ( is_array( $fieldValue ) ) {
					$fieldValue									=	implode( '|*|', $fieldValue );
				}

				if ( $fieldValue === null ) {
					$field->set( '_noCondition', true );

					$fieldValue									=	$_PLUGINS->callField( $field->get( 'type' ), 'getFieldRow', array( &$field, &$user, 'php', 'none', 'profile', 0 ), $field );

					$field->set( '_noCondition', false );

					if ( is_array( $fieldValue ) ) {
						$fieldValue								=	array_shift( $fieldValue );

						if ( is_array( $fieldValue ) ) {
							$fieldValue							=	implode( '|*|', $fieldValue );
						}
					}
				}
			}

			$values[$fieldId][$userId][$reason][$forceNoPost]	=	$fieldValue;
		}

		return $values[$fieldId][$userId][$reason][$forceNoPost];
	}

	/**
	 * Parses substitution extras array from available variables
	 *
	 * @return array
	 */
	private function getExtras()
	{
		static $extras		=	array();

		if ( empty( $extras ) ) {
			$post			=	$this->getInput()->getNamespaceRegistry( 'post' );

			if ( $post ) {
				$this->prepareExtras( 'post', $post->asArray(), $extras );
			}

			$get			=	$this->getInput()->getNamespaceRegistry( 'get' );

			if ( $get ) {
				$this->prepareExtras( 'get', $get->asArray(), $extras );
			}
		}

		return $extras;
	}

	/**
	 * Converts array or object into pathed extras substitutions
	 *
	 * @param string       $prefix
	 * @param array|object $items
	 * @param array        $extras
	 */
	private function prepareExtras( $prefix, $items, &$extras )
	{
		foreach ( $items as $k => $v ) {
			if ( is_array( $v ) ) {
				$multi					=	false;

				foreach ( $v as $cv ) {
					if ( is_array( $cv ) ) {
						$multi			=	true;
					}
				}

				if ( ! $multi ) {
					$v					=	implode( '|*|', $v );
				}
			}

			if ( ( ! is_object( $v ) ) && ( ! is_array( $v ) ) ) {
				$k						=	'_' . ltrim( str_replace( ' ', '_', trim( strtolower( $k ) ) ), '_' );

				$extras[$prefix . $k]	=	$v;
			}
		}
	}

	/**
	 * @param ListTable    $row
	 * @param UserTable[]  $users
	 * @param array        $columns
	 * @param FieldTable[] $fields
	 * @param array        $input
	 * @param int          $listid
	 * @param string|null  $search
	 * @param int          $Itemid
	 * @param int          $ui
	 */
	public function getList( &$row, &$users, &$columns, &$fields, &$input, $listid, &$search, &$Itemid, $ui )
	{
		if ( ( ! Application::Cms()->getClientId() ) && ( $search !== null ) ) {
			$tabs								=	array();

			foreach ( $fields as $field ) {
				if ( ! in_array( (int) $field->get( 'tabid' ), $tabs ) ) {
					$tabs[]						=	(int) $field->get( 'tabid' );
				}
			}

			if ( $users ) foreach( $users as $k => $user ) {
				if ( isset( $users[$k] ) ) {
					$hide						=	array();

					if ( $tabs ) {
						$tabCondition			=	$this->getTabConditional( $tabs, 'list', $user->get( 'id' ) );

						if ( $tabCondition ) {
							foreach ( $fields as $field ) {
								if ( in_array( (int) $field->get( 'tabid' ), $tabCondition ) ) {
									$hide[]		=	(int) $field->get( 'fieldid' );
								}
							}
						}
					}

					if ( ! $hide ) {
						$condition				=	$this->getFieldConditional( $fields, 'list', $user->get( 'id' ) );

						if ( $condition->hide ) {
							foreach ( $fields as $field ) {
								if ( in_array( (int) $field->get( 'fieldid' ), $condition->hide ) ) {
									$hide[]		=	(int) $field->get( 'fieldid' );
								}
							}
						}
					}

					if ( $hide ) {
						foreach ( $fields as $field ) {
							if ( in_array( (int) $field->get( 'fieldid' ), $hide ) && ( $this->input( $field->get( 'name' ), null, GetterInterface::RAW ) != '' ) ) {
								unset( $users[$k] );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param string    $content
	 * @param TabTable  $tab
	 * @param UserTable $user
	 * @param array     $postdata
	 * @param string    $output
	 * @param string    $formatting
	 * @param string    $reason
	 * @param bool      $tabbed
	 */
	public function tabEdit( &$content, &$tab, &$user, &$postdata, $output, $formatting, $reason, $tabbed )
	{
		if ( ( ! Application::Cms()->getClientId() ) || $this->params->get( 'cond_backend', 0 ) ) {
			if ( ( $output == 'htmledit' ) && ( $reason != 'search' ) ) {
				$this->getTabConditional( $tab, $reason, $user->get( 'id' ), true, $formatting, $tabbed );
			}
		}
	}

	/**
	 * @param TabTable[] $tabs
	 * @param UserTable  $user
	 * @param string     $reason
	 */
	public function tabsFetch( &$tabs, &$user, $reason )
	{
		$post				=	$this->getInput()->getNamespaceRegistry( 'post' );
		$view				=	$this->input( 'view', null, GetterInterface::STRING );

		if ( ! Application::Cms()->getClientId() ) {
			$checkView		=	( ( in_array( $reason, array( 'register', 'edit' ) ) && ( $post->count() && in_array( $view, array( 'saveregisters', 'saveuseredit' ) ) ) ) || ( $reason == 'profile' ) );
		} elseif ( Application::Cms()->getClientId() && $this->params->get( 'cond_backend', 0 ) ) {
			$checkView		=	( ( in_array( $reason, array( 'register', 'edit' ) ) && ( $post->count() && ( $view != 'edit' ) ) ) || ( $reason == 'profile' ) );
		} else {
			$checkView		=	false;
		}

		if ( $checkView && $tabs && ( $user && ( $user instanceof UserTable ) && ( ! $user->getError() ) ) ) {
			$condition		=	$this->getTabConditional( $tabs, $reason, $user->get( 'id' ) );

			if ( $condition ) {
				foreach ( $tabs as $k => $tab ) {
					if ( in_array( (int) $tab->get( 'tabid' ), $condition ) ) {
						unset( $tabs[$k] );
					}
				}
			}
		}
	}

	/**
	 * @param FieldTable[] $fields
	 * @param UserTable    $user
	 * @param string       $reason
	 * @param int          $tabid
	 * @param int|string   $fieldIdOrName
	 * @param bool         $fullAccess
	 */
	public function fieldsFetch( &$fields, &$user, $reason, $tabid, $fieldIdOrName, $fullAccess )
	{
		$post							=	$this->getInput()->getNamespaceRegistry( 'post' );
		$view							=	$this->input( 'view', null, GetterInterface::STRING );

		if ( ( ! Application::Cms()->getClientId() ) && ( ! $fullAccess ) ) {
			$checkView					=	( ( in_array( $reason, array( 'register', 'edit' ) ) && ( $post->count() && in_array( $view, array( 'saveregisters', 'saveuseredit' ) ) ) ) || ( $reason == 'profile' ) );
		} elseif ( Application::Cms()->getClientId() && $this->params->get( 'cond_backend', 0 ) && ( ! $fullAccess ) ) {
			$checkView					=	( ( in_array( $reason, array( 'register', 'edit' ) ) && ( $post->count() && ( $view != 'edit' ) ) ) || ( $reason == 'profile' ) );
		} else {
			$checkView					=	false;
		}

		if ( $checkView && $fields && ( $user && ( $user instanceof UserTable ) && ( ! $user->getError() ) ) ) {
			$tabs						=	array();
			$hide						=	array();

			foreach ( $fields as $field ) {
				if ( ! in_array( (int) $field->get( 'tabid' ), $tabs ) ) {
					$tabs[]				=	(int) $field->get( 'tabid' );
				}
			}

			if ( $tabs ) {
				$tabCondition			=	$this->getTabConditional( $tabs, $reason, $user->get( 'id' ) );

				if ( $tabCondition ) {
					foreach ( $fields as $field ) {
						if ( in_array( (int) $field->get( 'tabid' ), $tabCondition ) ) {
							$hide[]		=	(int) $field->get( 'fieldid' );
						}
					}
				}
			}

			if ( ! $hide ) {
				$condition				=	$this->getFieldConditional( $fields, $reason, $user->get( 'id' ) );

				if ( $condition->hide ) {
					foreach ( $fields as $field ) {
						if ( in_array( (int) $field->get( 'fieldid' ), $condition->hide ) ) {
							$hide[]		=	(int) $field->get( 'fieldid' );
						}
					}
				}
			}

			if ( $hide ) {
				foreach ( $fields as $k => $field ) {
					if ( in_array( (int) $field->get( 'fieldid' ), $hide ) ) {
						unset( $fields[$k] );
					}
				}
			}
		}
	}

	/**
	 * @param FieldTable $field
	 * @param UserTable  $user
	 * @param string     $output
	 * @param string     $formatting
	 * @param string     $reason
	 * @param int        $list_compare_types
	 * @return mixed|null|string
	 */
	public function fieldDisplay( &$field, &$user, $output, $formatting, $reason, $list_compare_types )
	{
		$return							=	null;

		if ( ( ! $field->get( '_noCondition', false ) ) && ( ( ! Application::Cms()->getClientId() ) || $this->params->get( 'cond_backend', 0 ) ) ) {
			$field->set( '_noCondition', true );

			if ( $output == 'html' ) {
				$tabCondition			=	$this->getTabConditional( (int) $field->get( 'tabid' ), $reason, $user->get( 'id' ) );
				$display				=	true;

				if ( $tabCondition && in_array( (int) $field->get( 'tabid' ), $tabCondition ) ) {
					$display			=	false;
				}

				if ( $display ) {
					$condition			=	$this->getFieldConditional( null, $reason, $user->get( 'id' ) );

					if ( $condition->hide ) {
						if ( in_array( (int) $field->get( 'fieldid' ), $condition->hide ) ) {
							$display	=	false;
						}
					}
				}

				if ( ! $display ) {
					$return				=	' ';
				}
			} elseif ( $output == 'htmledit' ) {
				$this->getFieldConditional( $field, $reason, $user->id, true );
			}

			$field->set( '_noCondition', false );
		}

		return $return;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $control_name
	 * @return array
	 */
	public function loadFields( $name, $value, $control_name )
	{
 		global $_CB_database;

		$values			=	array();

		$query			=	"SELECT CONCAT_WS( ',', f." . $_CB_database->NameQuote( 'fieldid' ) . ", f." . $_CB_database->NameQuote( 'name' ) . " ) AS value"
						.	", f." . $_CB_database->NameQuote( 'title' ) . " AS text"
						.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_fields' ) . " AS f"
						.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler_tabs' ) . " AS t"
						.	" ON t." . $_CB_database->NameQuote( 'tabid' ) . " = f." . $_CB_database->NameQuote( 'tabid' )
						.	"\n WHERE f." . $_CB_database->NameQuote( 'published' ) . " = 1"
						.	"\n AND f." . $_CB_database->NameQuote( 'name' ) . " != " . $_CB_database->Quote( 'NA' )
						.	"\n ORDER BY t." . $_CB_database->NameQuote( 'position' ) . ", t." . $_CB_database->NameQuote( 'ordering' ) . ", f." . $_CB_database->NameQuote( 'ordering' );
		$_CB_database->setQuery( $query );
		$fields			=	$_CB_database->loadObjectList();

		foreach ( $fields as $field ) {
			$values[]	=	moscomprofilerHTML::makeOption( $field->value, CBTxt::T( $field->text ) );
		}

		return $values;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $control_name
	 * @return array
	 */
	public function loadFieldOptions( $name, $value, $control_name )
	{
 		global $_CB_database;

		$fields				=	array();
		$values				=	array();

		$query				=	'SELECT o.' . $_CB_database->NameQuote( 'fieldvalueid' ) . ' AS value'
							.	', IF( o.' . $_CB_database->NameQuote( 'fieldlabel' ) . ', o.' . $_CB_database->NameQuote( 'fieldlabel' ) . ', o.' . $_CB_database->NameQuote( 'fieldtitle' ) . ' ) AS text'
							.	', o.' . $_CB_database->NameQuote( 'fieldid' )
							.	', f.' . $_CB_database->NameQuote( 'title' ) . ' AS fieldtitle'
							.	', f.' . $_CB_database->NameQuote( 'name' ) . ' AS fieldname'
							. 	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_field_values' ) . " AS o"
							.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler_fields' ) . " AS f"
							.	' ON f.' . $_CB_database->NameQuote( 'fieldid' ) . ' = o.' . $_CB_database->NameQuote( 'fieldid' )
							.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler_tabs' ) . " AS t"
							.	' ON t.' . $_CB_database->NameQuote( 'tabid' ) . ' = f.' . $_CB_database->NameQuote( 'tabid' )
							.	"\n WHERE f." . $_CB_database->NameQuote( 'published' ) . " = 1"
							.	"\n AND f." . $_CB_database->NameQuote( 'name' ) . " != " . $_CB_database->Quote( 'NA' )
							.	"\n ORDER BY t." . $_CB_database->NameQuote( 'position' ) . ", t." . $_CB_database->NameQuote( 'ordering' ) . ", f." . $_CB_database->NameQuote( 'ordering' ) . ", f." . $_CB_database->NameQuote( 'title' ) . ", o." . $_CB_database->NameQuote( 'ordering' );
		$_CB_database->setQuery( $query );
		$options			=	$_CB_database->loadObjectList();

		if ( $options ) foreach( $options as $option ) {
			if ( ! in_array( $option->fieldid, $fields ) ) {
				$values[]	=	moscomprofilerHTML::makeOptGroup( CBTxt::T( $option->fieldtitle ) );
				$fields[]	=	$option->fieldid;
			}

			$values[]		=	moscomprofilerHTML::makeOption( $option->value, CBTxt::T( $option->text ) );
		}

		return $values;
	}
}