<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * Hello World Component Controller
 */
class SocialLoginController extends JController
{

    public function display($cachable = false, $urlparams = false)
    {

        // Get the document object.
        $document	= JFactory::getDocument();

        // Set the default view name and format from the Request.
        $vName	 = JRequest::getCmd('view', 'login');
        $vFormat = $document->getType();
        $lName	 = JRequest::getCmd('layout', 'default');

        if ($view = $this->getView($vName, $vFormat)) {
            // Do any specific processing by view.

            switch ($vName) {
                case 'registration':
                    // If the user is already logged in, redirect to the profile page.
                    $user = JFactory::getUser();

                    if ($user->get('guest') != 1) {
                        // Redirect to profile page.
                        $this->setRedirect(JRoute::_('index.php?option=com_sociallogin&view=profile', false));
                        return;
                    }

                    // Check if user registration is enabled
                    if(JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0) {
                        // Registration is disabled - Redirect to login page.
                        $this->setRedirect(JRoute::_('index.php?option=com_sociallogin&view=login', false));
                        return;
                    }



                    // The user is a guest, load the registration model and show the registration page.
                    $model = $this->getModel('Registration');
                    break;

                // Handle view specific models.
                case 'profile':

                    // If the user is a guest, redirect to the login page.
                    $user = JFactory::getUser();

                    if ($user->get('guest') == 1) {
                        // Redirect to login page.
                        $this->setRedirect(JRoute::_('index.php?option=com_sociallogin&view=login', false));
                        return;
                    }

                    $model = $this->getModel($vName);
                    break;

                // Handle the default views.
                case 'login':
                    $model = $this->getModel($vName);
                    break;

                case 'reset':
                    // If the user is already logged in, redirect to the profile page.
                    $user = JFactory::getUser();
                    if ($user->get('guest') != 1) {
                        // Redirect to profile page.
                        $this->setRedirect(JRoute::_('index.php?option=com_sociallogin&view=profile', false));
                        return;
                    }

                    $model = $this->getModel($vName);
                    break;

                case 'remind':
                    // If the user is already logged in, redirect to the profile page.
                    $user = JFactory::getUser();
                    if ($user->get('guest') != 1) {
                        // Redirect to profile page.
                        $this->setRedirect(JRoute::_('index.php?option=com_sociallogin&view=profile', false));
                        return;
                    }

                    $model = $this->getModel($vName);
                    break;

                default:
                    $model = $this->getModel('Login');
                    break;
            }

            // Push the model into the view (as default).
            $view->setModel($model, true);

            $view->setLayout($lName);

            // Push document object into the view.
            $view->assignRef('document', $document);

            $view->display();

        }
    }


    function login() {

       $this->oa_social_login_callback();
    }

