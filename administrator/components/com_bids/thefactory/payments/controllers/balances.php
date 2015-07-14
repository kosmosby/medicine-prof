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

class JTheFactoryBalancesController extends JTheFactoryController
{
    var $name='Balances';
    var $_name='Balances';
	function __construct()
	{
       parent::__construct('payments');
       JHtml::addIncludePath($this->basepath.DS.'html');
    }
    function Listing()
    {
        $model= JModel::getInstance('Balance','JTheFactoryModel');
        $rows=$model->getBalancesList();

        $opts=array();
        $opts[]=JHTML::_('select.option', '', JText::_("FACTORY_ALL"));
        $opts[]=JHTML::_('select.option', '1', JText::_("FACTORY_ALL_WITH_NON_ZERO_BALANCES"));
        $opts[]=JHTML::_('select.option', '2', JText::_("FACTORY_ALL_WITH_NEGATIVE_BALANCES"));
        $filter_balances=JHTML::_('select.genericlist',$opts,'filter_balances',"class='inputbox'",'value','text',$model->get('filter_balances')); 
        
        $view=$this->getView('balance');
        $view->assign('userbalances',$rows);
        $view->assign('filter_userid',$model->get('filter_userid'));
        $view->assign('filter_balances',$filter_balances);
        $view->assign('pagination',$model->get('pagination'));
        $view->display('list');
    }

}
