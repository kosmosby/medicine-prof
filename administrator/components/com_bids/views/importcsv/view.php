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

defined('_JEXEC') or die('Restricted access');

class JBidsAdminViewImportCSV extends JBidsAdminView {

    function display() {
        parent::display();
    }

    function addToolBar() {

        JToolBarHelper::title(JText::_('COM_BIDS_AUCTIONS_FACTORY_TOOLS_IMPORT_LISTINGS'),'bids');
        JToolBarHelper::custom('importcsv', 'save', 'save', 'Save', false);
        JToolBarHelper::custom('offers', 'back', 'back', 'Back', false);
    }
}
