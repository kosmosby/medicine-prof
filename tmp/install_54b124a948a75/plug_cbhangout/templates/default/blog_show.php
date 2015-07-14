<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Database\Table\OrderedTable;
use CBLib\Language\CBTxt;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class HTML_cbhangoutBlog
 * Template for CB Blogs Show view
 */
class HTML_cbhangoutBlog
{
	/**
	 * @param  OrderedTable  $row
	 * @param  UserTable     $user
	 * @param  stdClass      $model
	 * @param  PluginTable   $plugin
	 */
	static function showBlog( $row, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $plugin, $usrs)
	{
		global $_CB_framework, $_LANG;

		$_CB_framework->setPageTitle( $row->get( 'title' ) );
		$_CB_framework->appendPathWay( htmlspecialchars( $_LANG['Hangout'] ), $_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), true, 'cbhangoutTab' ) );
		$_CB_framework->appendPathWay( htmlspecialchars( $row->get( 'title' ) ), $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'hangout', 'func' => 'show', 'id' => (int) $row->get( 'id' ) ) ) );

		$cbUser			=&	CBuser::getInstance( (int) $row->get( 'user' ), false );
                $join_button = '';
                if($row->get( 'user' ) == $user->get( 'id' )){
                    
                    $emails_str = '';
                    $subscrStr = '';
                    if($usrs){
                        $subscrStr .= '<div>' . $_LANG['Subscribers'] . ':<br />';
                        $usrCount = 0;
                        foreach ($usrs as $value) {
                            $cbUserObj			=&	CBuser::getInstance( (int) $value, false );
                            
                            $email = CBuser::getUserDataInstance($value)->get('email');
                            if($email){
                                
                                if($usrCount > 0){
                                    $emails_str .= ',';
                                    $subscrStr .= ', ';
                                }
                                $subscrStr .= $cbUserObj->getField( 'formatname', null, 'html', 'none', 'list', 0, true );
                                $subscrStr .= ' ('.$email.')';
                                $emails_str .= '{ id : \''.$email.'\', invite_type : \'EMAIL\' }';
                                $usrCount ++;
                            }
                            
                        }
                        $subscrStr .= '</div>';
                    }
                    
                    $curlang = $_CB_framework->getCfg( 'lang' ) == 'russian' ? 'ru' : 'en';
                    
                    $join_button = '<script >'
                                .'window.___gcfg = {'
                                    .'lang: \''.$curlang.'\','
                                    .'parsetags: \'onload\''
                                .'};'
                            .'</script>'
                            .'<script src="https://apis.google.com/js/platform.js" async defer></script>'
                            //.'<style>.begin_conf{cursor:pointer; border:1px solid #333; display:inline-block; }</style>'
                            .'<span class="begin_conf"><g:hangout render="createhangout" hangout_type="onair" topic="'.$row->get( 'title' ).'"
                                            invites="['.$emails_str.']">
                                 </g:hangout></span>'.$subscrStr;
                    
                    
                    
                }elseif($user->get( 'id' )){
                    if($row->price > 0){
                        $link = '#';
                        $link_text = $_LANG['Pay']." ".$row->price;
                    }else{
                        $link = cbSef( 'index.php?option=com_comprofiler&view=pluginclass&plugin=cbhangout&action=hangout&func=joinconf&id=' . (int) $row->id, true );
                        $link_text = $_LANG['Join'];
                    }
                    
                    if(count($usrs) && in_array($user->get( 'id' ), $usrs)){
                        $join_button = $_LANG['You allready joined conference'];
                    }else{
                        $join_button = '<a href="'.$link.'" /><input type="button" value="'.$link_text.'" class="btn btn-primary"/></a>';

                    }
                    
                }else{
                    $reg_link = $_CB_framework->viewUrl( 'registers', true, null, 'html', 0 );
                    $join_button = $_LANG['Need to register']
                            .'- <a href="'.$reg_link.'">'
                            .$_LANG['Register']
                            .'</a>';
                }
                
                
		$return			=	'<div class="blowShow">'
						.		'<div class="blogsTitle page-header"><h3>' . $row->get( 'title' ) . ' <small>' . CBTxt::T( 'WRITTEN_BY_BLOG_AUTHOR', 'Written by [blog_author]', array( '[blog_author]' => $cbUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) ) ) . '</small></h3></div>'
						.		'<div class="blogsHeader well well-sm">'
						.			CBTxt::T( 'CATEGORY_CATEGORY', 'Category: [category]', array( '[category]' => $row->get( 'category' ) ) )
						.			' &nbsp;/&nbsp; ' . CBTxt::T( 'CREATED_CREATED', 'Created: [created]', array( '[created]' => cbFormatDate( $row->get( 'created' ) ) ) )
						.			( $row->get( 'modified' ) && ( $row->get( 'modified' ) != '0000-00-00 00:00:00' ) ? ' &nbsp;/&nbsp; ' . CBTxt::T( 'MODIFIED_MODIFIED', 'Modified: [modified]', array( '[modified]' => cbFormatDate( $row->get( 'modified' ) ) ) ) : null )
                                                .                       ' &nbsp;/&nbsp; ' . $_LANG['Price'] . ' : ' .$row->get( 'price' )   
						.		'</div>'
						.		'<div class="blogsText">' . $row->get( 'hangout_intro' ) . $row->get( 'hangout_full' ) . '</div>'
						.	'</div>'
                                                .   '<div>'
                                                .       $join_button
                                                .   '</div>';

		echo $return;
	}
}
