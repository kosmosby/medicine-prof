<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HelloWorlds View
 */
class flexpaperViewCertificates extends JView
{
	/**
	 * HelloWorlds view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Get data from the model
		$items = $this->get('Items');

//        echo "<pre>";
//        print_r($items); die;
//

		$pagination = $this->get('Pagination');
        $state = $this->get('State');

        $this->state		= $this->get('State');

        $this->sortDirection = $state->get('filter_order_Dir');
        $this->sortColumn = $state->get('filter_order');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign data to the view
		$this->items = $items;
		$this->pagination = $pagination;

        // Set the toolbar
        $this->addToolBar();





        // Display the template
		parent::display($tpl);
	}

    protected function addToolBar()
    {
        JToolBarHelper::title(JText::_('COM_FLEXPAPER_CERTIFICATE_MANAGER'), 'certificates');
//        JToolBarHelper::deleteList('', 'certificates.delete');
        JToolBarHelper::editList('certificate.edit');
//        JToolBarHelper::addNew('certificate.add');
    }




}