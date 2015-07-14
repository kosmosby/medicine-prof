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

class JTheFactoryBalanceController extends JControllerLegacy
{
    var $name='Balance';
    var $_name='Balance';
    var $description='Add funds to your balance';

	function __construct($config=array())
	{
        $MyApp= JTheFactoryApplication::getInstance();
        $lang= JFactory::getLanguage();
        $lang->load('thefactory.payments');

        $config['view_path']=$MyApp->app_path_front.'payments'.DS."views";

        parent::__construct($config);
        JTheFactoryHelper::modelIncludePath('payments');
        JTheFactoryHelper::tableIncludePath('payments');

    }
    function getView( $name = '', $type = 'html', $prefix = '', $config = array() )
    {
        $MyApp= JTheFactoryApplication::getInstance();
        $config['template_path']=$MyApp->app_path_front.'payments'.DS."views".DS.strtolower($name).DS."tmpl";
        return parent::getView($name,$type,'JTheFactoryViewBalance',$config);
    }

    function addFunds()
    {
        $Itemid=JRequest::getInt('Itemid');
        $balance= JModelLegacy::getInstance('balance','JTheFactoryModel');
        $currency= JModelLegacy::getInstance('currency','JTheFactoryModel');
        $user_balance=$balance->getUserBalance();
        $default_currency=$currency->getDefault();
        $view=$this->getView('balance');
        $view->assign('balance',$user_balance);
        $view->assign('currency',$default_currency);
        $view->assign('Itemid',$Itemid);
        $view->display('addfunds');

    }
    function checkout()
    {
        $Itemid=JRequest::getInt('Itemid');
        $price=JRequest::getFloat('amount');
        if ($price<=0){
            JError::raiseWarning(510,JText::_("FACTORY_AMOUNT_MUST_BE"));
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=balance.addfunds&Itemid=".$Itemid);
            return;
        }
        $currency= JModelLegacy::getInstance('currency','JTheFactoryModel');
        $default_currency=$currency->getDefault();

        $modelorder=JTheFactoryPricingHelper::getModel('orders');
        $item=new stdClass();
        $item->itemname=$this->name;
        $item->itemdetails=JText::_($this->description);
        $item->iteminfo=null;
        $item->price=$price;
        $item->currency=$default_currency;
        $item->quantity=1;
        $item->params='';

        $order=$modelorder->createNewOrder($item,$price,$default_currency,null,'P');
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=orderprocessor.checkout&orderid=$order->id&Itemid=".$Itemid);

    }
}
