<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HelloWorlds View
 */
class SocialLoginViewSocialLogin extends JView
{
    /**
     * HelloWorlds view display method
     * @return void
     */
    function display($tpl = null)
    {
        /*
        // Get data from the model
        $items = $this->get('Items');
        $pagination = $this->get('Pagination');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode('<br />', $errors));
            return false;
        }
        // Assign data to the view
        $this->items = $items;
        $this->pagination = $pagination;
*/

        //$this->assignRef( 'greeting',	$greeting );

        require_once(JPATH_BASE.'/components/com_sociallogin/settings.php');

        $document =& JFactory::getDocument();

        $document->addStyleSheet('components/com_sociallogin/css/admin.css');
        $document->addScript('components/com_sociallogin/js/jquery.js');
        $document->addScript('components/com_sociallogin/js/admin.js');

        $model = &$this->getModel();
        $settings = $model->getSettings();

//        echo "<pre>";
//        print_r($oa_social_login_providers);

        $this->settings = $settings;
        $this->oa_social_login_providers = $oa_social_login_providers;


        $this->form		= $this->get('Form');

        $this->addToolbar();
        // Display the template
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JRequest::setVar('hidemainmenu', false);

        JToolBarHelper::title(JText::_('Social Login Settings'), 'weblinks.png');

        JToolBarHelper::apply('apply');
       // JToolBarHelper::save('save');

     }




}