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

class cbautoactionsActionUsergroup extends cbPluginHandler
{

	/**
	 * @param cbautoactionsActionTable $trigger
	 * @param UserTable $user
	 */
	public function execute( $trigger, $user )
	{
		if ( ! $user->get( 'id' ) ) {
			if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
				var_dump( CBTxt::T( 'AUTO_ACTION_USERGROUP_NO_USER', ':: Action [action] :: Usergroup skipped due to no user', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
			}

			return;
		}

		$cache										=	$user->get( 'password' );

		$user->set( 'password', null );

		foreach ( $trigger->getParams()->subTree( 'usergroup' ) as $row ) {
			/** @var ParamsInterface $row */
			$groups									=	$row->get( 'groups', null, GetterInterface::STRING );

			if ( $groups ) {
				$groups								=	explode( '|*|', $groups );

				cbArrayToInts( $groups );
			}

			$session								=	JFactory::getSession();
			$jUser									=	$session->get( 'user' );
			$isMe									=	( $jUser ? ( $jUser->id == $user->get( 'id' ) ) : false );

			switch ( $row->get( 'mode', 'add', GetterInterface::STRING ) ) {
				case 'create':
					$title							=	$trigger->getSubstituteString( $row->get( 'title', null, GetterInterface::STRING ) );

					if ( ! $title ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_USERGROUP_NO_TITLE', ':: Action [action] :: Usergroup skipped due to missing title', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
						}

						continue;
					}

					$usergroup						=	JTable::getInstance( 'usergroup' );

					$usergroup->load( array( 'title' => $title ) );

					if ( ! $usergroup->id ) {
						$usergroup->parent_id		=	(int) $row->get( 'parent', 0, GetterInterface::INT );
						$usergroup->title			=	$title;

						if ( ! $usergroup->store() ) {
							if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
								var_dump( CBTxt::T( 'AUTO_ACTION_USERGROUP_CREATE_FAILED', ':: Action [action] :: Usergroup failed to create', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
							}

							continue;
						}
					}

					if ( $row->get( 'add', 1, GetterInterface::BOOLEAN ) ) {
						if ( ! in_array( $usergroup->id, $user->get( 'gids' ) ) ) {
							$user->gids[]			=	$usergroup->id;

							if ( ! $user->store() ) {
								if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
									var_dump( CBTxt::T( 'AUTO_ACTION_USERGROUP_FAILED', ':: Action [action] :: Usergroup failed to save. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $user->getError() ) ) );
								}

								continue;
							}

							if ( $isMe ) {
								JAccess::clearStatics();

								$session->set( 'user', new JUser( $user->get( 'id'  ) ) );
							}
						}
					}
					break;
				case 'replace':
					if ( ! $groups ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_USERGROUP_NO_GROUPS', ':: Action [action] :: Usergroup skipped due to missing groups', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
						}

						continue;
					}

					$user->set( 'gids', $groups );

					if ( ! $user->store() ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_USERGROUP_FAILED', ':: Action [action] :: Usergroup failed to save. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $user->getError() ) ) );
						}

						continue;
					}

					if ( $isMe ) {
						JAccess::clearStatics();

						$session->set( 'user', new JUser( $user->get( 'id' ) ) );
					}
					break;
				case 'remove':
					if ( ! $groups ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_USERGROUP_NO_GROUPS', ':: Action [action] :: Usergroup skipped due to missing groups', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
						}

						continue;
					}

					$removed						=	false;

					foreach( $user->get( 'gids' ) as $k => $gid ) {
						if ( in_array( $gid, $groups ) ) {
							unset( $user->gids[$k] );

							$removed				=	true;
						}
					}

					if ( $removed ) {
						if ( ! $user->store() ) {
							if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
								var_dump( CBTxt::T( 'AUTO_ACTION_USERGROUP_FAILED', ':: Action [action] :: Usergroup failed to save. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $user->getError() ) ) );
							}

							continue;
						}

						if ( $isMe ) {
							JAccess::clearStatics();

							$session->set( 'user', new JUser( $user->get( 'id'  ) ) );
						}
					}
					break;
				case 'add':
				default:
					if ( ! $groups ) {
						if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
							var_dump( CBTxt::T( 'AUTO_ACTION_USERGROUP_NO_GROUPS', ':: Action [action] :: Usergroup skipped due to missing groups', array( '[action]' => (int) $trigger->get( 'id' ) ) ) );
						}

						continue;
					}

					$usergroups						=	$groups;

					foreach( $usergroups as $k => $usergroup ) {
						if ( in_array( $usergroup, $user->get( 'gids' ) ) ) {
							unset( $usergroups[$k] );
						}
					}

					if ( $usergroups ) {
						$user->set( 'gids', array_unique( array_merge( $user->get( 'gids' ), $usergroups ) ) );

						if ( ! $user->store() ) {
							if ( $trigger->getParams()->get( 'debug', false, GetterInterface::BOOLEAN ) ) {
								var_dump( CBTxt::T( 'AUTO_ACTION_USERGROUP_FAILED', ':: Action [action] :: Usergroup failed to save. Error: [error]', array( '[action]' => (int) $trigger->get( 'id' ), '[error]' => $user->getError() ) ) );
							}

							continue;
						}

						if ( $isMe ) {
							JAccess::clearStatics();

							$session->set( 'user', new JUser( $user->get( 'id'  ) ) );
						}
					}
					break;
			}
		}

		$user->set( 'password', $cache );
	}
}