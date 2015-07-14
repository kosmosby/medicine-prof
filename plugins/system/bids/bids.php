<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgSystemBids extends JPlugin {

    protected $_is_valid = true;

    function __construct(&$subject, $config) {

        parent::__construct($subject, $config);

        $app = JFactory::getApplication();

        $filepath = JPATH_ROOT . DS . 'components' . DS . 'com_bids' . DS . 'options.php';

        if (!$app->isSite() || !file_exists($filepath)) {
            $this->_is_valid = false;
            return false;
        }

        //load component settings
        require_once($filepath);
    }

    function onAfterInitialise() {

        $app = JFactory::getApplication();

        if($app->isSite()) {
            return;
        }

        $input = $app->input;
        $option = $input->getCmd('option','');
        $extension = $input->getCmd('extension','');

        if('com_categories'!=$option || 'com_bids'!=$extension) {
            return;
        }

        JHtml::_('behavior.modal');

        $bar = JToolBar::getInstance('toolbar');

        // Add a standard button.
        $bar->appendButton('Popup', 'new', 'Quick Add', 'index.php?option=com_bids&amp;task=showcatquickadd&amp;&amp;tmpl=component', 640, 480, 0, 0, 'window.location=\'index.php?option=com_categories&view=categories&extension=com_bids\'');

    }

    function onAfterRoute() {

        if (!$this->_is_valid) {
            return false;
        }

        $app = JFactory::getApplication();
        $input = $app->input;

        $view = $input->getCmd('view', '');
        $task = $input->getCmd('task', '');
        $option = $input->getCmd('option', '');

        $cfg = new BidConfig();

        $reg_mode = $cfg->bid_opt_registration_mode;
        $profileMode = $cfg->bid_opt_profile_mode;

        if ( 'component'==$reg_mode && 'com_users'==$option && 'registration'==$view && 'component' == $profileMode ) {
            //user is redirect to component registration form
            $app->redirect(JRoute::_('index.php?option=com_bids&task=registerForm&Itemid=' . $this->params->get('itemid', 0), false));
            return;
        }

    }
}