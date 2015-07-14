<?php

defined('_JEXEC') or die('Restricted access');

abstract class JHTMLAuctionApproved
{
    static function selectlist($name='filter_approved',$attributes='',$defaultvalue=null)
    {
        $opts = array();
        $opts[] = JHTML::_('select.option', "",JText::_("COM_BIDS_ANY_AUCTION"));
        $opts[] = JHTML::_('select.option', 1,JText::_("COM_BIDS_APPROVED"));
        $opts[] = JHTML::_('select.option', 0,JText::_("COM_BIDS_PENDING_APPROVAL"));
        return JHTML::_('select.genericlist',  $opts, $name, $attributes ,  'value', 'text', $defaultvalue);
    }

}
