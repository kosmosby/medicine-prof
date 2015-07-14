<?php
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view'); 
class JBidsAdminViewMessages extends JBidsAdminView
{
    function display($tpl=null) {

        JHtml::_('behavior.framework');

        return parent::display($tpl);
    }
}
