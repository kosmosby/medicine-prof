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

class JHTMLBidCategories
{
    static function letterFilter($filter_cat)
    {

        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $filter_letter = JRequest::getString('filter_letter', 'all');

        $letters_filter = "<div id='box_letters_filter'>";
        foreach ($letters as $letter) {

            $active = strtolower($filter_letter) == strtolower($letter) ? 'active' : '';
            $letters_filter .= "<a href='" . BidsHelperRoute::getCategoryRoute($filter_cat, 'listcats', null, true, $letter) . "' class='" . $active . "'>" . $letter . "</a>";
        }
        // List all categories
        $letters_filter .= "<a href='" . BidsHelperRoute::getCategoryRoute($filter_cat, 'listcats', null, true, 'all') . "'>" . JText::_('COM_BIDS_CATEGORIES_LETTER_FILTER_ALL') . "</a>";
        $letters_filter .= "</div>";

        return $letters_filter;
    }
}