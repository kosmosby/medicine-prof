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



defined('_JEXEC') or die();

class JHTMLEditProfile {

    static function selectCountry($selected) {
        $db = JFactory::getDbo();
        $db->setQuery('SELECT `name` AS text, `name` AS value FROM `#__bid_country`');
        $rows = array();
        $rows[] = JHTML::_('select.option', '', JText::_('COM_BIDS_ALL') );
        $rows = array_merge($rows, $db->loadObjectList());

        return JHTML::_('select.genericlist', $rows, 'country', 'class="inputbox"', 'value', 'text', $selected);
    }
}