    function oa_social_login_callback ()
    {

        $model = &$this->getModel();
        $settings = $model->getSettings();

         //Callback Handler
        if (isset ($_POST) AND !empty ($_POST ['oa_action']) AND $_POST ['oa_action'] == 'social_login' AND !empty ($_POST ['connection_token']))
        {

            //Read settings
            // $settings = get_option ('oa_social_login_settings');
            /*
            $settings ['api_subdomain'] = 'wordpress-test';
            $settings ['api_key'] = 'b9f48dc9-2395-46f5-8ee6-f44b977bccda';
            $settings ['api_secret'] = '4ec30816-ebd2-4b72-b9d8-7342573af648';
            */
            //API Settings
            $api_subdomain = (!empty ($settings ['api_subdomain']) ? $settings ['api_subdomain'] : '');
            $api_key = (!empty ($settings ['api_key']) ? $settings ['api_key'] : '');
            $api_secret = (!empty ($settings ['api_secret']) ? $settings ['api_secret'] : '');

            //Get user profile
            $curl = curl_init ();
            curl_setopt ($curl, CURLOPT_URL, 'https://' . $api_subdomain . '.api.oneall.com/connections/' . $_POST ['connection_token'] . '.json');
            curl_setopt ($curl, CURLOPT_HEADER, 0);
            curl_setopt ($curl, CURLOPT_USERPWD, $api_key . ":" . $api_secret);
            curl_setopt ($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt ($curl, CURLOPT_VERBOSE, 0);
            curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($curl, CURLOPT_FAILONERROR, 0);

            //Process
            if (($json = curl_exec ($curl)) !== false)
            {
                //Close connection
                curl_close ($curl);

                //Decode
                $social_data = json_decode ($json);

//                echo "<pre>";
//                print_r($social_data); die;

                //User Data
                if (is_object ($social_data) AND $social_data->response->result->status->code == 200)
                {
                    $identity = $social_data->response->result->data->user->identity;
                    $user_token = $social_data->response->result->data->user->user_token;

                    //Identity
                    $user_identity_id = $identity->id;
                    $user_identity_provider = $identity->source->name;

                    if(isset($identity->name->givenName) && $identity->name->givenName) {
                        //Firstname
                        $user_first_name = $identity->name->givenName;
                    }
                    elseif(isset($identity->preferredUsername)) {
                        //Firstname
                        $user_first_name = $identity->preferredUsername;
                    }
                    else {
                        $user_first_name = 'noname';
                    }

                    if(isset($identity->name->familyName) && $identity->name->familyName) {
                        //Lastname
                        $user_last_name = $identity->name->familyName;
                    }
                    else {
                        $user_last_name = '';
                    }

                    //Fullname
                    $user_full_name = '';
                    if ( ! empty ($identity->name->formatted))
                    {
                        $user_full_name = $identity->name->formatted;
                    }
                    elseif ( ! empty ($identity->name->displayName))
                    {
                        $user_full_name = $identity->name->displayName;
                    }
                    else
                    {
                        $user_full_name = trim ($user_first_name.' '.$user_last_name);
                    }

                    //Email
                    $user_email = '';
                    if (property_exists ($identity, 'emails') AND is_array ($identity->emails))
                    {
                        foreach ($identity->emails AS $email)
                        {
                            $user_email = $email->value;
                            $user_email_is_verified = ($email->is_verified == '1');
                        }
                    }




                    //Thumbnail
                    $user_thumbnail = '';
                    if ( ! empty ($identity->thumbnailUrl))
                    {
                        $user_thumbnail = trim ($identity->thumbnailUrl);
                    }

                    //User Website
                    $user_website = '';
                    if ( ! empty ($identity->profileUrl))
                    {
                        $user_website = $identity->profileUrl;
                    }
                    elseif ( ! empty ($identity->urls [0]->value))
                    {
                        $user_website = $identity->urls [0]->value;
                    }

                    //Preferred Username
                    $user_login = '';
                    if ( ! empty ($identity->preferredUsername))
                    {
                        $user_login = $identity->preferredUsername;
                    }
                    elseif (! empty ($identity->displayName))
                    {
                        $user_login = $identity->displayName;
                    }
                    elseif (! empty ($identity->name->formatted))
                    {
                        $user_login = $identity->name->formatted;
                    }

                    $user_id = $model->oa_social_login_get_user_by_token ($user_token);
                    // Get user by token
                    //$user_id = '';



                    //Try to link to existing account
                    if (is_numeric ($user_id))
                    {
                        //Linked enabled?
                        if ( ! isset($settings['plugin_link_verified_accounts']) OR $settings['plugin_link_verified_accounts'] == '1')
                        {

                            //Only of email is verified
                            if ( ! empty ($user_email) AND $user_email_is_verified === true)
                            {

                                //Read existing user
                                if (($user_id_tmp = $model->email_exists($user_email)) !== false)
                                {

                                    if (is_numeric ($user_id_tmp))
                                    {
                                        $user_id = $user_id_tmp;
                                        $model->delete_metadata('user', null, 'oa_social_login_user_token', $user_token, true);
                                        $model->update_user_meta ($user_id, 'oa_social_login_user_token', $user_token);
                                        $model->update_user_meta ($user_id, 'oa_social_login_identity_id', $user_identity_id);
                                        $model->update_user_meta ($user_id, 'oa_social_login_identity_provider', $user_identity_provider);

                                        if ( ! empty ($user_thumbnail))
                                        {
                                            $model->update_user_meta ($user_id, 'oa_social_login_user_thumbnail', $user_thumbnail);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    //New User
                    if ( ! is_numeric ($user_id))
                    {

                        //Username is mandatory
                        if ( ! isset ($user_login) OR strlen(trim($user_login)) == 0)
                        {
                            $user_login = $user_identity_provider.'User';
                        }

                        //Username must be unique
                        if ($model->username_exists ($user_login))
                        {
                            $i = 1;
                            $user_login_tmp = $user_login;
                            do
                            {
                                $user_login_tmp = $user_login.($i++);
                            } while ($model->username_exists ($user_login_tmp));
                            $user_login = $user_login_tmp;
                        }

                        //echo $model->is_email($user_email); die;

                        //Email must be unique
                        if ( ! isset ($user_email) OR ! $model->is_email($user_email) OR $model->email_exists ($user_email))
                        {
                            $user_email = $model->oa_social_login_create_rand_email();
                        }



                        $password = $model->generate_password();

                        $user_data = array (
                            'username' => $user_login,
                            'email' => $user_email,
                            'email1' => $user_email,
                            'email2' => $user_email,
                            'name' => $user_first_name.' '.$user_last_name,
                            'user_url' => $user_website,
                            'password1' => $password,
                            'password2' => $password,
                            'block' => 0,
                            'groups' => array(0 => 2)
                        );

                        // Create a new user
                        $inserted_user = $model->insert_user ($user_data);

                        if (is_numeric($inserted_user->id))
                        {
                            $model->delete_metadata('user', null, 'oa_social_login_user_token', $user_token, true);
                            $model->update_user_meta ($inserted_user->id, 'oa_social_login_user_token', $user_token);
                            $model->update_user_meta ($inserted_user->id, 'oa_social_login_identity_id', $user_identity_id);
                            $model->update_user_meta ($inserted_user->id, 'oa_social_login_identity_provider', $user_identity_provider);

                            if ( ! empty ($user_thumbnail))
                            {
                                $model->update_user_meta ($inserted_user->id, 'oa_social_login_user_thumbnail', $user_thumbnail);
                            }
                        }

                        $user_id = $inserted_user->id;
                    }
                    //Sucess

                    $user =& JFactory::getUser($user_id);

                    if (is_object($user))
                    {
                        //Setup Cookie
                        $model->set_auth_cookie($user_id);

                        //Redirect to administration area
                        /*
                        if (! empty ($_REQUEST['oa_social_login_source']) AND in_array ($_REQUEST['oa_social_login_source'], array ('login', 'registration')))
                        {
                            //Registration
                            if ($_REQUEST['oa_social_login_source'] == 'registration')
                            {
                                //Default redirection
                                $redirect_to = admin_url();
                                $redirect_to_safe = false;

                                //Redirection customized
                                if (isset ($settings ['plugin_registration_form_redirect']))
                                {
                                    switch (strtolower($settings ['plugin_registration_form_redirect']))
                                    {
                                        //Homepage
                                        case 'homepage':
                                            $redirect_to = site_url();
                                            break;

                                        //Custom
                                        case 'custom':
                                            if ( isset ($settings ['plugin_registration_form_redirect_custom_url']) AND strlen(trim($settings ['plugin_registration_form_redirect_custom_url'])) > 0)
                                            {
                                                $redirect_to = trim($settings ['plugin_registration_form_redirect_custom_url']);
                                            }
                                            break;

                                        //Default/Dashboard
                                        default:
                                        case 'dashboard':
                                            $redirect_to = admin_url();
                                            break;
                                    }
                                }
                            }
                            //Login
                            elseif ($_REQUEST['oa_social_login_source'] == 'login')
                            {
                                //Default redirection
                                $redirect_to = site_url();
                                $redirect_to_safe = false;

                                //Redirection in URL
                                if ( ! empty ($_GET['redirect_to']))
                                {
                                    $redirect_to = $_GET['redirect_to'];
                                    $redirect_to_safe = true;
                                }
                                else
                                {
                                    //Redirection customized
                                    if (isset ($settings ['plugin_login_form_redirect']))
                                    {
                                        switch (strtolower($settings ['plugin_login_form_redirect']))
                                        {
                                            //Dashboard
                                            case 'dashboard':
                                                $redirect_to = admin_url();
                                                break;

                                            //Custom
                                            case 'custom':
                                                if ( isset ($settings ['plugin_login_form_redirect_custom_url']) AND strlen(trim($settings ['plugin_login_form_redirect_custom_url'])) > 0)
                                                {
                                                    $redirect_to = trim($settings ['plugin_login_form_redirect_custom_url']);
                                                }
                                                break;

                                            //Default/Homepage
                                            default:
                                            case 'homepage':
                                                $redirect_to = site_url();
                                                break;
                                        }
                                    }
                                }
                            }

                            if ($redirect_to_safe)
                            {
                                wp_redirect($redirect_to);
                            }
                            else
                            {
                                wp_safe_redirect($redirect_to);
                            }
                        }
                        */

                            $this->redirect_after_login('index.php');
                            exit();
                    }
                        //Set current user
                        else
                        {
                            if(isset($inserted_user->password2) && $inserted_user->password2) {
                                $model->set_current_user($user->id, $inserted_user->password2);
                            }
                        }
                    }
                }
            }
        }

    function redirect_after_login( $url, $msg='', $msgType='message' )
    {

        // check for relative internal links
        if (preg_match( '#^index[2]?.php#', $url )) {
            $url = JURI::base() . $url;
        }

        // Strip out any line breaks
        $url = preg_split("/[\r\n]/", $url);
        $url = $url[0];

        // If we don't start with a http we need to fix this before we proceed
        // We could validly start with something else (e.g. ftp), though this would
        // be unlikely and isn't supported by this API
        if(!preg_match( '#^http#i', $url )) {
            $uri =& JURI::getInstance();
            $prefix = $uri->toString(Array('scheme', 'user', 'pass', 'host', 'port'));
            if($url[0] == '/') {
                // we just need the prefix since we have a path relative to the root
                $url = $prefix . $url;
            } else {
                // its relative to where we are now, so lets add that
                $parts = explode('/', $uri->toString(Array('path')));
                array_pop($parts);
                $path = implode('/',$parts).'/';
                $url = $prefix . $path . $url;
            }
        }


        // If the message exists, enqueue it
        if (trim( $msg )) {
            $this->enqueueMessage($msg, $msgType);
        }

        // Persist messages if they exist
        if (count($this->_messageQueue))
        {
            $session =& JFactory::getSession();
            $session->set('application.queue', $this->_messageQueue);
        }

        /*
        * If the headers have been sent, then we cannot send an additional location header
        * so we will output a javascript redirect statement.
        */
        if (headers_sent()) {
            echo "<script>document.location.href='$url';</script>\n";
        } else {
            //@ob_end_clean(); // clear output buffer
            header( 'HTTP/1.1 301 Moved Permanently' );
            header( 'Location: ' . $url );
        }
        $this->close();
    }


}