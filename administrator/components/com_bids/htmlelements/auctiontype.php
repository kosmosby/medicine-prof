<?php

defined('_JEXEC') or die('Restricted access');

abstract class JHTMLAuctiontype
{
    static function selectlist($name='auction_type',$attributes='',$defaultvalue=null)
    {
        $opts = array();
        $opts[] = JHTML::_('select.option', '', JText::_("COM_BIDS_SELECT_AUCTION_TYPE" ));
        $opts[] = JHTML::_('select.option', AUCTION_TYPE_PUBLIC,"Public Auction");
        $opts[] = JHTML::_('select.option', AUCTION_TYPE_PRIVATE,"Private Auction");

        return JHTML::_('select.genericlist',  $opts, $name, $attributes,  'value', 'text', $defaultvalue);
    }

}
