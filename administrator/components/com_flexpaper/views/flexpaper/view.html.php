<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HelloWorld View
 */
class flexpaperViewflexpaper extends JView
{
	/**
	 * display method of Hello view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		// get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
 
		// echo "<pre>";
		// print_r($item); die;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->item = $item;

        $this->item->id = isset($this->item->id)?$this->item->id:'';
 
 		JLoader::import( 'courses', JPATH_SITE . DS . 'components' . DS . 'com_flexpaper' . DS . 'models' );
        $courses_model = JModel::getInstance( 'courses', 'flexpaperModel' );

        JLoader::import( 'course', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_flexpaper' . DS . 'models' );
        $course_model = JModel::getInstance( 'course', 'flexpaperModel' );

        $membershiplist = $courses_model->getItems();

//        echo "<pre>";
//        print_r($membershiplist); die;

        $membership_list_for_select = $course_model->prepare_for_select($membershiplist,'title');

   		//dropdown membershiplists
        $options1 = array();
        $default = isset($this->item->membership_list_id)?$this->item->membership_list_id:'';
        foreach( $membership_list_for_select as $key=>$value) {
            $options1[] = JHTML::_( 'select.option', $key, $value );
        }
        $this->membership_list =& JHTML::_('select.genericlist',$options1,'membership_list_id','class="inputbox" size=40','value','text',$default);


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
		$isNew = isset($this->item->id)?0:1;
		JToolBarHelper::title($isNew ? JText::_('COM_FLEXPAPER_NEW_DOCUMENT')
		                             : JText::_('COM_FLEXPAPER_EDIT_DOCUMENT'), 'documents');
		JToolBarHelper::save('flexpaper.save');
		JToolBarHelper::cancel('flexpaper.cancel', $isNew ? 'JTOOLBAR_CANCEL'
		                                                   : 'JTOOLBAR_CLOSE');
	}
}