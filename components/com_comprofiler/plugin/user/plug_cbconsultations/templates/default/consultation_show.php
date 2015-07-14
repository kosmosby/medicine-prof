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
 * Class HTML_cbconsultationsconsultation
 * Template for CB consultations Show view
 */
class HTML_cbconsultationsconsultation
{
	/**
	 * @param  OrderedTable  $row
	 * @param  UserTable     $user
	 * @param  stdClass      $model
	 * @param  PluginTable   $plugin
	 */
	static function showconsultation( $row, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $plugin, $bids )
	{
		global $_CB_framework;

		$_CB_framework->setPageTitle( $row->get( 'title' ) );
		$_CB_framework->appendPathWay( htmlspecialchars( CBTxt::T( 'consultations' ) ), $_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), true, 'cbconsultationsTab' ) );
		$_CB_framework->appendPathWay( htmlspecialchars( $row->get( 'title' ) ), $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'consultations', 'func' => 'show', 'id' => (int) $row->get( 'id' ) ) ) );

		$cbUser			=&	CBuser::getInstance( (int) $row->get( 'user' ), false );

		$return			=	'<div class="blowShow">'
						.		'<div class="consultationsTitle page-header"><h3>' . $row->get( 'title' ) . ' <small>' . CBTxt::T( 'WRITTEN_BY_consultation_AUTHOR', 'Written by [consultation_author]', array( '[consultation_author]' => $cbUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) ) ) . '</small></h3></div>'
						.		'<div class="consultationsHeader well well-sm">'
						.			CBTxt::T( 'CATEGORY_CATEGORY', 'Category: [category]', array( '[category]' => $row->get( 'category' ) ) )
						.			' &nbsp;/&nbsp; ' . CBTxt::T( 'CREATED_CREATED', 'Created: [created]', array( '[created]' => cbFormatDate( $row->get( 'created' ) ) ) )
						.			( $row->get( 'modified' ) && ( $row->get( 'modified' ) != '0000-00-00 00:00:00' ) ? ' &nbsp;/&nbsp; ' . CBTxt::T( 'MODIFIED_MODIFIED', 'Modified: [modified]', array( '[modified]' => cbFormatDate( $row->get( 'modified' ) ) ) ) : null )
						.		'</div>'
						.		'<div class="consultationsText">' . $row->get( 'consultation_intro' ) . $row->get( 'consultation_full' ) . '</div>'
						.	'</div>';
    if($bids!=null){
      $return .= '<script >
          window.___gcfg = {
                  lang: \'ru\',
                          parsetags: \'onload\'
                              };
                              </script>  <script src="https://apis.google.com/js/platform.js" async defer></script> ';
      $return .= '<h2>Ставки</h2>';
      if(empty($bids)){
        $return .= "Нет ставок";
      }else{
        $return .= '<table cellspacing="5" class="consultationsContainer table table-hover table-responsive">';
        $return .= '<tr><th>Дата</th><th>Пользователь</th><th>e-mail</th><th>Цена</th><th></th></tr>';
        foreach($bids as $key=>$value){
          $return .='<tr>';
          $return .= '<td>'.$value->bid_date.'</td>';
          $return .= '<td>'.$value->name.'</td>';
          $return .= '<td>'.$value->email.'</td>';
          $return .= '<td>$'.$value->bid_price.'</td>';
          $return .= '<td>';
          if($key==0){
            //Old version was using Goolge Hangouts for communications.
            //$return .= '<g:hangout render="createhangout" hangout_type="normal" topic="'.addslashes($row->get('title')).'"
            //           invites="[{ id : \''.$value->email.'\', invite_type : \'EMAIL\' }]">
            //           </g:hangout>';
            //New version is using CloudInterpreter
            $return .= '<a target="_blank" href="http://dev.cloudinterpreter.com:8901/'.md5($row->get('id')).'"><button class="btn btn-success">Начать консультацию</button></a>';
          }
          $return .= '</td>';
          $return .='</tr>';
        }
        $return .= '</table>';
      }
		}
    echo $return;
	}
}
