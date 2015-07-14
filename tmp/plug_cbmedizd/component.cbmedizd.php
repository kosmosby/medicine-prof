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

class CBplug_cbmedizd extends cbPluginHandler
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

		$tab->load( array( 'pluginclass' => 'cbmedizdTab' ) );

		$profileUrl				=	$_CB_framework->userProfileUrl( $user->get( 'id' ), false, 'cbmedizdTab' );

		if ( ! ( $tab->enabled && Application::MyUser()->canViewAccessLevel( $tab->viewaccesslevel ) ) ) {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		ob_start();
		switch ( $action ) {
			case 'medizd':
				switch ( $function ) {
					case 'new':
						$this->showMedizdEdit( null, $user );
						break;
					case 'edit':
						$this->showMedizdEdit( $id, $user );
						break;
					case 'save':
						cbSpoofCheck( 'plugin' );
						$this->saveMedizdEdit( $id, $user );
						break;

					case 'delete':
						$this->deleteMedizd( $id, $user );
						break;
					case 'show':
					default:
						cbRedirect( $profileUrl );
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

		$return					=	'<div id="cbMedizd" class="cbMedizd' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . '">'
								.		'<div id="cbMedizdInner" class="cbMedizdInner">'
								.			$html
								.		'</div>'
								.	'</div>';

		echo $return;
	}

	/**
	 * @param null|int    $id
	 * @param UserTable   $user
	 * @param null|string $message
	 * @param null|string $messageType
	 */
	public function showMedizdEdit( $id, $user, $message = null, $messageType = 'error' )
	{
		global $_CB_framework, $_CB_database,$_PLUGINS;
                $absPath							=	$_PLUGINS->getPluginPath( $plugin );
                require $absPath . '/templates/default/medizd_edit.php';
		$inviteLimit						=	(int) $this->params->get( 'invite_limit', null );
		$cbModerator						=	Application::User( (int) $user->get( 'id' ) )->isGlobalModerator();
                
		$row								=	new cbmedizdProductTable();

		$row->load( (int) $id );

		$canAccess							=	false;

		if ( ! $row->get( 'id' ) ) {
			if ( $cbModerator ) {
				$canAccess					=	true;
			} elseif ( $user->get( 'id' ) && Application::MyUser()->canViewAccessLevel( $this->params->get( 'invite_create_access', 2 ) ) ) {
				if ( $inviteLimit ) {
					$query					=	'SELECT COUNT(*)'
											.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin_cbmedizd' )
											.	"\n WHERE " . $_CB_database->NameQuote( 'user_id' ) . " = " . (int) $user->get( 'id' )
											.	"\n AND ( " . $_CB_database->NameQuote( 'user' ) . " IS NULL OR " . $_CB_database->NameQuote( 'user' ) . " = " . $_CB_database->Quote( '' ) . " )";
					$_CB_database->setQuery( $query );
					$inviteCount			=	(int) $_CB_database->loadResult();

					if ( $inviteCount < $inviteLimit ) {
						$canAccess			=	true;
					}
				} else {
					$canAccess				=	true;
				}
			}
		} elseif ( $cbModerator || ( $row->get( 'user_id' ) == $user->get( 'id' ) ) ) {
			$canAccess						=	true;
		}

		$profileUrl							=	$_CB_framework->userProfileUrl( $row->get( 'user_id', $user->get( 'id' ) ), false, 'cbmedizdTab' );

		if ( $canAccess) {
			$inviteEditor					=	$this->params->get( 'invite_editor', 2 );

			cbinvitesClass::getTemplate( 'medizd_edit' );

			$input							=	array();

			$toTooltip						=	cbTooltip( null, CBTxt::T( 'MEDPR_INPUT_CODE' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['code']					=	'<input type="text" id="code" name="code" value="' . htmlspecialchars( $this->input( 'post/code', $row->get( 'code' ), GetterInterface::INT ) ) . '" class="required digits form-control" size="35"' . ( $toTooltip ? ' ' . $toTooltip : null ) . ' />';

			$subjectTooltip					=	cbTooltip( null, CBTxt::T( 'MEDPR_INPUT_NAME' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['name']				=	'<input type="text" id="name" name="name" value="' . htmlspecialchars( $this->input( 'post/name', $row->get( 'name' ), GetterInterface::STRING ) ) . '" class="required form-control" size="35"' . ( $subjectTooltip ? ' ' . $subjectTooltip : null ) . ' />';

			
			$body						=	$this->input( 'post/description', $row->get( 'description' ), GetterInterface::STRING );
			

			
                        $bodyTooltip				=	cbTooltip( null, CBTxt::T( 'MEDPR_INPUT_DESCR' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

                        $input['description']				=	'<textarea id="description" name="description" class="form-control" cols="35" rows="4"' . ( $bodyTooltip ? ' ' . $bodyTooltip : null ) . '>' . htmlspecialchars( $row->get( 'description') ) . '</textarea>';
			
                        //$subjectTooltip					=	cbTooltip( null, CBTxt::T( 'Select category.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['category']				=	$this->getMedizdCategories($row->get( 'category' ));//'<input type="text" id="category" name="category" value="' . htmlspecialchars( $this->input( 'post/category', $row->get( 'category' ), GetterInterface::STRING ) ) . '" class="form-control" size="35"' . ( $subjectTooltip ? ' ' . $subjectTooltip : null ) . ' />';
                        
                        $subjectTooltip					=	cbTooltip( null, CBTxt::T( 'MEDPR_INPUT_MANUFACTIRE' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['proizvoditel']				=	'<input type="text" id="proizvoditel" name="proizvoditel" value="' . htmlspecialchars( $this->input( 'post/proizvoditel', $row->get( 'proizvoditel' ), GetterInterface::STRING ) ) . '" class="form-control" size="35"' . ( $subjectTooltip ? ' ' . $subjectTooltip : null ) . ' />';
                        
                        
                        $db = JFactory::getDBO();
                        $db->setQuery("SELECT country_name as name, country_name as id FROM #__comprofiler_countries ORDER BY country_name");
                        $countries = $db->loadObjectList();
                        
                        
			$input['country']				=	$text_field = JHTML::_('select.genericlist',   $countries, 'country', ' class="form-control" size="1" style="width:360px;"', 'id', 'name', htmlspecialchars( $this->input( 'post/country', $row->get( 'country' ), GetterInterface::STRING ) ) );
	 
                        $subjectTooltip					=	cbTooltip( null, CBTxt::T( 'MEDPR_INPUT_PRICE' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['price']				=	'<input type="text" id="price" name="price" value="' . htmlspecialchars( $this->input( 'post/price', $row->get( 'price' ), GetterInterface::STRING ) ) . '" class="form-control" size="35"' . ( $subjectTooltip ? ' ' . $subjectTooltip : null ) . ' />';

			
			if ( $message ) {
				$_CB_framework->enqueueMessage( $message, $messageType );
			}

			HTML_cbmedizdProductEdit::showProductEdit( $row, $input, $user, $this );
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}
        
        private function getMedizdCategories($value){
            $db = JFactory::getDBO();
            $db->setQuery("SELECT * FROM #__comprofiler_plugin_cbmedizd_categories WHERE parent_id = 0 ORDER BY name");
            $cats = $db->loadObjectList();
            
            $html = '<select name="category" id="category" class="form-control" size="1" style="width:360px;">';
            
            for($intA = 0; $intA < count($cats); $intA ++){
                $html .= '<optgroup label="'.$cats[$intA]->name.'">';
                $db->setQuery("SELECT * FROM #__comprofiler_plugin_cbmedizd_categories WHERE parent_id = {$cats[$intA]->id} ORDER BY name");
                $inner_cats = $db->loadObjectList();
                for($intB = 0; $intB < count($inner_cats); $intB ++){
                    $html .= '<option value="'.$inner_cats[$intB]->id.'" '.($inner_cats[$intB]->id == $value ? ' selected' : '').'>'.$inner_cats[$intB]->name.'</option>';
                }
                $html .= '</optgroup>';
            }
            
            $html .= '</select>';
            
            return $html;
        }
        
	/**
	 * @param null|int  $id
	 * @param UserTable $user
	 */
	private function saveMedizdEdit( $id, $user )
	{
		global $_CB_framework, $_CB_database, $_PLUGINS;

		$cbModerator						=	Application::User( (int) $user->get( 'id' ) )->isGlobalModerator();
                $post							=	$this->getInput()->getNamespaceRegistry( 'post' );
		$row								=	new cbmedizdProductTable();
                $user_groups_can_create = array(12,16); //proizvoditeli, komercheskie organizacii
                $gids = $user->get('gids');
		$row->load( (int) $id );

		$canAccess							=	false;
		$inviteCount						=	0;

		if ( ! $row->get( 'id' ) ) {
			if ( $cbModerator ) {
				$canAccess					=	true;
			} elseif ( $user->get( 'id' )  ) {
				
				$canAccess				=	true;
				
			}
		} elseif ( $cbModerator || ( $row->get( 'user_id' ) == $user->get( 'id' ) ) ) {
			$canAccess						=	true;
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
                    $canAccess = false;
                }

		$profileUrl							=	$_CB_framework->userProfileUrl( $row->get( 'user_id', $user->get( 'id' ) ), false, 'cbmedizdTab' );

		if ( $canAccess ) {
			
			

                    $new					=	( $row->get( 'id' ) ? false : true );


                    if ( ! $row->bind( $_POST ) ) {
                        return;
                    }
                    
                    $row->user_id = $user->get( 'id' );
                    $row->created = date("Y-m-d H:i:s");

                    if ( $row->getError() || ( ! $row->store() ) ) {
                            $this->showMedizdEdit( $row->get( 'id' ), $user, CBTxt::T( 'ERROR', 'Failed to save! Error: [error]', array( '[error]' => $row->getError() ) ) ); return;
                    }

					

					
				

                    cbRedirect( $profileUrl, CBTxt::T( 'MEDPR_SAVEPR_SUCC' )  );
			
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}

	
	/**
	 * @param int       $id
	 * @param UserTable $user
	 */
	private function deleteMedizd( $id, $user )
	{
		global $_CB_framework;

		$cbModerator			=	Application::User( (int) $user->get( 'id' ) )->isGlobalModerator();

		$row					=	new cbmedizdProductTable();

		$row->load( (int) $id );

		$canAccess				=	false;

		if ( $row->get( 'id' ) && ( $cbModerator || ( $row->get( 'user_id' ) == $user->get( 'id' ) ) ) ) {
			$canAccess			=	true;
		}

		$profileUrl				=	$_CB_framework->userProfileUrl( $row->get( 'user_id', $user->get( 'id' ) ), false, 'cbmedizdTab' );

		if ( $canAccess ) {
			
			if ( ! $row->delete() ) {
				cbRedirect( $profileUrl, CBTxt::T( 'FAILED_DELETE_ERROR', 'Failed to delete! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

			cbRedirect( $profileUrl, CBTxt::T( 'MEDPR_PROD_DELSUCC' ) );
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}
}
?>