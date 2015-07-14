<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HelloWorld View
 */
class flexpaperViewCertificate extends JView
{
	/**
	 * display method of Hello view
	 * @return void
	 */
	public function display($tpl = null) 
	{

        $document =& JFactory::getDocument();

        $document->addScript('components/com_flexpaper/js/jquery.js');
        $document->addScript('components/com_flexpaper/js/admin.js');


		// get the Data
		//$form = $this->get('Form');
		$item = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		//$this->form = $form;
		$this->item = $item;

//        echo "<pre>";
//        print_r($this->item); 

        $mosConfig_live_site = 'http://'. $_SERVER['HTTP_HOST'];

        $this->assignRef('path',$mosConfig_live_site);

		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		$input = JFactory::getApplication()->input;
		$input->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		JToolBarHelper::title($isNew ? JText::_('COM_FLEXPAPER_NEW_CERTIFICATE')
		                             : JText::_('COM_FLEXPAPER_EDIT_CERTIFICATE'), 'certificates');
//		JToolBarHelper::save('certificate.save');
		JToolBarHelper::cancel('certificate.cancel', $isNew ? 'JTOOLBAR_CANCEL'
		                                                   : 'JTOOLBAR_CLOSE');
	}
}
