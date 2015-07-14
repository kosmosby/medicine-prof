<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HelloWorld View
 */
class flexpaperViewbundle extends JView
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
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->item = $item;

        $model = $this->getModel('bundle');
        $Membershiplist = $model->getMembershipBundleList();

//       echo "<pre>";
//       print_r($this->item); die;
//       echo $this->item->id; die;

        //dropdown membershiplists
        $options1 = array();
        $default = $this->item->id;
//        $months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr');
//        $options1[] = JHTML::_( 'select.option', '', '-Membership List-' );
        foreach( $Membershiplist as $key=>$value) {
            $options1[] = JHTML::_( 'select.option', $key, $value );
        }
        $this->bundleslist =& JHTML::_('select.genericlist',$options1,'bundle_id','class="inputbox" size=40','value','text',$default);


        //dropdown flexpaperlists
        $membershiplist = $model->getMembershipList();
        $options1 = array();
        $default = $this->item->bundles_id;

//        $months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr');
        //$options1[] = JHTML::_( 'select.option', '', '-Flexpapers List-' );
        foreach( $membershiplist as $key=>$value) {
            $options1[] = JHTML::_( 'select.option', $key, $value );
        }
        $this->membershiplist =& JHTML::_('select.genericlist',$options1,'membership_list_id[]','class="inputbox" multiple="multiple" size=40','value','text',$default);



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
		JToolBarHelper::title($isNew ? JText::_('COM_FLEXPAPER_NEW_BUNDLE')
		                             : JText::_('COM_FLEXPAPER_EDIT_BUNDLE'), 'bundles');
        JToolBarHelper::apply('bundle.apply');
		JToolBarHelper::save('bundle.save');
		JToolBarHelper::cancel('bundle.cancel', $isNew ? 'JTOOLBAR_CANCEL'
		                                                   : 'JTOOLBAR_CLOSE');
	}
}