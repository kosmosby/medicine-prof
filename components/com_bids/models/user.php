<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/



// no direct access
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class bidsModelUser extends JModelLegacy {

    protected $profile;

    function loadUser($userid) {

        if(!$userid) {
            return false;
        }

        $this->profile = BidsHelperTools::getUserProfileObject($userid);
        return true;
    }

    //mixed param (array or object)
    function saveDefaultAuctionSettings($ob) {

        jimport('joomla.html.parameter');

        $my = JFactory::getUser();
        $config = & JFactory::getConfig();

        $us = JTable::getInstance('bidusersettings');

        $settings = new JParameter(null);

        $paramKeys = array(
            'show_reserve',
            'picture',
            'add_picture',
            'auto_accept_bin',
            'bid_counts',
            'max_price',
            'end_hour',
            'end_minute',
            'auction_type',
            'currency',
            'shipment_info',
            'shipment_price',
            'payment_info',
            'payment_options');

        $vals = array();
        foreach($paramKeys as $k) {
            $v = is_object($ob) ? (isset($ob->$k) ? $ob->$k : null) : (is_array($ob) ? @$ob[$k] : null);
            $vals[$k] = $v;
        }

        //convert end time to GMT
        try {
            $endTime = JFactory::getDate( ' ' . $vals['end_hour'] . ':' . $vals['end_minute'] . ':00', $config->getValue('config.offset'));
            @list($vals['end_hour'],$vals['end_minute']) = explode(':',$endTime->toFormat('%H:%M'));
        } catch(Exception $e) {
            if(JDEBUG) {
                JError::raiseWarning(1,$e->getMessage());
            }
        }


        $settings->bind($vals);

        $us->settings = $settings;
        $us->userid = $my->id;

        return $us->store();
    }
    function validateSaveUser($hash='default')
    {
        $result=array();
        $form = JRequest::getVar('jform',array());

        if (!$form['name'] )
            $result[]=JText::_("COM_BIDS_ERR_ENTER_NAME");
        if (!JRequest::getVar('surname',null,$hash))
            $result[]=JText::_("COM_BIDS_ERR_ENTER_SURNAME");
        if (!JRequest::getVar('city',null,$hash))
            $result[]=JText::_("COM_BIDS_ERR_ENTER_CITY");
        if (!JRequest::getVar('country',null,$hash))
            $result[]=JText::_("COM_BIDS_ERR_ENTER_COUNTRY");

        return $result;
    }
    function saveUserDetails() {

        $cfg = BidsHelperTools::getConfig();


        $errors=$this->validateSaveUser('post');
        if (count($errors)) return $errors;

		$my = JFactory::getUser();
		$currentUser = $my->id ? $my->id : 0;
        if (!$currentUser) {
            return JText::_("COM_BIDS_USER_NOT_AVAILABLE");
        }

        $user = JTable::getInstance('biduser');
        if (!$user->load( $currentUser ) ) {
            $user->createRecord($currentUser);
        }

        $user->bind(JRequest::get('post'));

        $jform = JRequest::getVar('jform');
        $user->name = $jform['name'];

        $user->userid=$currentUser;

        $date = JFactory::getDate(time());
        $user->modified = $date->toSQL();

        if($user->store()){
            $act = JRequest::getCmd("act",null);
            if($act=="savesettings"){
                if ($cfg->bid_opt_allow_user_settings) {
                    $this->saveDefaultAuctionSettings(JRequest::get());
                }
            }

        }

        return true;
    }

    private function _sendMail(&$user, $password) {

        global $mainframe;

        $lang = JFactory::getLanguage();
    	$lang->load('com_user');

        $db		=  JFactory::getDBO();

        $name 		= $user->get('name');
        $email 		= $user->get('email');
        $username 	= $user->get('username');

        $usersConfig 	= &JComponentHelper::getParams( 'com_users' );
        $sitename 		= $mainframe->getCfg( 'sitename' );
        $useractivation = $usersConfig->get( 'useractivation' );
        $mailfrom 		= $mainframe->getCfg( 'mailfrom' );
        $fromname 		= $mainframe->getCfg( 'fromname' );
        $siteURL		= JURI::base();

        $subject 	= sprintf ( $lang->_('Account details for' ), $name, $sitename);
        $subject 	= html_entity_decode($subject, ENT_QUOTES);

        if ( $useractivation == 1 ){
                $message = sprintf ( $lang->_('SEND_MSG_ACTIVATE' ), $name, $sitename, $siteURL."index.php?option=com_user&task=activate&activation=".$user->get('activation'), $siteURL, $username, $password);
        } else {
                $message = sprintf ( $lang->_('SEND_MSG' ), $name, $sitename, $siteURL);
        }

        $message = html_entity_decode($message, ENT_QUOTES);

        //get all super administrator
        $query = 'SELECT name, email, sendEmail' .
                        ' FROM #__users' .
                        ' WHERE LOWER( usertype ) = "super administrator"';
        $db->setQuery( $query );
        $rows = $db->loadObjectList();

        // Send email to user
        if ( ! $mailfrom  || ! $fromname ) {
            $fromname = $rows[0]->name;
            $mailfrom = $rows[0]->email;
        }

        JUtility::sendMail($mailfrom, $fromname, $email, $subject, $message);

        // Send notification to all administrators
        $subject2 = sprintf ( $lang->_('Account details for' ), $name, $sitename);
        $subject2 = html_entity_decode($subject2, ENT_QUOTES);

        // get superadministrators id
        foreach ( $rows as $row )
        {
                if ($row->sendEmail)
                {
                        $message2 = sprintf ( $lang->_('SEND_MSG_ADMIN' ), $row->name, $sitename, $name, $email, $username);
                        $message2 = html_entity_decode($message2, ENT_QUOTES);
                        JUtility::sendMail($mailfrom, $fromname, $row->email, $subject2, $message2);
                }
        }
    }

    function getBalance() {

        JTheFactoryHelper::modelIncludePath('payments');
        $balance = JModelLegacy::getInstance('balance','JTheFactoryModel');

        return $balance->getUserBalance();
    }
    function getMessages() {

        $db = $this->getDbo();
        $db->setQuery("
        	    SELECT m.*, a.title, u.username
        		FROM #__bid_messages as m
        		LEFT JOIN #__bid_auctions as a ON m.auction_id = a.id
        		LEFT JOIN #__users as u ON m.userid1 = u.id
        		WHERE userid2='".$this->profile->id."'  AND userid1 <> '".$this->profile->id."' AND m.published=1
        		ORDER BY m.modified DESC");

        return $db->loadObjectList();
    }

    function getRatings($limit=0) {
        $db = $this->getDbo();
        $sql = "select * from #__bid_rate r
                   left join #__bid_auctions a on r.auction_id=a.id
                   left join #__users u on r.voter_id=u.id
                   where user_rated_id='".$this->profile->id."' order by r.id desc";
        $db->setQuery($sql, 0, $limit);
        return $db->loadObjectList();
    }
    function getUserCountries()
    {
        $db= $this->getDbo();
        $query = JTheFactoryDatabase::getQuery();
        $profile = BidsHelperTools::getUserProfileObject();
        $field = $profile->getFilterField('country');
        $table = $profile->getFilterTable('country');

        
        $query->select("distinct `{$field}` country");
        $query->from($table);
        $query->where("`{$field}`<>'' and `{$field}` is not null");
        $db->setQuery((string)$query);
        return $db->loadObjectList();
        
    }

}
