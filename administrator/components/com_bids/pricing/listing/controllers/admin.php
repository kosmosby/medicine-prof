<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: Bids
 * @subpackage: Pay per listing
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JBidAdminListingController extends JControllerLegacy
{
    var $name='AdminListing';
    var $_name='AdminListing';
    var $itemname='listing';
    var $itempath=null;

	function __construct()
	{
	    $this->itempath=JPATH_COMPONENT_ADMINISTRATOR.DS.'pricing'.DS.$this->itemname;
	   $config=array(
            'view_path'=>$this->itempath.DS."views"
       );
       JLoader::register('J'.APP_PREFIX.'AdminListingToolbar',$this->itempath.DS.'toolbars'.DS.'toolbar.php');
       JLoader::register('J'.APP_PREFIX.'AdminListingHelper',$this->itempath.DS.'helpers'.DS.'helper.php');
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
        return parent::getView($name,$type,'J'.APP_PREFIX.'PricingViewListing',$config);
    }

    function execute($task)
    {
        JBidAdminListingToolbar::display($task);
        return parent::execute($task);
    }
    function config()
    {
        JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'htmlelements');

        $model= JModel::getInstance('Listing','J'.APP_PREFIX.'PricingModel');
        $r=$model->getItemPrices();

        JModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'thefactory'.DS.'category'.DS.'models');
        $catModel= JModel::getInstance('Category','JTheFactoryModel');
        $cattree=$catModel->getCategoryTree();

        $view=$this->getView('Config');
        $view->assign('currency',$r->default_currency);
        $view->assign('default_price',$r->default_price);
        $view->assign('price_powerseller',$r->price_powerseller);
        $view->assign('price_verified',$r->price_verified);
        $view->assign('category_pricing_enabled',$r->category_pricing_enabled);
        $view->assign('category_pricing',$r->category_pricing);
        $view->assign('category_tree',$cattree);
        $view->assign('itemname',$this->itemname);

        $view->display();
    }
    function save()
    {
        $d=JRequest::get('post');
        $model= JModel::getInstance('Listing','J'.APP_PREFIX.'PricingModel');
        $model->saveItemPrices($d);

        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=pricing.config&item='.$this->itemname,JText::_('COM_BIDS_SETTINGS_SAVED'));
    }
    function cancel()
    {
        $app = JFactory::getApplication();
        $app->redirect('index.php?option='.APP_EXTENSION.'&task=pricing.listing');//redirect to main listing
    }
}
