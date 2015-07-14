<?php

defined('_JEXEC') or die('Restricted access');

abstract class JHTMLRBidUser
{
    static function selectlist($name='userid',$attributes='',$defaultvalue=null)
    {
        $db = JFactory::getDbo();
        $query="select distinct u.id as `value`, u.username as `text` from #__users u where u.block!=1 order by username";
        $db->setQuery($query);
        $users = $db->loadObjectList();
        $users= array_merge(array(JHTML::_("select.option","",JText::_("COM_BIDS_ANY_USER"))),$users);
        return JHTML::_("select.genericlist",$users,$name,$attributes,'value', 'text', $defaultvalue);
    }

}
