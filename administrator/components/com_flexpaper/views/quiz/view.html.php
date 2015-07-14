<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HelloWorld View
 */
class flexpaperViewquiz extends JView
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

        $model = $this->getModel('quiz');
        $testslist = $model->getMembershipQuizList();

//       echo "<pre>";
//       print_r($this->item); die;
//       echo $this->item->id; die;

        //dropdown membershiplists
        $options1 = array();
        $default = $this->item->test_id;
//        $months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr');
//        $options1[] = JHTML::_( 'select.option', '', '-Membership List-' );
        foreach( $testslist as $key=>$value) {
            $options1[] = JHTML::_( 'select.option', $key, $value );
        }
        $this->testslist =& JHTML::_('select.genericlist',$options1,'test_id','class="inputbox" size=40','value','text',$default);


        //dropdown flexpaperlists
        $membershiplist = $model->getMembershipList();
        $options1 = array();
        $default = $this->item->membership_list_id;

//        $months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr');
        //$options1[] = JHTML::_( 'select.option', '', '-Flexpapers List-' );
        foreach( $membershiplist as $key=>$value) {
            $options1[] = JHTML::_( 'select.option', $key, $value );
        }
        $this->membershiplist =& JHTML::_('select.genericlist',$options1,'membership_list_id','class="inputbox" size=40','value','text',$default);



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
		$isNew = ($this->item->membership_list_id == 0);
		JToolBarHelper::title($isNew ? JText::_('COM_FLEXPAPER_NEW_QUIZ')
		                             : JText::_('COM_FLEXPAPER_EDIT_QUIZ'), 'quizes');
        JToolBarHelper::apply('quiz.apply');
		JToolBarHelper::save('quiz.save');
		JToolBarHelper::cancel('quiz.cancel', $isNew ? 'JTOOLBAR_CANCEL'
		                                                   : 'JTOOLBAR_CLOSE');
	}
}