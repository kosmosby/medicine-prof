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

class JBidsAdminViewSettingsPanel extends JBidsAdminView {

    function addToolBar() {

        JToolBarHelper::title(JText::_('COM_BIDS_AUCTIONS_FACTORY_SETTINGS'), 'bids');
    }

    function display($tpl=null) {

        $db =  JFactory::getDBO();
        $db->setQuery("select * from #__bid_paysystems where enabled=1");
        $gateways = $db->loadObjectList();

        $db->setQuery("select * from #__bid_pricing where enabled=1");
        $items = $db->loadObjectList();

        $db->setQuery("select * from #__bid_cronlog where event='cron' order by logtime desc limit 1");
        $log = $db->loadObject();

        $cfg =  JTheFactoryHelper::getConfig();

        $this->assignref('gateways', $gateways);
        $this->assignref('items', $items);
        if ($log)
            $this->assignref('latest_cron_time', $log->logtime);
        else
            $this->assign('latest_cron_time', JText::_('COM_BIDS_NEVER'));
        $this->assignref('cfg', $cfg);

        parent::display($tpl);
    }

}
