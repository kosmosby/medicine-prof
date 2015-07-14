<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HelloWorlds View
 */
class flexpaperViewflexpapers extends JView
{

    /**
     * method just for grab data
     */
    function copyflexpaper_membership_listToFlexpaper() {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query = "SELECT * FROM #__flexpaper_membership_list";
        $db->setQuery($query);
        $rows = $db->loadobjectList();

        for($i=0;$i<count($rows);$i++) {
            $query = "UPDATE #__flexpaper SET `membership_list_id`=".$rows[$i]->membership_list_id." WHERE id = ".$rows[$i]->flexpaper_id;
           $db->setQuery($query);
            $db->query();
        }
    }

	/**
	 * HelloWorlds view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
        //some special functions
//        $this->copyflexpaper_membership_listToFlexpaper();
//        die;

		// Get data from the model
		$items = $this->get('Items');

//        echo "<pre>";
//        print_r($items); die;

		$pagination = $this->get('Pagination');

        $state = $this->get('State');
        $this->state		= $this->get('State');
        $this->sortDirection = $state->get('filter_order_Dir');
        $this->sortColumn = $state->get('filter_order');

        //selectlist courses
		JLoader::import( 'courses', JPATH_SITE . DS . 'components' . DS . 'com_flexpaper' . DS . 'models' );
        $courses_model = JModel::getInstance( 'courses', 'flexpaperModel' );

        JLoader::import( 'course', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_flexpaper' . DS . 'models' );
        $course_model = JModel::getInstance( 'course', 'flexpaperModel' );

        $membershiplist = $courses_model->getItems();
        $membership_list_for_select = $course_model->prepare_for_select($membershiplist,'title');

        //dropdown courses
        $options1 = array();
        $default = $this->state->get('filter.course_id');

        $options1[] = JHTML::_( 'select.option', '', JText::_('COM_FLEXPAPER_SELECT_COURSE') );
        foreach( $membership_list_for_select as $key=>$value) {
            $options1[] = JHTML::_( 'select.option', $key, $value );

        }
        $this->membership_list =& JHTML::_('select.genericlist',$options1,'filter_course_id','class="inputbox" onchange="this.form.submit()"','value','text',$default);


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
        JToolBarHelper::title(JText::_('COM_FLEXPAPER_DOCUMENT_MANAGER'), 'documents');
        JToolBarHelper::deleteList('', 'flexpapers.delete');
        JToolBarHelper::editList('flexpaper.edit');
        JToolBarHelper::addNew('flexpaper.add');
    }




}