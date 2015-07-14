<?php

class JHTMLCommission {

    function selectType() {

        $input = JFactory::getApplication()->input;
        $selected = $input->get('commissionType');
        $task = $input->get('task');

        $opts = array();
        $opts[] = JHTML::_('select.option','',JText::_('COM_BIDS_SELECT_COMMISSION_TYPE'));
        $opts[] = JHTML::_('select.option','seller',JText::_('COM_BIDS_COMMISSION_SELLER'));
        $opts[] = JHTML::_('select.option','buyer',JText::_('COM_BIDS_COMMISSION_BUYER'));

        return JHTML::_('select.genericlist',$opts,'commissionType','onchange="document.adminForm.commissionType.value=this.value;Joomla.submitbutton(\''.$task.'\')"','value','text',$selected);
    }
}
