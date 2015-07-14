<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

/**
 * Social Login Model
 */
class SocialLoginModelSocialLogin extends JModelItem
{
    /**
     * @var string msg
     */
    protected $msg;

    /**
     * Get the message
     * @return string The message to be displayed to the user
     */

    public function getTable($type = 'UserMeta', $prefix = 'SocialLoginTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    function oa_social_login_get_user_by_token ($user_token)
    {
        // Create a new query object.
        $db		= $this->getDbo();
        $query	= $db->getQuery(true);
        $user	= JFactory::getUser();

        if($user_token) {
            $sql = "SELECT u.ID FROM #__sociallogin_usermeta AS um	INNER JOIN  #__users AS u ON (um.user_id=u.ID)	WHERE um.meta_key = 'oa_social_login_user_token' AND um.meta_value = ".$db->quote($user_token);
            $db->setQuery($sql);
            $row = $db->loadResult();
        }

        if($row) {
            return $row;
        }
        else {
            return false;
        }
    }

    function email_exists($email) {
        $db		= $this->getDbo();
        $sql = "SELECT id FROM #__users WHERE email = ".$db->quote($email);
        $db->setQuery($sql);

        $user_id = $db->loadResult();

        if ( $user_id )
            return $user_id;

        return false;
    }

    function delete_metadata($meta_type, $object_id, $meta_key, $meta_value = '', $delete_all = false) {
        $db		= $this->getDbo();

        if($meta_key && $meta_value) {
            $sql = "DELETE FROM #__sociallogin_usermeta WHERE meta_key = '".$meta_key."' AND meta_value = '".$meta_value."'";
            $db->setQuery($sql);
            $db->query();
        }

        return true;
    }

    function username_exists( $username ) {

        $user =& JFactory::getUser();
        $user =& JFactory::getUser( $username );

        if ( isset($user->id) && $user->id ) {
            return $user->id;
        } else {
            return null;
        }
    }

    function is_email( $email ) {

       jimport('joomla.mail.helper');

       return JMailHelper::isEmailAddress($email);
       //return false;
    }

    function oa_social_login_create_rand_email()
    {
        do
        {
            $email = md5(uniqid(rand(10000,99000)))."@example.com";
        }	while($this->email_exists($email));

        return $email;
    }

    function generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ( $special_chars )
            $chars .= '!@#$%^&*()';
        if ( $extra_special_chars )
            $chars .= '-_ []{}<>~`+=,.;:/?|';

        $password = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $password .= substr($chars, rand(0, strlen($chars) - 1), 1);
        }

        return $password;
    }

    function insert_user($data) {

        $user = new JUser;

        // Bind the data.
        if (!$user->bind($data)) {
            $this->setError(JText::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError()));
            return false;
        }

        // Load the users plugin group.
        JPluginHelper::importPlugin('user');

        // Store the data.
        if (!$user->save()) {
            $this->setError(JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));
            return false;
        }

        return $user;

    }

    function update_user_meta($user_id, $meta_key, $meta_value, $prev_value = '') {
        $this->update_metadata('user', $user_id, $meta_key, $meta_value, $prev_value);
    }


    function update_metadata($meta_type, $object_id, $meta_key, $meta_value, $prev_value = '') {

        $db		= $this->getDbo();

        $sql = "DELETE FROM #__sociallogin_usermeta WHERE user_id = '".$object_id."' AND meta_key = '".$meta_key."'";
        $db->setQuery($sql);
        $db->query();

        $sql = "INSERT INTO #__sociallogin_usermeta ( user_id, meta_key, meta_value )" .
               " VALUES ( ".(int) $object_id.", ".$db->Quote($meta_key).", ".$db->Quote($meta_value)." )";


        $db->setQuery($sql);
        $db->query();

    }

    function set_current_user( $user_id, $password ) {

        $app = JFactory::getApplication();
        $user =& JFactory::getUser($user_id);

//      echo "<pre>";
//      print_r($user); die;

        // Populate the data array:
        $data = array();
        $data['return'] = base64_decode(JRequest::getVar('return', '', 'POST', 'BASE64'));
        $data['username'] = $user->username;
        $data['password'] = $password;

        // Set the return URL if empty.
        if (empty($data['return'])) {
            $data['return'] = 'index.php?option=com_users&view=profile';
        }

        // Get the log in options.
        $options = array();
        $options['remember'] = JRequest::getBool('remember', false);
        $options['return'] = $data['return'];

        // Get the log in credentials.
        $credentials = array();
        $credentials['username'] = $data['username'];
        $credentials['password'] = $data['password'];

//        echo "<pre>";
//        print_r($credentials);
//        print_r($options); die;

        // Perform the log in.
        $error = $app->login($credentials, $options);

        // Check if the log in succeeded.
        /*
        if (!JError::isError($error)) {
            $app->setUserState('users.login.form.data', array());
            $app->redirect(JRoute::_($data['return'], false));
        } else {
            $data['remember'] = (int)$options['remember'];
            $app->setUserState('users.login.form.data', $data);
            $app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
        }
        */

        $this->setRedirect(JRoute::_('index.php', false));

    }

    function _get_user($user_id) {
        jimport('joomla.user.helper');

        $user =& JFactory::getUser($user_id);

        $instance = JUser::getInstance();
        $instance->load($user_id);

        //TODO : move this out of the plugin
        jimport('joomla.application.component.helper');
        $config	= JComponentHelper::getParams('com_users');

        // Default to Registered.
        $defaultUserGroup = $config->get('new_usertype', 2);

        $acl = JFactory::getACL();

        $instance->set('id'			, $user_id);
        $instance->set('name'			, $user->name);
        $instance->set('username'		, $user->username);
        $instance->set('password_clear'	, '');
        $instance->set('email'			, $user->email);	// Result should contain an email (check)
        $instance->set('usertype'		, 'deprecated');
        $instance->set('groups'		, array($defaultUserGroup));

        return $instance;
    }


    function set_auth_cookie($user_id) {

        jimport('joomla.user.helper');

        $instance = $this->_get_user($user_id);

        // Mark the user as logged in
        $instance->set('guest', 0);

        // Register the needed session variables
        $session = JFactory::getSession();
        $session->set('user', $instance);

        $db = JFactory::getDBO();

        // Check to see the the session already exists.
        $app = JFactory::getApplication();
        $app->checkSession();

        $query = 'UPDATE `#__session`' .
            ' SET `guest` = 0,' .
            '	`username` = '.$db->quote($instance->get('username')).',' .
            '	`userid` = '.(int) $instance->get('id') .
            ' WHERE `session_id` = '.$db->quote($session->getId());

        // Update the user related fields for the Joomla sessions table.
        $db->setQuery($query);

        $db->query();

        // Hit the user last visit field
        $instance->setLastVisit();

        return true;

    }

    function getSettings() {

        $db		= $this->getDbo();

        $sql = "SELECT * FROM #__sociallogin_settings";
        $db->setQuery($sql);
        $rows = $db->LoadAssocList();

        $new_arr = array();
        for($i=0;$i<count($rows);$i++) {
            $new_arr[$rows[$i]['setting']] = $rows[$i]['value'];
        }

        return $new_arr;

    }




}