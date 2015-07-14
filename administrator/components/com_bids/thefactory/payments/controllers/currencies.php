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


class JTheFactoryCurrenciesController extends JTheFactoryController
{

    var $name='Currencies';
    var $_name='Currencies';
	function __construct()
	{
       parent::__construct('payments');
       JHtml::addIncludePath($this->basepath.DS.'html');
    }

    function Listing()
    {
        $db		=  JFactory::getDBO();

        $db->setQuery("SELECT * FROM `#__".APP_PREFIX."_currency` order by `ordering`");
        $rows = $db->loadObjectList();


        $view=$this->getView('currency','html');
        $view->assignRef('currencies',$rows);
        $view->display();

    }
    function Edit()
    {
        $cids=JRequest::getVar('cid');
        if (is_array($cids)) $cids=$cids[0];
        $currtable= JTable::getInstance('CurrencyTable','JTheFactory');
        
        if (!$currtable->load($cids)){
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=currencies.listing",JText::_("FACTORY_CURRENCY_DOES_NOT_EXIST"));
            return;        
        }
        
        $view=$this->getView('currency','html');
        $view->assignRef('currency',$currtable);
        $view->display("edit");
        
    }

    function Delete()
    {
        $cids=JRequest::getVar('cid');
        $msg="";
        if (count($cids))
        {
            $cid_list=implode(',',$cids);
            $db		=  JFactory::getDBO();
            $db->setQuery("DELETE FROM `#__".APP_PREFIX."_currency` where id in ({$cid_list})");
            $db->query();
            $msg=$db->getAffectedRows().JText::_("FACTORY_ROWS_DELETED");
        }
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=currencies.listing",$msg);

    }
    function SetDefault()
    {
        $cid=JRequest::getVar('cid');
        $currtable= JTable::getInstance('CurrencyTable','JTheFactory');
        if ($cid && $currtable->load($cid))
        {
            $db= JFactory::getDBO();
            $db->setQuery("UPDATE `#__".APP_PREFIX."_currency` set `default`=null");
            $db->query();
            $db->setQuery("UPDATE `#__".APP_PREFIX."_currency` set `default`=1 where id={$cid}");
            $db->query();
            $db->setQuery("UPDATE `#__".APP_PREFIX."_currency` set `convert`=`convert`/{$currtable->convert}");
            $db->query();
            $msg=JText::_("FACTORY_NEW_DEFAULT_CURRENCY").": ".$currtable->name;
            JTheFactoryEventsHelper::triggerEvent('onDefaultCurrencyChange');
        }else $msg=JText::_("FACTORY_CURRENCY_NOT_FOUND");
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=currencies.listing",$msg);

    }
    function NewItem()
    {
        $view=$this->getView('currency','html');
        $view->display("new");
    }
    function Save()
    {
        $currtable= JTable::getInstance('CurrencyTable','JTheFactory');
        $id=JRequest::getInt('id');
        $olddefault=0;
        $oldorder=0;
        if ($currtable->load($id))
        {
            $olddefault=$currtable->default;
            $oldorder=$currtable->ordering;
        }
                
        $currtable->bind(JRequest::get('post'));
        $currtable->default=$olddefault;
        $currtable->ordering=$oldorder;
        
        $currtable->store();
        
        if ($this->doTask=='saveadd')
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=currencies.newitem");
        else
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=currencies.listing");

    }
    function saveadd()
    {
        self::save();
    }
    function reorder()
    {
        $db		=  JFactory::getDBO();
        $r=JRequest::get('request');

        foreach($r as $k=>$v)
            if (substr($k,0,6)=='order_')
            {
                $id=substr($k,6);
                $db->setQuery("update `#__".APP_PREFIX."_currency` set `ordering`='$v' where id=$id ");
                $db->query();
            }


        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=currencies.listing");
    }
    function RefreshConversions()
    {
        $model= JModel::getInstance('Currency','JTheFactoryModel');
        $currtable= JTable::getInstance('CurrencyTable','JTheFactory');

        $currencies=$model->getCurrencyList();
        $default_currency=$model->getDefault();
        $results=array();
        foreach($currencies as $currency){
            if ($currency->name==$default_currency){
                $currtable->load($currency->id);
                $currtable->convert=1;
                $currtable->store();
                $results[]=$currency->name." ---> ".$default_currency." = 1";
                continue;
            }
            $conversion=$model->getGoogleCurrency($currency->name,$default_currency);
            if ($conversion===false){
                $results[]=JText::_("FACTORY_ERROR_CONVERTING")." {$currency->name} --> $default_currency";
                continue;
            }

            $currtable->load($currency->id);
            $currtable->convert=$conversion;
            $currtable->store();
            $results[]=$currency->name." ---> ".$default_currency." = $conversion see <a href='https://www.google.com/search?q=1+{$currency->name}+to+{$default_currency}' target='_blank'>in google</a>";

        }

        JTheFactoryEventsHelper::triggerEvent('onConversionRateChange');

        $view=$this->getView('currency','html');
        $view->assignRef("default_currency",$default_currency);
        $view->assignRef("results",$results);
        $view->display("conversion");

    }
}
