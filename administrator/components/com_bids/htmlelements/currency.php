<?php

defined('_JEXEC') or die('Restricted access');

abstract class JHTMLCurrency
{
    static function selectlist($name='currency',$attributes='',$defaultvalue=null)
    {
        $db= JFactory::getDbo();
        $db->setQuery("select `name` as text,`name` as value from `#__".APP_PREFIX."_currency` order by `name`");
        $currencies=$db->loadObjectList();
        if(!$defaultvalue){
            $db->setQuery("select `name` as value from `#__".APP_PREFIX."_currency` where `default`=1");
            $defaultvalue=$db->loadResult();
        }

        return JHtml::_('select.genericlist',$currencies,$name,$attributes,'value','text',$defaultvalue);
    }

}
