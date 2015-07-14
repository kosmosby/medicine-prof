<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 3.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: Bids
 * @subpackage: Pay per bid
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JBidAdminBidController extends JControllerLegacy
{
    var $name='AdminBid';
    var $_name='AdminBid';
    var $itemname='bid';
    var $itempath=null;

	function __construct()
	{
	    $this->itempath=JPATH_COMPONENT_ADMINISTRATOR.DS.'pricing'.DS.$this->itemname;
	   $config=array(
            'view_path'=>$this->itempath.DS."views"
       );
       JLoader::register('JBidAdminBidToolbar',$this->itempath.DS.'toolbars'.DS.'toolbar.php');
       JLoader::register('JBidAdminBidHelper',$this->itempath.DS.'helpers'.DS.'helper.php');
       jimport('joomla.application.component.model');
       JModel::addIncludePath($this->itempath.DS.'models');
       JTable::addIncludePath($this->itempath.DS.'tables');
       $lang= JFactory::getLanguage();
        $lang->load(APP_PREFIX.'.'.$this->itemname);
       parent::__construct($config);

    }
    function getView( $name = '', $type = 'html', $prefix = '', $config = array() )
    {
        $MyApp= JTheFactoryApplication::getInstance();
        $config['template_path']=$this->itempath.DS.'views'.DS.strtolower($name).DS."tmpl";
        return parent::getView($name,$type,$prefix,$config);
    }

    function execute($task)
    {
        JBidAdminBidToolbar::display($task);
        return parent::execute($task);
    }
    function config()
    {
        JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'htmlelements');

        $model= JModel::getInstance('Bid','J'.APP_PREFIX.'PricingModel');
        $r=$model->getItemPrices();

        $view=$this->getView('Config','html','JBidPricingViewBid');
        $view->assign('currency',$r->default_currency);
        $view->assign('default_price',$r->default_price);
        $view->assign('price_powerseller',$r->price_powerseller);
        $view->assign('allow_no_funds',$r->allow_no_funds);
        $view->assign('price_verified',$r->price_verified);
        $view->assign('itemname',$this->itemname);

        $view->display();
    }
    function save()
    {
        $d=JRequest::get('post');
        $model= JModel::getInstance('Bid','J'.APP_PREFIX.'PricingModel');
        $model->saveItemPrices($d);

        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=pricing.config&item='.$this->itemname,JText::_('COM_BIDS_SETTINGS_SAVED'));
    }

    function cancel()
    {
        $app = JFactory::getApplication();
        $app->redirect('index.php?option='.APP_EXTENSION.'&task=pricing.listing');//redirect to main listing
    }
}
