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

jimport('joomla.application.component.model');

class JTheFactoryModelCurrency extends JModel
{
    var $context='currency';
    var $tablename=null;
    function __construct()
    {
        $this->context=APP_EXTENSION."_currency";
        $this->tablename='#__'.APP_PREFIX.'_currency';
        JTheFactoryHelper::tableIncludePath('payments');
        parent::__construct();
    }
    function getDefault()
    {
        $db= $this->getDbo();
        //ensure that if there is no default set, we get a currency
        $db->setQuery("select `name` from `{$this->tablename}` order by `default`=1 desc",0,1);
        return $db->loadResult();
    }
    function getCurrencyList()
    {
        $db= $this->getDbo();
        $db->setQuery("select * from `{$this->tablename}` order by `ordering`");
        return $db->loadObjectList();

    }
    function getGoogleCurrency($currency_from,$currency_to)
    {
        $url="http://www.google.com/ig/calculator?hl=en&q=1+$currency_from=?$currency_to";
        $res=JTheFactoryHelper::remote_read_url($url);
        $matches=array();
        if (preg_match('/lhs:\s*"([^"]*)"\s*,rhs:\s*"([^"]*)"\s*,error:\s*"([^"]*)"/',$res,$matches)){
            $error=$matches[3];
            $rate=$matches[2];
            if ($error) return false;
            return floatval(substr($rate,0,strpos($rate," ")));

        }else
            return false;
    }
    function convertToDefaultCurrency($amount,$currency)
    {
        if (!$currency) return $amount;
        $db = JFactory::getDbo();
        $db->setQuery('SELECT * from #__'.APP_PREFIX.'_currency WHERE name='.$db->quote($currency));
        $p = $db->loadObject();
        return $amount*$p->convert;
    }
    function convertCurrency($amount,$currency,$tocurrency)
    {
        if (!$tocurrency || !$currency) return $amount;

        $db= JFactory::getDbo();
        $db->setQuery('SELECT * from #__'.APP_PREFIX.'_currency WHERE name='.$db->quote($currency));
        $c1=$db->loadObject();
        $db->setQuery('SELECT * from #__'.APP_PREFIX.'_currency WHERE name='.$db->quote($tocurrency));
        $c2=$db->loadObject();

        $convert = $c2->convert ? ($c1->convert/$c2->convert) : 0;

        return $amount*$convert;
    }
}
