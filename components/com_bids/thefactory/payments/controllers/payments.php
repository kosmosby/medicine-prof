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

class JTheFactoryPaymentsController extends JControllerLegacy
{
    var $name='Payments';
    var $_name='Payments';
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
   
        return parent::getView($name,$type,'JTheFactoryViewPayments',$config);
    }

    function History()
    {
        $Itemid=JRequest::getInt('Itemid');
        $user= JFactory::getUser();

        $modelorder=JTheFactoryPricingHelper::getModel('orders');
        $orders=$modelorder->getOrdersList($user->username);

        $view=$this->getView('payments');
        $view->assign('orders',$orders);
        $view->assign('pagination',$modelorder->pagination);
        $view->assign('Itemid',$Itemid);

        $view->display('history');

    }

}
