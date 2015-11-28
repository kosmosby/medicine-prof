<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2014 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Plugin\GroupJiveVideo\Table\VideoTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_groupjiveVideoActivity
{

	/**
	 * render frontend event activity
	 *
	 * @param CB\Plugin\Activity\Table\ActivityTable $row
	 * @param string                                 $title
	 * @param string                                 $message
	 * @param CB\Plugin\Activity\Activity            $stream
	 * @param VideoTable                             $video
	 * @param cbgjVideoPlugin                        $plugin
	 * @return string
	 */
	static function showVideoActivity( $row, &$title, &$message, $stream, $video, $plugin )
	{
		global $_CB_framework;

		$title			=	CBTxt::T( 'GROUP_VIDEO_ACTIVITY_TITLE', 'published a video in [group]', array( '[group]' => '<strong><a href="' . $_CB_framework->pluginClassUrl( $plugin->_gjPlugin->element, true, array( 'action' => 'groups', 'func' => 'show', 'id' => (int) $video->group()->get( 'id' ) ) ) . '">' . htmlspecialchars( CBTxt::T( $video->group()->get( 'name' ) ) ) . '</a></strong>' ) );

		$return			=	'<div class="gjVideoActivity">'
						.		'<div class="gjGroupVideoRow gjContainerBox img-thumbnail">'
						.			'<div class="gjContainerBoxHeader">'
						.				'<video width="640" height="360" style="width: 100%; height: 100%;" src="' . htmlspecialchars( $video->get( 'url' ) ) . '" type="' . htmlspecialchars( $video->mimeType() ) . '" controls="controls" preload="none" class="streamItemVideo gjVideoPlayer"></video>'
						.			'</div>'
						.			'<div class="gjContainerBoxBody text-left">'
						.				'<div class="gjContainerBoxTitle">'
						.					'<strong>'
						.						'<a href="' . htmlspecialchars( $video->get( 'url' ) ) . '" target="_blank" rel="nofollow">' . htmlspecialchars( ( $video->get( 'title' ) ? $video->get( 'title' ) : $video->name() ) ) . '</a>'
						.					'</strong>'
						.				'</div>';

		if ( $video->get( 'caption' ) ) {
			$return		.=				'<div class="gjContainerBoxContent cbMoreLess">'
						.					'<div class="cbMoreLessContent">'
						.						htmlspecialchars( $video->get( 'caption' ) )
						.					'</div>'
						.					'<div class="cbMoreLessOpen fade-edge hidden">'
						.						'<a href="javascript: void(0);" class="cbMoreLessButton">' . CBTxt::T( 'See More' ) . '</a>'
						.					'</div>'
						.				'</div>';
		}

		$return			.=			'</div>'
						.		'</div>'
						.	'</div>';

		return $return;
	}
}