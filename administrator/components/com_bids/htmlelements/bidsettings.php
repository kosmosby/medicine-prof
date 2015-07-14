<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access.');

class JHTMLBidSettings {

    function quickIconButton($link, $image, $text) {
        $html ="     	
    	<div style=\"float:left;\">
    		<div class=\"icon\">
    			<a href=\"{$link}\">".
    				JHTML::_('image.administrator', $image,'../components/'.APP_EXTENSION.'/images/menu/', NULL, NULL, $text )
    				."<span>$text</span>
    			</a>
    		</div>
    	</div>";
        return $html;
    }
}
