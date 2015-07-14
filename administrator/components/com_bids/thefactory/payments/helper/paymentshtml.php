<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: payments
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

class JTheFactoryPaymentsHtmlHelper
{
    static function quickIconButton($link, $image, $text)
    {
        $html = "
    	<div style=\"float:left;\">
    		<div class=\"icon\">
    			<a href=\"{$link}\">" .
                JHTML::_('image.administrator', $image, '../components/' . APP_EXTENSION . '/images/menu/', NULL, NULL, $text)
                . "<span>$text</span>
    			</a>
    		</div>
    	</div>";
        return $html;
    }
}