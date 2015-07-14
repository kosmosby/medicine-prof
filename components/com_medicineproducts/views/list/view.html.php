<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content categories view.
 *
 * @since  1.5
 */
class MedicineProductsViewList extends JViewLegacy
{
	/**
	 * Language key for default page heading
	 *
	 * @var    string
	 * @since  3.2
	 */
    
        protected $params;
        public $items;
	protected $pageHeading = 'JGLOBAL_ARTICLES';

	/**
	 * @var    string  The name of the extension for the category
	 * @since  3.2
	 */
	protected $extension = 'com_medicineproducts';
        
        
        public function display($tpl = null)
	{
                $mainframe = JFactory::getApplication();
		$Itemid = JRequest::getInt('Itemid'); 
   

		$pathway  = $mainframe->getPathway();
		$document = JFactory::getDocument();
		$params	= $mainframe->getParams();
                $document->addStyleSheet(JUri::base()."components/com_medicineproducts/includes/jqgrid/css/ui.jqgrid-bootstarp.css");                
                $document->addStyleSheet(JUri::base()."components/com_medicineproducts/includes/jqgrid/css/ui.jqgrid.css");
                $document->addStyleSheet(JUri::base()."components/com_medicineproducts/includes/jqgrid/css/jquery-ui.css");
                
                JHtml::_('jquery.framework'); // load jquery
                JHtml::_('jquery.ui');
                //$document->addScript(JUri::base()."components/com_medicineproducts/includes/jqgrid/js/jquery-1.11.0.min.js");
	 	$document->addScript(JUri::base()."components/com_medicineproducts/includes/jqgrid/js/i18n/grid.locale-en.js");
	 	$document->addScript(JUri::base()."components/com_medicineproducts/js/medicineproducts.js");
                $document->addScript(JUri::base()."components/com_medicineproducts/includes/jqgrid/js/jquery.jqGrid.min.js");
	 	// Page Title
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$menu	= $menus->getActive();
                $this->items		= $this->get('Items');
                $title = JFactory::getDocument()->getTitle();
		//var_dump($this->items);
                if(!$title){
                    $title = JText::_('MEDPREP_TITLE');
                }
		
		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JRegistry;//new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',	$title);
			}
		} else {
			$params->set('page_title',	$title);
		}
		$document->setTitle( $params->get( 'page_title' ) );
		$pathway->addItem( JText::_( $title ));
                
                $this->item		= $this->get('Item');

		return parent::display($tpl);
	}
       
        
}
