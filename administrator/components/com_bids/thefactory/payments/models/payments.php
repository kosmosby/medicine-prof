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
class JTheFactoryModelPayments extends JModel
{
    var $context='payments';
    var $tablename=null;
    var $pagination=null;
    function __construct()
    {
        $this->context=APP_EXTENSION."_payments.";
        $this->tablename='#__'.APP_PREFIX.'_payment_log';
        JTheFactoryHelper::tableIncludePath('payments');
        parent::__construct();
    }
    function getPaymentsList($username='')
    {

        $db= $this->getDbo();
        $app= JFactory::getApplication();

        $limit=$app->getUserStateFromRequest($this->context."limit" , 'limit',$app->getCfg('list_limit') );
        $limitstart=$app->getUserStateFromRequest($this->context."limitstart" , 'limitstart',0);

        jimport('joomla.html.pagination');
        $this->pagination=new JPagination($this->getTotal($username), $limitstart, $limit);

        $q = $db->getQuery(true);
        $q->select('p.*,u.username')
            ->from($this->tablename.' p')
            ->leftJoin('#__users u ON u.id=p.userid')
            ->order('p.id')
        ;
        if(!empty($username)) {
            $q->where('u.username LIKE \'%'.$db->escape($username).'%\'');
        }

        $db->setQuery($q,$limitstart,$limit);

        return $db->loadObjectList();
    }

    function getTotal($username='')
    {
        $db= $this->getDbo();
        $q = $db->getQuery(true);
        $q->select('COUNT(1)')
            ->from($this->tablename . ' p')
            ->leftJoin('#__users u ON u.id=p.userid')
        ;
        if (!empty($username)) {
            $q->where('u.username LIKE \'%' . $db->escape($username) . '%\'');
        }
        $db->setQuery($q);

        return $db->loadResult();
    }

}
