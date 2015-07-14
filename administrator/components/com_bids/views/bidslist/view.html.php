<?php
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view'); 
class JBidsAdminViewBidsList extends JBidsAdminView
{
    function display($tpl = null)
    {
        JHtml::_('behavior.framework');

        parent::display($tpl);
    }

    function addToolBar() {
        JToolBarHelper::title(JText::_( 'COM_BIDS_HISTORY' ), 'bids');
    }
    
}
