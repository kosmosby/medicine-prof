<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HelloWorlds View
 */
class flexpaperViewQuestions extends JView
{
	/**
	 * HelloWorlds view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

        $state = $this->get('State');
        $this->state		= $this->get('State');
        $this->sortDirection = $state->get('filter_order_Dir');
        $this->sortColumn = $state->get('filter_order');


        //selectlist tests
		JLoader::import( 'tests', JPATH_SITE . DS . 'components' . DS . 'com_flexpaper' . DS . 'models' );
        $tests_model = JModel::getInstance( 'tests', 'flexpaperModel' );

        $testslist = $tests_model->getItems();


        JLoader::import( 'course', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_flexpaper' . DS . 'models' );
        $course_model = JModel::getInstance( 'course', 'flexpaperModel' );

        $tests_list_for_select = $course_model->prepare_for_select($testslist,'name');

        //dropdown tests
        $options1 = array();
        $default = $this->state->get('filter.test_id');

        $options1[] = JHTML::_( 'select.option', '', JText::_('COM_FLEXPAPER_SELECT_TEST') );
        foreach( $tests_list_for_select as $key=>$value) {
            $options1[] = JHTML::_( 'select.option', $key, $value );

        }
        $this->tests_list =& JHTML::_('select.genericlist',$options1,'filter_test_id','class="inputbox" onchange="this.form.submit()"','value','text',$default);


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
        JToolBarHelper::title(JText::_('COM_FLEXPAPER_QUESTIONS_MANAGER'), 'questions');
        JToolBarHelper::deleteList('', 'questions.delete');
        JToolBarHelper::editList('question.edit');
        JToolBarHelper::addNew('question.add');
    }




}