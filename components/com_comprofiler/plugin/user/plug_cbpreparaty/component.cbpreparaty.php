<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C)2005-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CBLib\Application\Application;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class CBplug_cbpreparaty extends cbPluginHandler
{

	/**
	 * @param null      $tab
	 * @param UserTable $user
	 * @param int       $ui
	 * @param array     $postdata
	 */
	public function getCBpluginComponent( $tab, $user, $ui, $postdata )
	{
		global $_CB_framework;

		outputCbJs( 1 );
		outputCbTemplate( 1 );

		$action					=	$this->input( 'action', null, GetterInterface::STRING );
		$function				=	$this->input( 'func', null, GetterInterface::STRING );
		$id						=	$this->input( 'id', null, GetterInterface::INT );
		$user					=	CBuser::getMyUserDataInstance();

		$tab					=	new TabTable();

		$tab->load( array( 'pluginclass' => 'cbinvitesTab' ) );

		$profileUrl				=	$_CB_framework->userProfileUrl( $user->get( 'id' ), false, 'cbinvitesTab' );

		if ( ! ( $tab->enabled && Application::MyUser()->canViewAccessLevel( $tab->viewaccesslevel ) ) ) {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		ob_start();
		switch ( $action ) {
			case 'preparaty':
				switch ( $function ) {
					
					case 'delete':
						$this->deletePreparaty( $id, $user );
						break;

				}
				break;
			default:
				cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
				break;
		}
		$html					=	ob_get_contents();
		ob_end_clean();

		$class					=	$this->params->get( 'general_class', null );

		$return					=	'<div id="cbInvites" class="cbInvites' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . '">'
								.		'<div id="cbInvitesInner" class="cbInvitesInner">'
								.			$html
								.		'</div>'
								.	'</div>';

		echo $return;
	}

	

	/**
	 * @param int       $id
	 * @param UserTable $user
	 */
	private function deletePreparaty( $id, $user )
	{
		global $_CB_framework;
                $db = JFactory::getDBO();
                require_once ( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_flexicontent/classes/flexicontent.helper.php' );
                //require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'flexicontent.helper.php');
                $state = -2;
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$dispatcher = JDispatcher::getInstance();
		JRequest::setVar("isflexicontent", "yes");
		static $event_failed_notice_added = false;
		
                $query = 'SELECT id FROM #__content'
				. ' WHERE id = '.(int)$id.
                        " AND created_by = " . (int) $user->get( 'id' );
                $db->setQuery( $query );
			
                if(!$db->loadResult()){
                    $profileUrl				=	$_CB_framework->userProfileUrl( $user->get( 'id' ) , false, '' );

                    cbRedirect( $profileUrl, CBTxt::T( 'You can\'t deleted this drug!' ) );
                }
                
		if ( $id )
		{
			$v = FLEXIUtilities::getCurrentVersions((int)$id);
			
			$query = 'UPDATE #__content'
				. ' SET state = ' . (int)$state
				. ' WHERE id = '.(int)$id
				//. ' AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id'). ' ) )'
			;
			$db->setQuery( $query );
			$db->query();
			if ( $db->getErrorNum() )  if (FLEXI_J16GE) throw new Exception($db->getErrorMsg(), 500); else JError::raiseError(500, $db->getErrorMsg());
			
			$query = 'UPDATE #__flexicontent_items_tmp'
				. ' SET state = ' . (int)$state
				. ' WHERE id = '.(int)$id
				//. ' AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id'). ' ) )'
			;
			$db->setQuery( $query );
			$db->query();
			if ( $db->getErrorNum() )  if (FLEXI_J16GE) throw new Exception($db->getErrorMsg(), 500); else JError::raiseError(500, $db->getErrorMsg());
			
			$query = 'UPDATE #__flexicontent_items_versions'
				. ' SET value = ' . (int)$state
				. ' WHERE item_id = '.(int)$id
				. ' AND valueorder = 1'
				. ' AND field_id = 10'
				. ' AND version = ' .(int)$v['version']
				;
			$db->setQuery( $query );
			$db->query();
			if ( $db->getErrorNum() )  if (FLEXI_J16GE) throw new Exception($db->getErrorMsg(), 500); else JError::raiseError(500, $db->getErrorMsg());
		}
		
		
		// ****************************************************************
		// Trigger Event 'onContentChangeState' of Joomla's Content plugins
		// ****************************************************************
		if (FLEXI_J16GE) {
			// Make sure we import flexicontent AND content plugins since we will be triggering their events
			JPluginHelper::importPlugin('content');
			
			// PREPARE FOR TRIGGERING content events
			// We need to fake joomla's states ... when triggering events
			$fc_state = $state;
			if ( in_array($fc_state, array(1,-5)) ) $jm_state = 1;           // published states
			else if ( in_array($fc_state, array(0,-3,-4)) ) $jm_state = 0;   // unpublished states
			else $jm_state = $fc_state;                                      // trashed & archive states
			$fc_itemview = $app->isSite() ? FLEXI_ITEMVIEW : 'item';
			
			$item = new stdClass();
			
			// Compatibility steps (including Joomla compatible state),
			// so that 3rd party plugins using the change state event work properly
		  JRequest::setVar('view', 'article');	  JRequest::setVar('option', 'com_content');
			$item->state = $jm_state;
			
			$result = $dispatcher->trigger($this->event_change_state, array('com_content.article', (array) $id, $jm_state));
			
			// Revert compatibilty steps ... the $item->state is not used further regardless if it was changed,
			// besides the event_change_state using plugin should have updated DB state value anyway
			JRequest::setVar('view', $fc_itemview);	  JRequest::setVar('option', 'com_flexicontent');
			if ($item->state == $jm_state) $item->state = $fc_state;  // this check is redundant, item->state is not used further ...
			
			if (in_array(false, $result, true) && !$event_failed_notice_added) {
				JError::raiseNotice(10, JText::_('One of plugin event handler for onContentChangeState failed') );
				$event_failed_notice_added = true;
				return false;
			}
		}
		$profileUrl				=	$_CB_framework->userProfileUrl( $user->get( 'id' ) , false, '' );

		cbRedirect( $profileUrl, CBTxt::T( 'Drug deleted successfully!' ) );
	}
}
?>