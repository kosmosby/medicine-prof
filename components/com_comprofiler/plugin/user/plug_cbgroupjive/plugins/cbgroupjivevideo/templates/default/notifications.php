<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Database\Table\UserTable;
use CB\Plugin\GroupJive\Table\GroupTable;
use CB\Plugin\GroupJive\Table\NotificationTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_groupjiveVideoNotifications
{

	/**
	 * render frontend group video notify params
	 *
	 * @param NotificationTable $row
	 * @param array             $input
	 * @param GroupTable        $group
	 * @param UserTable         $user
	 * @param cbgjVideoPlugin   $plugin
	 * @return string
	 */
	static function showVideoNotifications( $row, $input, $group, $user, $plugin )
	{
		$return			=	'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
						.		'<label for="params__video_new" class="col-sm-9 control-label">' . CBTxt::T( 'Publish of new video' ) . '</label>'
						.		'<div class="cb_field col-sm-3 text-right">'
						.			$input['video_new']
						.		'</div>'
						.	'</div>';

		if ( $row->group()->params()->get( 'video', 1 ) == 2 ) {
			$return		.=	'<div class="cbft_select cbtt_select form-group cb_form_line clearfix">'
						.		'<label for="params__video_approve" class="col-sm-9 control-label">' . CBTxt::T( 'New video requires approval' ) . '</label>'
						.		'<div class="cb_field col-sm-3 text-right">'
						.			$input['video_approve']
						.		'</div>'
						.	'</div>';
		}

		return $return;
	}
}