<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgUserBids extends JPlugin {

    protected $_is_valid = true;

    function __construct(&$subject, $config) {

        parent::__construct($subject, $config);

        $app = &JFactory::getApplication();

        $filepath = JPATH_ROOT . DS . 'components' . DS . 'com_bids' . DS . 'options.php';

        if ($app->isAdmin() || !file_exists($filepath)) {
            $this->_is_valid = false;
            return false;
        }

        //load component settings
        require_once($filepath);
        $cfg=new BidConfig();

        if('component'!=$cfg->bid_opt_registration_mode) {
            $this->_is_valid = false;
            return false;
        }

        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'thefactory'.DS.'application'.DS.'application.class.php');

        $configfile = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_bids'.DS.'application.ini';
        $MyApp = &JTheFactoryApplication::getInstance($configfile, true);

        JTable::addIncludePath(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_bids'.DS.'tables');
    }

    public function onUserBeforeSave($oldUser,$isNew,$newUser) {

        if (!$this->_is_valid) {
            return true;
        }



        $app = JFactory::getApplication();

        if(!defined('APP_PREFIX')) define('APP_PREFIX','bid');

        //var_dump(APP_PREFIX);exit;
        $bidprofile = JTable::getInstance('biduser');

        $this->bind2Profile( $bidprofile, JRequest::get('post') );

        if(!$bidprofile->check()) {
            $app->redirect('index.php?option=com_bids&task=registerform');
            return;
        }
    }

    public function onUserAfterSave($user, $isnew, $success, $msg) {

        if (!$this->_is_valid) {
            return true;
        }

        if($user['id'] && $success) {

            $db = JFactory::getDbo();
            $app = JFactory::getApplication();
            $input = $app->input;

            $bidprofile = JTable::getInstance('biduser');

            //new profile
            if(!$bidprofile->load($user['id'])) {
                $db->setQuery("INSERT INTO #__bid_users (userid) VALUES (".$db->quote($user['id']).")" );
                $db->query();
            }

            $bidprofile->userid = $user['id'];

            $this->bind2Profile( $bidprofile, JRequest::get('post') );

            //hack - because the html input is callded jform[name]
            $bidprofile->name = $user['name'];

            $bidprofile->store();
        }
    }

    public function onUserAfterDelete($user, $succes, $msg)
    {
        if (!$succes) {
            JError::raiseWarning(1,'Could not delete bids for userid='.$user['id']);
            return false;
        }

        $db = JFactory::getDbo();

        $db->setQuery('DELETE FROM #__bids WHERE userid='.(int) $user['id']);
        $db->query();

        $db->setQuery('DELETE FROM #__bid_proxy WHERE user_id='.(int) $user['id']);
        $db->query();

        $db->setQuery('DELETE FROM #__bid_report_auctions WHERE userid='.(int) $user['id']);
        $db->query();

        $db->setQuery('DELETE FROM #__bid_suggestions WHERE userid='.(int) $user['id']);
        $db->query();

        $db->setQuery('DELETE FROM #__bid_users WHERE userid='.(int) $user['id']);
        $db->query();

        $db->setQuery('DELETE FROM #__bid_watchlist WHERE userid='.(int) $user['id']);
        $db->query();

        $db->setQuery('UPDATE #__bid_auctions SET userid=NULL WHERE userid='.(int) $user['id']);
        $db->query();

        return true;
    }

    protected function bind2Profile(&$rowProfile,$data) {

        $rowProfile->bind( $data );
        $rowProfile->modified = gmdate('Y-m-d H:i:s');

        $notEditable = array('verified','isBidder','isSeller','powerseller');
        foreach($notEditable as $f) {
            $rowProfile->$f = null;
        }
    }
}