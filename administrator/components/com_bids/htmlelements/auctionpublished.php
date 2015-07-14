<?php

defined('_JEXEC') or die('Restricted access');

abstract class JHTMLAuctionpublished
{
    static function selectlist($name='published',$attributes='',$defaultvalue=null)
    {
        $opts = array();
        $opts[] = JHTML::_('select.option', 1,JText::_("COM_BIDS_PUBLISHED"));
        $opts[] = JHTML::_('select.option', 0,JText::_("COM_BIDS_UNPUBLISHED"));
        return JHTML::_('select.radiolist',  $opts, $name, $attributes ,  'value', 'text', $defaultvalue);
    }

}
