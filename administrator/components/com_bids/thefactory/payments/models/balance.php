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

class JTheFactoryModelBalance extends JModel
{
    var $context='balance';
    var $tablename=null;
    function __construct()
    {
        $this->context=APP_EXTENSION."_balance.";
        $this->tablename='#__'.APP_PREFIX.'_payment_balance';
        JTheFactoryHelper::tableIncludePath('payments');
        parent::__construct();
    }
    function getUserBalance($userid=null)
    {
        if (!$userid){
            $my= JFactory::getUser();
            $userid=$my->id;
        }
        $balance=JTable::getInstance('BalanceTable','JTheFactory');
        $balance->load($userid);
        return $balance;
    }
    function decreaseBalance($amount,$userid=null)
    {
        if (!$userid){
            $my= JFactory::getUser();
            $userid=$my->id;
        }
        $balance=JTable::getInstance('BalanceTable','JTheFactory');
        if (!$balance->load($userid))
            $balance->addBalance($userid);
        $balance->userid=$userid;
        $balance->balance-=$amount;
        if (!$balance->currency){
            $model= JModel::getInstance('Currency','JTheFactoryModel');
            $balance->currency=$model->getDefault();
        }
        $balance->store();
        return $balance;
    }
    function increaseBalance($amount,$userid=null)
    {
        if (!$userid){
            $my= JFactory::getUser();
            $userid=$my->id;
        }
        $balance=JTable::getInstance('BalanceTable','JTheFactory');
        if (!$balance->load($userid))
            $balance->addBalance($userid);
            
        $balance->userid=$userid;
        $balance->balance+=$amount;
        if (!$balance->currency){
            $model= JModel::getInstance('Currency','JTheFactoryModel');
            $balance->currency=$model->getDefault();
        }
        $balance->store();
           
        return $balance;
    }
    function getBalancesList()
    {
        $db= $this->getDbo();
        $app= JFactory::getApplication();

        $filter_userid=$app->getUserStateFromRequest( $this->context.'filter_userid',	'filter_userid',	'',	'string' );
        $filter_balances=$app->getUserStateFromRequest( $this->context.'filter_balances',	'filter_balances',	1,	'int' );

        $limit=$app->getUserStateFromRequest($this->context."limit" , 'limit',$app->getCfg('list_limit') );
        $limitstart=$app->getUserStateFromRequest($this->context."limitstart" , 'limitstart',0);
        
        $this->set('filter_userid',$filter_userid);
        $this->set('filter_balances',$filter_balances);

        jimport('joomla.html.pagination');
        $this->pagination=new JPagination($this->getTotal(), $limitstart, $limit);
        $where="where 1=1 ";
        if($this->get('filters')) $where.="and ".$this->get('filters');
        if($filter_userid) $where.=" and u.`username` like '%$filter_userid%' ";
        if($filter_balances==1) $where.=" and p.`balance` is not null and p.`balance`<>0 ";
        if($filter_balances==2) $where.=" and p.`balance` is not null and p.`balance`<0 ";
        
        $db->setQuery("select p.balance,p.currency,u.username,u.id as userid from `#__users` u
                        left join `{$this->tablename}` p on u.id=p.userid
                        $where
                        order by username ",$limitstart,$limit);

        return $db->loadObjectList();
    }

    function getTotal()
    {
        $db= $this->getDbo();
        $db->setQuery("select count(*) from `{$this->tablename}` ");
        return $db->loadResult();
    }

}
