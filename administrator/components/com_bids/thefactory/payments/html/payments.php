<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: payments
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

abstract class JHTMLPayments
{
    static function StatusList($defaultvalue=null)
    {
        $status[]=JHtml::_('select.option','ok','ok');
        $status[]=JHtml::_('select.option','error','error');
        $status[]=JHtml::_('select.option','manual_check','manual_check');

        return JHtml::_('select.genericlist',$status,'paymentstatus','','value','text',$defaultvalue);
    }
    static function CurrencyList()
    {
        $model= JModel::getInstance('Currency','JTheFactoryModel');
        $currency=$model->getDefault();
        $currencies[]=JHtml::_('select.option',$currency,$currency);

        return JHtml::_('select.genericlist',$currencies,'currency');
    }
    static function UserList($defaultuser=null)
    {
        $db= JFactory::getDbo();
        $db->setQuery("select `username` as text,`id` as value from `#__users` order by `username`");
        $users=$db->loadObjectList();
        return JHtml::_('select.genericlist',$users,'userid','','value','text',$defaultuser);
    }
}
