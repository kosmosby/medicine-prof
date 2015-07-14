<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\GetterInterface;
use CBLib\Language\CBTxt;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class cbautoactionsActionGroupJive extends cbPluginHandler
{

	/**
	 * @param cbautoactionsActionTable $trigger
	 * @param UserTable $user
	 */
	public function execute( $trigger, $user )
	{
		global $_CB_framework, $_CB_database, $_PLUGINS;

		if ( ! $this->installed() ) {
			if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
				var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_NOT_INSTALLED', ':: Action [action] :: CB GroupJive is not installed', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
			}

			return;
		}

		$gjPlugin										=	$_PLUGINS->getLoadedPlugin( 'user', 'cbgroupjive' );
		$gjParams										=	$_PLUGINS->getPluginParams( $gjPlugin );

		foreach ( $trigger->getParams()->subTree( 'groupjive' ) as $row ) {
			/** @var ParamsInterface $row */
			switch( (int) $row->get( 'mode', 1, GetterInterface::INT ) ) {
				case 3:
					$owner								=	$row->get( 'owner', null, GetterInterface::STRING );

					if ( ! $owner ) {
						$owner							=	(int) $user->get( 'id' );
					} else {
						$owner							=	(int) $trigger->getSubstituteString( $owner );
					}

					if ( ! $owner ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_NO_OWNER', ':: Action [action] :: CB GroupJive skipped due to missing owner', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
						}

						continue;
					}

					$name								=	$trigger->getSubstituteString( $row->get( 'name', null, GetterInterface::STRING ) );

					if ( ! $name ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_NO_NAME', ':: Action [action] :: CB GroupJive skipped due to missing name', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
						}

						continue;
					}

					$parent								=	(int) $row->get( 'parent', 0, GetterInterface::INT );

					$category							=	new cbgjCategory( $_CB_database );

					if ( $row->get( 'unique', 1, GetterInterface::BOOLEAN ) ) {
						$category->load( array( 'user_id' => (int) $owner, 'name' => $name, 'parent' => (int) $parent ) );
					} else {
						$category->load( array( 'name' => $name, 'parent' => (int) $parent ) );
					}

					if ( ! $category->get( 'id' ) ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_NO_CATEGORY', ':: Action [action] :: CB GroupJive skipped due to missing category', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
						}

						continue;
					}

					$categoryEditor						=	$gjParams->get( 'category_editor', 1 );

					$category->set( 'published', 1 );
					$category->set( 'parent', (int) $parent );
					$category->set( 'user_id', $owner );
					$category->set( 'name', $name );

					if ( ( $categoryEditor == 2 ) || ( $categoryEditor == 3 ) ) {
						$category->set( 'description', $trigger->getSubstituteString( $row->get( 'description', null, GetterInterface::RAW ), false ) );
					} else {
						$category->set( 'description', $trigger->getSubstituteString( $row->get( 'description', null, GetterInterface::STRING ) ) );
					}

					$category->set( 'access', (int) $gjParams->get( 'category_access_default', -2 ) );
					$category->set( 'types', $row->get( 'types', $gjParams->get( 'category_types_default', '1|*|2|*|3' ), GetterInterface::STRING ) );
					$category->set( 'create', (int) $gjParams->get( 'category_create_default', 1 ) );
					$category->set( 'create_access', (int) $gjParams->get( 'category_createaccess_default', -1 ) );
					$category->set( 'nested', (int) $gjParams->get( 'category_nested_default', 1 ) );
					$category->set( 'nested_access', (int) $gjParams->get( 'category_nestedaccess_default', -1 ) );
					$category->set( 'date', $_CB_framework->getUTCDate() );
					$category->set( 'ordering', 99999 );

					if ( ! $category->store() ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_FAILED', ':: Action [action] :: CB GroupJive failed to save. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $category->getError() ) ) );

							continue;
						}
					}
					break;
				case 2:
					$owner								=	$row->get( 'owner', null, GetterInterface::STRING );

					if ( ! $owner ) {
						$owner							=	(int) $user->get( 'id' );
					} else {
						$owner							=	(int) $trigger->getSubstituteString( $owner );
					}

					if ( ! $owner ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_NO_OWNER', ':: Action [action] :: CB GroupJive skipped due to missing owner', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
						}

						continue;
					}

					$categoryId							=	(int) $row->get( 'category', -1, GetterInterface::INT );

					$category							=	new cbgjCategory( $_CB_database );

					if ( $categoryId == -1 ) {
						$name							=	$trigger->getSubstituteString( $row->get( 'category_name', null, GetterInterface::STRING ) );

						if ( ! $name ) {
							if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
								var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_NO_CAT_NAME', ':: Action [action] :: CB GroupJive skipped due to missing category name', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
							}

							continue;
						}

						$parent							=	(int) $row->get( 'category_parent', 0, GetterInterface::INT );

						if ( $row->get( 'category_unique', 1, GetterInterface::BOOLEAN ) ) {
							$category->load( array( 'user_id' => (int) $owner, 'name' => $name, 'parent' => (int) $parent ) );
						} else {
							$category->load( array( 'name' => $name, 'parent' => (int) $parent ) );
						}

						if ( ! $category->get( 'id' ) ) {
							$categoryEditor				=	$gjParams->get( 'category_editor', 1 );

							$category->set( 'published', 1 );
							$category->set( 'parent', (int) $parent );
							$category->set( 'user_id', $owner );
							$category->set( 'name', $name );

							if ( ( $categoryEditor == 2 ) || ( $categoryEditor == 3 ) ) {
								$category->set( 'description', $trigger->getSubstituteString( $row->get( 'category_description', null, GetterInterface::RAW ), false ) );
							} else {
								$category->set( 'description', $trigger->getSubstituteString( $row->get( 'category_description', null, GetterInterface::STRING ) ) );
							}

							$category->set( 'access', (int) $gjParams->get( 'category_access_default', -2 ) );
							$category->set( 'types', $row->get( 'category_types', $gjParams->get( 'category_types_default', '1|*|2|*|3' ), GetterInterface::STRING ) );
							$category->set( 'create', (int) $gjParams->get( 'category_create_default', 1 ) );
							$category->set( 'create_access', (int) $gjParams->get( 'category_createaccess_default', -1 ) );
							$category->set( 'nested', (int) $gjParams->get( 'category_nested_default', 1 ) );
							$category->set( 'nested_access', (int) $gjParams->get( 'category_nestedaccess_default', -1 ) );
							$category->set( 'date', $_CB_framework->getUTCDate() );
							$category->set( 'ordering', 99999 );

							if ( ! $category->store() ) {
								if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
									var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_FAILED', ':: Action [action] :: CB GroupJive failed to save. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $category->getError() ) ) );
								}

								continue;
							}
						}
					} else {
						$category->load( (int) $categoryId );
					}

					if ( ! $category->get( 'id' ) ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_NO_CATEGORY', ':: Action [action] :: CB GroupJive skipped due to missing category', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
						}

						continue;
					}

					$name								=	$trigger->getSubstituteString( $row->get( 'name', null, GetterInterface::STRING ) );

					if ( ! $name ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_NO_NAME', ':: Action [action] :: CB GroupJive skipped due to missing name', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
						}

						continue;
					}

					$parent								=	(int) $row->get( 'group_parent', 0, GetterInterface::INT );
					$join								=	false;

					$group								=	new cbgjGroup( $_CB_database );

					if ( $row->get( 'unique', 1, GetterInterface::BOOLEAN ) ) {
						$group->load( array( 'category' => (int) $category->get( 'id' ), 'user_id' => (int) $owner, 'name' => $name, 'parent' => (int) $parent ) );
					} else {
						$group->load( array( 'category' => (int) $category->get( 'id' ), 'name' => $name, 'parent' => (int) $parent ) );

						if ( $row->get( 'autojoin', 1, GetterInterface::BOOLEAN ) ) {
							$join						=	true;
						}
					}

					if ( ! $group->get( 'id' ) ) {
						$groupEditor					=	$gjParams->get( 'group_editor', 1 );

						$group->set( 'published', 1 );
						$group->set( 'category', (int) $category->get( 'id' ) );
						$group->set( 'parent', (int) $parent );
						$group->set( 'user_id', $owner );
						$group->set( 'name', $name );

						if ( ( $groupEditor == 2 ) || ( $groupEditor == 3 ) ) {
							$group->set( 'description', $trigger->getSubstituteString( $row->get( 'description', null, GetterInterface::RAW ), false ) );
						} else {
							$group->set( 'description', $trigger->getSubstituteString( $row->get( 'description', null, GetterInterface::STRING ) ) );
						}

						$group->set( 'access', (int) $gjParams->get( 'group_access_default', -2 ) );
						$group->set( 'types', (int) $row->get( 'type', $gjParams->get( 'group_type_default', 1 ), GetterInterface::INT ) );
						$group->set( 'nested', (int) $gjParams->get( 'group_nested_default', 1 ) );
						$group->set( 'nested_access', (int) $gjParams->get( 'group_nestedaccess_default', -1 ) );
						$group->set( 'date', $_CB_framework->getUTCDate() );
						$group->set( 'ordering', 1 );

						if ( ! $group->store() ) {
							if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
								var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_FAILED', ':: Action [action] :: CB GroupJive failed to save. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $group->getError() ) ) );
							}

							continue;
						}

						$group->storeOwner( $group->get( 'user_id' ) );

						if ( $group->get( 'user_id' ) != $user->get( 'id' ) ) {
							$groupUser					=	new cbgjUser( $_CB_database );

							$groupUser->load( array( 'group' => (int) $group->get( 'id' ), 'user_id' => (int) $user->get( 'id' ) ) );

							if ( ! $groupUser->get( 'id' ) ) {
								$groupUser->set( 'user_id', (int) $user->get( 'id' ) );
								$groupUser->set( 'group', (int) $group->get( 'id' ) );
								$groupUser->set( 'date', $_CB_framework->getUTCDate() );
								$groupUser->set( 'status', 1 );

								if ( ! $groupUser->store() ) {
									if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
										var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_FAILED', ':: Action [action] :: CB GroupJive failed to save. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $groupUser->getError() ) ) );
									}

									continue;
								}
							}
						}
					} elseif ( $join ) {
						$groupUser					=	new cbgjUser( $_CB_database );

						$groupUser->load( array( 'group' => (int) $group->get( 'id' ), 'user_id' => (int) $user->get( 'id' ) ) );

						if ( ! $groupUser->get( 'id' ) ) {
							$groupUser->set( 'user_id', (int) $user->get( 'id' ) );
							$groupUser->set( 'group', (int) $group->get( 'id' ) );
							$groupUser->set( 'date', $_CB_framework->getUTCDate() );
							$groupUser->set( 'status', (int) $row->get( 'group_status', 1, GetterInterface::INT ) );

							if ( ! $groupUser->store() ) {
								if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
									var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_FAILED', ':: Action [action] :: CB GroupJive failed to save. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $groupUser->getError() ) ) );
								}

								continue;
							}

							if ( $groupUser->get( 'status' ) == 4 ) {
								$group->storeOwner( $groupUser->get( 'user_id' ) );
							}
						}
					}
					break;
				case 4:
					$groups							=	$row->get( 'groups', null, GetterInterface::STRING );

					if ( $groups ) {
						$groups						=	explode( '|*|', $groups );

						cbArrayToInts( $groups );

						foreach ( $groups as $groupId ) {
							$group					=	new cbgjGroup( $_CB_database );

							$group->load( (int) $groupId );

							if ( $group->get( 'id' ) ) {
								$groupUser			=	new cbgjUser( $_CB_database );

								$groupUser->load( array( 'group' => (int) $group->get( 'id' ), 'user_id' => (int) $user->get( 'id' ) ) );

								if ( $groupUser->get( 'id' ) && ( $groupUser->get( 'status' ) != 4 ) ) {
									$groupUser->deleteAll();
								}
							}
						}
					}
					break;
				case 1:
				default:
					$groups							=	$row->get( 'groups', null, GetterInterface::STRING );

					if ( $groups ) {
						$groups						=	explode( '|*|', $groups );

						cbArrayToInts( $groups );

						foreach ( $groups as $groupId ) {
							$group					=	new cbgjGroup( $_CB_database );

							$group->load( (int) $groupId );

							if ( $group->get( 'id' ) ) {
								$groupUser			=	new cbgjUser( $_CB_database );

								$groupUser->load( array( 'group' => (int) $group->get( 'id' ), 'user_id' => (int) $user->get( 'id' ) ) );

								if ( ! $groupUser->get( 'id' ) ) {
									$groupUser->set( 'user_id', (int) $user->get( 'id' ) );
									$groupUser->set( 'group', (int) $group->get( 'id' ) );
									$groupUser->set( 'date', $_CB_framework->getUTCDate() );
									$groupUser->set( 'status', (int) $row->get( 'status', 1, GetterInterface::INT ) );

									if ( ! $groupUser->store() ) {
										if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
											var_dump( CBTxt::T( 'AUTO_ACTION_GROUPJIVE_FAILED', ':: Action [action] :: CB GroupJive failed to save. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $groupUser->getError() ) ) );
										}

										continue;
									}

									if ( $groupUser->get( 'status' ) == 4 ) {
										$group->storeOwner( $groupUser->get( 'user_id' ) );
									}
								}
							}
						}
					}
					break;
			}
		}
	}

	/**
	 * @return array
	 */
	public function categories()
	{
		$options		=	array();

		if ( $this->installed() ) {
			$options	=	cbgjClass::getCategoryOptions( null );
		}

		return $options;
	}

	/**
	 * @return array
	 */
	public function groups()
	{
		$options		=	array();

		if ( $this->installed() ) {
			$options	=	cbgjClass::getGroupOptions( null );
		}

		return $options;
	}

	/**
	 * @return bool
	 */
	public function installed()
	{
		global $_PLUGINS;

		if ( $_PLUGINS->getLoadedPlugin( 'user', 'cbgroupjive' ) ) {
			return true;
		}

		return false;
	}
}